<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Documents\DocumentProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 600;

    public function __construct(public int $documentId)
    {
    }

    public function handle(DocumentProcessor $processor): void
    {
        $doc = Document::find($this->documentId);
        if ($doc) {
            $processor->process($doc);
        }
    }

    /** Chạy khi job bị kill/timeout: đảm bảo tài liệu không bị kẹt ở "processing" và không bị coi là đã lập chỉ mục. */
    public function failed(\Throwable $e): void
    {
        Document::whereKey($this->documentId)->where('processing_status', '!=', Document::P_COMPLETED)->update([
            'processing_status' => Document::P_FAILED,
            'processing_error' => 'Tác vụ nền bị gián đoạn: ' . mb_substr($e->getMessage(), 0, 300),
        ]);
    }
}
