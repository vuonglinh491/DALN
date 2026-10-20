<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\Documents\DocumentProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentProcessingTest extends TestCase
{
    use RefreshDatabase;

    private function doc(string $ext, string $content): Document
    {
        Storage::fake('local');
        $doc = Document::factory()->create(['extension' => $ext, 'storage_path' => "documents/t/file.$ext"]);
        Storage::disk('local')->put($doc->storage_path, $content);
        return $doc;
    }

    public function test_txt_is_processed_and_verified(): void
    {
        $doc = $this->doc('txt', str_repeat("Đạo hàm biểu thị tốc độ thay đổi của hàm số tại một điểm.\n", 60));
        app(DocumentProcessor::class)->process($doc);

        $doc->refresh();
        $this->assertSame('completed', $doc->processing_status);
        $this->assertGreaterThan(1, DocumentChunk::where('document_id', $doc->id)->count());
        $this->assertDatabaseHas('processing_jobs', ['document_id' => $doc->id, 'status' => 'completed']);
    }

    public function test_reprocessing_replaces_old_chunks(): void
    {
        $doc = $this->doc('txt', 'Nội dung phiên bản một đủ dài để lập chỉ mục.');
        app(DocumentProcessor::class)->process($doc);
        Storage::disk('local')->put($doc->storage_path, 'Nội dung phiên bản hai hoàn toàn khác biệt.');
        app(DocumentProcessor::class)->process($doc->fresh());

        $this->assertSame(1, DocumentChunk::where('document_id', $doc->id)->count());
        $this->assertStringContainsString('phiên bản hai', DocumentChunk::first()->content);
    }

    public function test_empty_and_corrupt_files_fail_and_are_not_marked_indexed(): void
    {
        foreach ([['txt', ''], ['docx', 'khong phai zip'], ['pdf', '%PDF-1.4 hong'], ['txt', '  ']] as [$ext, $content]) {
            $doc = $this->doc($ext, $content);
            app(DocumentProcessor::class)->process($doc);
            $doc->refresh();
            $this->assertContains($doc->processing_status, ['failed', 'needs_ocr'], "$ext phải lỗi");
            $this->assertNotSame('completed', $doc->processing_status);
            $this->assertNotEmpty($doc->processing_error);
            $this->assertSame(0, DocumentChunk::where('document_id', $doc->id)->count());
        }
        $this->assertGreaterThanOrEqual(4, \App\Models\ProcessingJob::whereIn('status', ['failed', 'needs_ocr'])->count());
    }

    public function test_missing_file_fails_cleanly(): void
    {
        Storage::fake('local');
        $doc = Document::factory()->create();
        app(DocumentProcessor::class)->process($doc);
        $this->assertSame('failed', $doc->fresh()->processing_status);
    }
}
