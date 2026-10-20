<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocumentJob;
use App\Models\Document;
use App\Services\Audit\AuditLogger;
use App\Services\Documents\DocumentWorkflow;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $q = trim((string) $request->query('q'));
        $docs = Document::where('visibility', 'public')
            ->when($status !== 'all', fn ($w) => $w->where('moderation_status', $status))
            ->when($q !== '', fn ($w) => $w->where('title', 'like', "%{$q}%"))
            ->with(['owner:id,name', 'category:id,name'])->oldest('submitted_at')->paginate(15)->withQueryString();
        $counts = Document::where('visibility', 'public')->selectRaw('moderation_status, count(*) n')->groupBy('moderation_status')->pluck('n', 'moderation_status');

        return view('admin.moderation.index', compact('docs', 'status', 'q', 'counts'));
    }

    public function show(Document $document)
    {
        abort_unless($document->visibility === 'public', 404); // admin không xem tài liệu riêng tư của người khác

        $preview = $document->chunks()->orderBy('chunk_index')->limit(5)->pluck('content')->implode("\n");
        $preview = mb_strlen($preview) > 3000 ? mb_substr($preview, 0, 3000) . '…' : $preview;

        return view('admin.moderation.show', [
            'document' => $document->load(['owner:id,name,email', 'category:id,name', 'tags', 'reviews.admin:id,name', 'processingJobs' => fn ($q) => $q->latest()->limit(5)]),
            'preview' => $preview,
            'decisions' => DocumentWorkflow::DECISIONS,
        ]);
    }

    public function decide(Request $request, Document $document, DocumentWorkflow $workflow)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:' . implode(',', array_keys(DocumentWorkflow::DECISIONS))],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        try {
            $workflow->decide($document, $request->user(), $data['decision'], $data['reason'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->withErrors(['reason' => $e->getMessage()]);
        }

        return redirect()->route('admin.moderation.index')->with('success', 'Đã ghi nhận quyết định kiểm duyệt.');
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);
        $title = $document->title;
        $document->delete();
        AuditLogger::record('document.delete', $document, ['title' => $title, 'by' => 'admin']);

        return redirect()->route('admin.moderation.index')->with('success', 'Đã xóa tài liệu «' . $title . '».');
    }

    public function reprocess(Document $document)
    {
        $document->update(['processing_status' => Document::P_PENDING, 'processing_error' => null]);
        ProcessDocumentJob::dispatch($document->id);
        AuditLogger::record('document.reprocess', $document, ['by' => 'admin']);

        return back()->with('success', 'Đã đưa tài liệu vào hàng đợi xử lý lại.');
    }
}
