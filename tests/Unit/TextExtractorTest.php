<?php

namespace Tests\Unit;

use App\Services\Documents\ExtractionException;
use App\Services\Documents\TextExtractor;
use PHPUnit\Framework\TestCase;

class TextExtractorTest extends TestCase
{
    private function tmp(string $content = ''): string
    {
        $f = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents($f, $content);
        return $f;
    }

    public function test_txt_is_read_and_normalised(): void
    {
        $pages = (new TextExtractor())->extract($this->tmp("Xin chào\r\n\r\n\r\nthế giới"), 'txt');
        $this->assertSame("Xin chào\n\nthế giới", $pages[0]['text']);
        $this->assertNull($pages[0]['page']);
    }

    public function test_docx_text_is_extracted(): void
    {
        $f = tempnam(sys_get_temp_dir(), 'dx') . '.docx';
        $zip = new \ZipArchive();
        $zip->open($f, \ZipArchive::CREATE);
        $zip->addFromString('word/document.xml', '<w:document><w:body><w:p><w:r><w:t>Xin chào &amp; thế giới</w:t></w:r></w:p><w:p><w:r><w:t>Đoạn hai</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        $this->assertSame("Xin chào & thế giới\nĐoạn hai", (new TextExtractor())->extract($f, 'docx')[0]['text']);
    }

    public function test_empty_file_is_rejected(): void
    {
        $this->expectException(ExtractionException::class);
        (new TextExtractor())->extract($this->tmp(''), 'txt');
    }

    public function test_corrupt_docx_is_rejected(): void
    {
        $this->expectException(ExtractionException::class);
        (new TextExtractor())->extract($this->tmp('không phải zip'), 'docx');
    }

    public function test_corrupt_pdf_is_rejected(): void
    {
        $this->expectException(ExtractionException::class);
        (new TextExtractor())->extract($this->tmp('%PDF-1.4 hỏng hoàn toàn'), 'pdf');
    }
}
