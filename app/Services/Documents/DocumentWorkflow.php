<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentReview;
use App\Models\User;
use App\Notifications\DocumentModerated;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Quy tắc chuyển trạng thái kiểm duyệt:
 *   draft ──(chủ sở hữu gửi công khai)──▶ pending
 *   pending ──approve──▶ approved | ──reject──▶ rejected | ──changes──▶ changes_requested
 *   rejected / changes_requested ──(chủ sở hữu chỉnh sửa & gửi lại)──▶ pending
 *   approved ──hide──▶ hidden ──restore──▶ approved
 *   approved ──(chủ sở hữu sửa tệp/tiêu đề/mô tả/danh mục)──▶ pending   (tái kiểm duyệt)
 */
class DocumentWorkflow
{
    public const DECISIONS = [
        // quyết định => [trạng thái đầu hợp lệ, trạng thái đích, bắt buộc lý do]
        'approve' => [['pending'], 'approved', false],
        'reject' => [['pending'], 'rejected', true],
        'changes' => [['pending'], 'changes_requested', true],
        'hide' => [['approved'], 'hidden', true],
        'restore' => [['hidden'], 'approved', false],
    ];

    public function decide(Document $doc, User $admin, string $decision, ?string $reason): void
    {
        if (!isset(self::DECISIONS[$decision])) {
            throw new \DomainException('Quyết định không hợp lệ.');
        }
        [$from, $to, $needReason] = self::DECISIONS[$decision];
        $reason = $reason !== null ? trim($reason) : null;

        if (!in_array($doc->moderation_status, $from, true)) {
            throw new \DomainException('Không thể thực hiện quyết định này với tài liệu đang ở trạng thái hiện tại.');
        }
        if ($doc->visibility !== 'public') {
            throw new \DomainException('Chỉ kiểm duyệt tài liệu được gửi công khai.');
        }
        if ($needReason && ($reason === null || $reason === '')) {
            throw new \DomainException('Vui lòng nhập lý do cho quyết định này.');
        }

        DB::transaction(function () use ($doc, $admin, $decision, $to, $reason) {
            $doc->update([
                'moderation_status' => $to,
                'review_note' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'approved_at' => $to === 'approved' ? ($doc->approved_at ?? now()) : $doc->approved_at,
            ]);
            DocumentReview::create([
                'document_id' => $doc->id,
                'admin_id' => $admin->id,
                'decision' => $decision,
                'reason' => $reason,
                'version_number' => $doc->current_version,
            ]);
            AuditLogger::record('document.' . $decision, $doc, ['reason' => $reason, 'title' => $doc->title], $admin);
        });

        $doc->owner?->notify(new DocumentModerated($doc, $decision, $reason));
    }

    /** Chủ sở hữu gửi (lại) tài liệu công khai để kiểm duyệt. */
    public function submit(Document $doc): void
    {
        if (!in_array($doc->moderation_status, ['draft', 'rejected', 'changes_requested'], true)) {
            throw new \DomainException('Tài liệu không ở trạng thái có thể gửi duyệt.');
        }
        $doc->update(['visibility' => 'public', 'moderation_status' => Document::PENDING, 'submitted_at' => now()]);
        AuditLogger::record('document.submit', $doc, ['title' => $doc->title]);
    }
}
