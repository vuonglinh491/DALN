<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentProcessingIssue extends Notification
{
    use Queueable;

    public function __construct(private Document $doc, private string $error)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->doc->id,
            'message' => 'Không thể lập chỉ mục tài liệu «' . $this->doc->title . '».',
            'reason' => $this->error,
            'url' => route('documents.mine'),
        ];
    }
}
