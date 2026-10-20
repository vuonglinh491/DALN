<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Document;
use App\Models\Setting;
use App\Services\Ai\AiLimitException;
use App\Services\Ai\RagService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $docs = Document::aiEligibleFor($user)->orderBy('title')->limit(300)->get(['id', 'title', 'user_id']);

        return view('ai.index', [
            'recent' => $user->conversations()->latest('updated_at')->limit(8)->get(),
            'docs' => $docs,
            'preselect' => (int) $request->query('document'),
            'configured' => (bool) config('daln.gemini.key'),
        ]);
    }

    public function history(Request $request)
    {
        return view('ai.history', ['conversations' => $request->user()->conversations()->with('document:id,title')->withCount('messages')->latest('updated_at')->paginate(15)]);
    }

    public function start(Request $request, RagService $rag)
    {
        $d = $request->validate([
            'scope' => ['required', 'in:' . implode(',', array_keys(Conversation::SCOPES))],
            'document_id' => ['nullable', 'integer', 'required_if:scope,document'],
        ]);
        $conv = new Conversation([
            'user_id' => $request->user()->id,   // lấy từ phiên đăng nhập, không từ client
            'title' => 'Hội thoại mới',
            'scope' => $d['scope'],
            'document_id' => $d['scope'] === 'document' ? $d['document_id'] : null,
        ]);
        if ($conv->scope === 'document') {
            $doc = Document::find($conv->document_id);
            abort_unless($doc && $request->user()->can('useAi', $doc), 404);
        }
        $conv->save();

        return redirect()->route('ai.show', $conv);
    }

    public function show(Request $request, Conversation $conversation, RagService $rag)
    {
        abort_unless((int) $conversation->user_id === (int) $request->user()->id, 404);

        return view('ai.show', [
            'conversation' => $conversation->load('document:id,title'),
            'messages' => $conversation->messages()->with('sources.document')->get(),
            'problem' => $rag->scopeProblem($request->user(), $conversation),
            'configured' => (bool) config('daln.gemini.key'),
            'maxChars' => (int) Setting::get('ai_max_question_chars'),
        ]);
    }

    public function message(Request $request, Conversation $conversation, RagService $rag)
    {
        abort_unless((int) $conversation->user_id === (int) $request->user()->id, 404);
        $max = (int) Setting::get('ai_max_question_chars');
        $d = $request->validate([
            'question' => ['required', 'string', 'min:2', 'max:' . $max],
            'mode' => ['required', 'in:' . implode(',', array_keys(config('daln.modes')))],
        ]);

        // Quyền được kiểm tra lại ở MỖI câu hỏi (tài liệu có thể đã bị ẩn/đổi quyền)
        if ($problem = $rag->scopeProblem($request->user(), $conversation)) {
            return response()->json(['message' => $problem], 422);
        }

        try {
            [$userMsg, $answer] = $rag->ask($request->user(), $conversation, trim($d['question']), $d['mode']);
        } catch (AiLimitException $e) {
            return response()->json(['message' => $e->getMessage()], 429);
        }

        if ($conversation->title === 'Hội thoại mới') {
            $conversation->update(['title' => Str::limit(trim($d['question']), 60)]);
        }

        return response()->json([
            'user' => ['id' => $userMsg->id, 'html' => $userMsg->content_html],
            'assistant' => [
                'id' => $answer->id,
                'html' => $answer->content_html,
                'status' => $answer->status,
                'grounded' => $answer->grounded,
                'sources' => $answer->sources->map(fn ($s) => [
                    'ref' => $s->ref_no,
                    'title' => $s->document->title ?? 'Tài liệu',
                    'page' => $s->page_start,
                    'excerpt' => $s->excerpt,
                    'url' => $s->document ? $this->sourceUrl($s) : null,
                ])->values(),
            ],
        ]);
    }

    public function destroy(Request $request, Conversation $conversation)
    {
        abort_unless((int) $conversation->user_id === (int) $request->user()->id, 404);
        $conversation->delete();

        return redirect()->route('ai.history')->with('success', 'Đã xóa hội thoại.');
    }

    /** Đồng ý cho AI xử lý tài liệu riêng tư/chưa công khai của chính mình (gửi nội dung tới Gemini). */
    public function consent(Request $request)
    {
        $request->validate(['agree' => ['accepted']]);
        $request->user()->forceFill(['ai_consent_at' => now()])->save();

        return back()->with('success', 'Đã ghi nhận đồng ý. AI có thể dùng tài liệu riêng tư của bạn.');
    }

    public function revokeConsent(Request $request)
    {
        $request->user()->forceFill(['ai_consent_at' => null])->save();

        return back()->with('success', 'Đã thu hồi đồng ý. AI sẽ không dùng tài liệu riêng tư của bạn nữa.');
    }

    private function sourceUrl($s): string
    {
        $doc = $s->document;
        if ($doc->extension === 'pdf') {
            return route('library.file', $doc) . ($s->page_start ? '#page=' . $s->page_start : '');
        }

        return route('library.show', $doc);
    }
}
