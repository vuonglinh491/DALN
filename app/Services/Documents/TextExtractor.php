<?php

namespace App\Services\Documents;

use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

/**
 * Trích xuất văn bản từ PDF (có lớp văn bản), DOCX và TXT.
 * Trả về danh sách trang: [['page' => int|null, 'text' => string], ...]
 * page = null với định dạng không có khái niệm trang cố định (DOCX, TXT).
 */
class TextExtractor
{
    public function extract(string $absPath, string $ext): array
    {
        $ext = strtolower($ext);
        clearstatcache(true, $absPath);
        if (!is_file($absPath)) {
            throw new ExtractionException('Không tìm thấy tệp trên máy chủ.');
        }
        if (filesize($absPath) === 0) {
            throw new ExtractionException('Tệp rỗng.');
        }

        switch ($ext) {
            case 'pdf':
                return $this->pdf($absPath);
            case 'docx':
                return $this->docx($absPath);
            case 'txt':
                return $this->txt($absPath);
        }
        throw new ExtractionException("Định dạng .{$ext} chưa được hỗ trợ.");
    }

    private function pdf(string $path): array
    {
        try {
            $config = new Config();
            $config->setRetainImageContent(false); // giảm bộ nhớ
            $pdf = (new Parser([], $config))->parseFile($path);
            $pdfPages = $pdf->getPages();
        } catch (\Throwable $e) {
            throw new ExtractionException('Không đọc được tệp PDF (tệp hỏng hoặc được mã hóa).', 0, $e);
        }

        $pages = [];
        foreach ($pdfPages as $i => $page) {
            try {
                $text = $page->getText();
            } catch (\Throwable $e) {
                $text = '';
            }
            $pages[] = ['page' => $i + 1, 'text' => $this->clean($text)];
        }

        return $pages;
    }

    private function docx(string $path): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new ExtractionException('Máy chủ thiếu extension PHP zip nên không đọc được DOCX.');
        }
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new ExtractionException('Tệp DOCX không hợp lệ hoặc bị hỏng.');
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml === false || $xml === '') {
            throw new ExtractionException('Tệp DOCX không có nội dung văn bản hợp lệ.');
        }

        $xml = preg_replace('#</w:p>#', "\n", $xml);
        $xml = preg_replace('#<w:br[^>]*/>#', "\n", $xml);
        $xml = preg_replace('#<w:tab/>|</w:tc>#', "\t", $xml);
        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return [['page' => null, 'text' => $this->clean($text)]];
    }

    private function txt(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new ExtractionException('Không đọc được tệp văn bản.');
        }
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw); // BOM
        if (!mb_check_encoding($raw, 'UTF-8')) {
            // Thử bảng mã tiếng Việt phổ biến trên Windows
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1258');
        }

        return [['page' => null, 'text' => $this->clean($raw)]];
    }

    private function clean(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');              // bỏ byte UTF-8 sai
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $text); // ký tự điều khiển
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t\x{00A0}]+/u', ' ', $text);
        $text = preg_replace('/ ?\n ?/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim((string) $text);
    }
}
