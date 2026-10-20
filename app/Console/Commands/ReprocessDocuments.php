<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDocumentJob;
use App\Models\Document;
use Illuminate\Console\Command;

class ReprocessDocuments extends Command
{
    protected $signature = 'daln:reprocess {--all : Xử lý lại cả tài liệu đã hoàn thành}';
    protected $description = 'Đưa các tài liệu lỗi/đang chờ (hoặc tất cả) vào hàng đợi xử lý nội dung.';

    public function handle(): int
    {
        $q = Document::query();
        if (!$this->option('all')) {
            $q->whereIn('processing_status', [Document::P_FAILED, Document::P_PENDING, Document::P_NEEDS_OCR]);
        }
        $n = 0;
        foreach ($q->cursor() as $doc) {
            $doc->update(['processing_status' => Document::P_PENDING, 'processing_error' => null]);
            ProcessDocumentJob::dispatch($doc->id);
            $n++;
        }
        $this->info("Đã đưa {$n} tài liệu vào hàng đợi.");

        return self::SUCCESS;
    }
}
