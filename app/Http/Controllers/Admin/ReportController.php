<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Document;
use App\Models\Report;
use App\Services\Audit\AuditLogger;
use App\Services\Documents\DocumentWorkflow;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'reports');
        $status = $request->query('status', 'open');
        $reports = Report::with(['reporter:id,name', 'reportable', 'handler:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))->latest()->paginate(15, ['*'], 'reports_page')->withQueryString();
        $comments = Comment::with(['user:id,name', 'document:id,title'])->latest()->paginate(15, ['*'], 'comments_page')->withQueryString();

        return view('admin.reports.index', compact('tab', 'status', 'reports', 'comments'));
    }

    public function handle(Request $request, Report $report, DocumentWorkflow $workflow)
    {
        $d = $request->validate([
            'action' => ['required', 'in:resolve,resolve_hide,dismiss'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($report->status !== 'open') {
            return back()->with('error', 'Báo cáo này đã được xử lý.');
        }

        if ($d['action'] === 'resolve_hide') {
            $target = $report->reportable;
            if ($target instanceof Comment) {
                $target->update(['status' => 'hidden']);
            } elseif ($target instanceof Document) {
                try {
                    $workflow->decide($target, $request->user(), 'hide', $d['note'] ?: 'Vi phạm theo báo cáo #' . $report->id);
                } catch (\DomainException $e) {
                    return back()->with('error', 'Không thể ẩn tài liệu: ' . $e->getMessage());
                }
            } else {
                return back()->with('error', 'Đối tượng bị báo cáo không còn tồn tại.');
            }
        }

        $report->update([
            'status' => $d['action'] === 'dismiss' ? 'dismissed' : 'resolved',
            'handled_by' => $request->user()->id, 'handled_at' => now(), 'resolution_note' => $d['note'] ?? null,
        ]);
        AuditLogger::record('report.' . $d['action'], $report, ['type' => $report->reportable_type, 'target' => $report->reportable_id]);

        return back()->with('success', 'Đã xử lý báo cáo.');
    }

    public function toggleComment(Comment $comment)
    {
        $comment->update(['status' => $comment->status === 'visible' ? 'hidden' : 'visible']);
        AuditLogger::record('comment.' . ($comment->status === 'hidden' ? 'hide' : 'show'), $comment, ['document_id' => $comment->document_id]);

        return back()->with('success', 'Đã cập nhật bình luận.');
    }
}
