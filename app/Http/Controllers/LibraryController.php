<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use App\Models\Download;
use App\Models\Tag;
use App\Services\Search\SearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    public function index(Request $request, SearchService $search)
    {
        $f = $request->validate([
            'category' => ['nullable', 'integer'], 'tag' => ['nullable', 'string', 'max:70'],
            'type' => ['nullable', 'in:pdf,docx,txt'], 'sort' => ['nullable', 'in:' . implode(',', array_keys(SearchService::SORTS))],
        ]);
        $f['sort'] = ($f['sort'] ?? null) === 'relevance' ? 'newest' : ($f['sort'] ?? 'newest');

        return view('library.index', [
            'documents' => $search->search($f),
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => Tag::whereHas('documents', fn ($q) => $q->published())->orderBy('name')->limit(30)->get(),
            'filters' => $f,
            'sorts' => collect(SearchService::SORTS)->except('relevance')->all(),
        ]);
    }

    public function show(Request $request, Document $document)
    {
        // 404 (không phải 403) để không lộ sự tồn tại của tài liệu riêng tư / chưa duyệt
        abort_unless(Gate::allows('view', $document), 404);
        $user = $request->user();

        // Lượt xem: mỗi phiên một lần, không tính chủ sở hữu
        $key = 'viewed.' . $document->id;
        if ((!$user || $user->id !== $document->user_id) && !$request->session()->has($key) && $document->isPublished()) {
            $document->increment('view_count');
            $request->session()->put($key, true);
        }

        $document->load(['owner:id,name', 'category:id,name', 'tags']);
        $previewChars = (int) \App\Models\Setting::get('preview_chars');
        $preview = null;
        if ($document->processing_status === Document::P_COMPLETED) {
            $text = '';
            foreach ($document->chunks()->orderBy('chunk_index')->limit(6)->get(['content']) as $c) {
                $text .= $c->content . "\n";
                if (mb_strlen($text) >= $previewChars) {
                    break;
                }
            }
            $preview = mb_strlen($text) > $previewChars ? mb_substr($text, 0, $previewChars) . '…' : trim($text);
        }

        $related = Document::published()->where('category_id', $document->category_id)->where('id', '!=', $document->id)
            ->with(['owner:id,name', 'category:id,name'])->orderByDesc('download_count')->limit(4)->get();

        return view('library.show', [
            'document' => $document,
            'preview' => $preview,
            'related' => $related,
            'avg' => $document->ratings()->avg('score'),
            'ratingCount' => $document->ratings()->count(),
            'myRating' => $user ? $document->ratings()->where('user_id', $user->id)->value('score') : null,
            'bookmarked' => $user ? $document->bookmarks()->where('user_id', $user->id)->exists() : false,
            'comments' => $document->comments()->visible()->with('user:id,name')->latest()->paginate(10, ['*'], 'comments_page'),
            'canComment' => $user && $document->isPublished(),
        ]);
    }

    /** Tải xuống: cần đăng nhập + quyền; mỗi lượt tải thành công được ghi nhận 1 bản ghi + tăng bộ đếm. */
    public function download(Request $request, Document $document)
    {
        abort_unless(Gate::allows('download', $document), 404);
        $path = Storage::disk('local')->path($document->storage_path);
        abort_unless(is_file($path), 404, 'Tệp không còn tồn tại trên máy chủ.');

        DB::transaction(function () use ($request, $document) {
            Download::create([
                'user_id' => $request->user()->id,
                'document_id' => $document->id,
                'version_number' => $document->current_version,
                'ip_address' => $request->ip(),
            ]);
            $document->increment('download_count');
        });

        return response()->download($path, $document->original_filename, ['X-Content-Type-Options' => 'nosniff']);
    }

    /** Mở tệp gốc để đọc trực tiếp (PDF/TXT) — dùng cho liên kết nguồn trích dẫn; không tính lượt tải. */
    public function file(Document $document)
    {
        abort_unless(Gate::allows('download', $document), 404);
        abort_unless(in_array($document->extension, ['pdf', 'txt'], true), 404, 'Định dạng này chỉ hỗ trợ tải xuống.');
        $path = Storage::disk('local')->path($document->storage_path);
        abort_unless(is_file($path), 404, 'Tệp không còn tồn tại trên máy chủ.');

        return response()->file($path, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => "inline; filename*=UTF-8''" . rawurlencode($document->original_filename),
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ]);
    }
}
