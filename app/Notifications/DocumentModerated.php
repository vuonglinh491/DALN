<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentModerated extends Notification
{
    use Queueable;

    public function __construct(private Document $doc, private string $decision, private ?string $reason)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $t = '«' . $this->doc->title . '»';
        $msg = [
            'approve' => "Tài liệu {$t} đã được duyệt và hiển thị công khai.",
            'reject' => "Tài liệu {$t} bị từ chối.",
            'changes' => "Tài liệu {$t} cần chỉnh sửa trước khi được duyệt.",
            'hide' => "Tài liệu {$t} đã bị quản trị viên ẩn.",
            'restore' => "Tài liệu {$t} đã được hiển thị lại.",
        ][$this->decision] ?? "Trạng thái tài liệu {$t} đã thay đổi.";

        return [
            'document_id' => $this->doc->id,
            'decision' => $this->decision,
            'message' => $msg,
            'reason' => $this->reason,
            'url' => route('documents.mine'),
        ];
    }
}
