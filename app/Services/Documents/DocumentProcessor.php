<?php

namespace App\Services\Documents;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\ProcessingJob;
use App\Notifications\DocumentProcessingIssue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Trích xuất -> chia chunk -> lưu -> XÁC MINH số chunk thực tế rồi mới đánh dấu "completed".
 */
class DocumentProcessor
{
    public function __construct(private TextExtractor $extractor, private Chunker $chunker)
    {
    }

    public function process(Document $doc): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $job = ProcessingJob::create([
            'document_id' => $doc->id,
            'type' => 'extract_text',
            'status' => 'running',
            'attempts' => 1,
            'started_at' => now(),
        ]);
        $doc->update(['processing_status' => Document::P_PROCESSING, 'processing_error' => null]);

        try {
            $path = Storage::disk('local')->path($doc->storage_path);
            $pages = $this->extractor->extract($path, $doc->extension);

            $totalChars = 0;
            foreach ($pages as $p) {
                $totalChars += mb_strlen(trim($p['text']));
            }
            if ($totalChars < 20) {
                if ($doc->extension === 'pdf') {
                    throw new NeedsOcrException('PDF không có lớp văn bản (có thể là bản quét). Hệ thống chưa hỗ trợ OCR.');
                }
                throw new ExtractionException('Tệp không chứa nội dung văn bản để lập chỉ mục.');
            }

            $chunks = $this->chunker->chunk($pages);
            if (count($chunks) === 0) {
                throw new ExtractionException('Không tạo được đoạn nội dung nào từ tệp.');
            }

            DB::transaction(function () use ($doc, $chunks) {
                DocumentChunk::where('document_id', $doc->id)->delete();
                $now = now();
                foreach (array_chunk($chunks, 100) as $batch) {
                    DocumentChunk::insert(array_map(fn ($c) => [
                        'document_id' => $doc->id,
                        'chunk_index' => $c['index'],
                        'page_start' => $c['page_start'],
                        'page_end' => $c['page_end'],
                        'content' => $c['content'],
                        'char_count' => mb_strlen($c['content']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $batch));
                }
            });

            // Xác minh đầu ra thực tế trước khi tuyên bố hoàn thành
            $stored = DocumentChunk::where('document_id', $doc->id)->count();
            if ($stored !== count($chunks)) {
                throw new \RuntimeException('Số đoạn đã lưu (' . $stored . ') không khớp số đoạn tạo ra (' . count($chunks) . ').');
            }

            $doc->update([
                'processing_status' => Document::P_COMPLETED,
                'processing_error' => null,
                'page_count' => $doc->extension === 'pdf' ? count($pages) : null,
            ]);
            $job->update(['status' => 'completed', 'finished_at' => now()]);
        } catch (NeedsOcrException $e) {
            DocumentChunk::where('document_id', $doc->id)->delete();
            $doc->update(['processing_status' => Document::P_NEEDS_OCR, 'processing_error' => $e->getMessage()]);
            $job->update(['status' => 'needs_ocr', 'error' => $e->getMessage(), 'finished_at' => now()]);
            $this->notifyOwner($doc, $e->getMessage());
        } catch (\Throwable $e) {
            Log::warning('Xử lý tài liệu thất bại', ['document_id' => $doc->id, 'error' => $e->getMessage()]);
            DocumentChunk::where('document_id', $doc->id)->delete();
            $msg = $e instanceof ExtractionException ? $e->getMessage() : 'Lỗi xử lý không xác định: ' . mb_substr($e->getMessage(), 0, 300);
            $doc->update(['processing_status' => Document::P_FAILED, 'processing_error' => $msg]);
            $job->update(['status' => 'failed', 'error' => $msg, 'finished_at' => now()]);
            $this->notifyOwner($doc, $msg);
        }
    }

    private function notifyOwner(Document $doc, string $message): void
    {
        $doc->owner?->notify(new DocumentProcessingIssue($doc, $message));
    }
}
