<?php

namespace App\Services\Documents;

/**
 * Chia văn bản thành các đoạn (chunk) kích thước hợp lý, có chồng lấp nhẹ và giữ số trang.
 * Đầu vào: [['page' => int|null, 'text' => string], ...]
 * Đầu ra: [['index' => int, 'page_start' => ?int, 'page_end' => ?int, 'content' => string], ...]
 */
class Chunker
{
    private int $size;
    private int $overlap;

    public function __construct(?int $size = null, ?int $overlap = null)
    {
        $this->size = $size ?? (int) config('daln.rag.chunk_size', 1200);
        $this->overlap = $overlap ?? (int) config('daln.rag.chunk_overlap', 150);
    }

    public function chunk(array $pages): array
    {
        $units = [];
        foreach ($pages as $p) {
            foreach (preg_split('/\n+/', (string) $p['text']) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                foreach ($this->splitLong($line) as $piece) {
                    $units[] = [$p['page'], $piece];
                }
            }
        }

        $chunks = [];
        $buf = '';
        $carry = 0;   // số ký tự đầu buffer chỉ là phần chồng lấp từ chunk trước
        $ps = null;
        $pe = null;

        $flush = function () use (&$chunks, &$buf, &$ps, &$pe, &$carry) {
            if (mb_strlen($buf) > $carry && trim($buf) !== '') {
                $chunks[] = ['index' => count($chunks), 'page_start' => $ps, 'page_end' => $pe, 'content' => trim($buf)];
            }
        };

        foreach ($units as [$page, $text]) {
            if (mb_strlen($buf) > $carry && mb_strlen($buf) + mb_strlen($text) + 1 > $this->size) {
                $flush();
                $tail = $this->tail($buf);
                $buf = $tail;
                $carry = mb_strlen($tail);
                $ps = $tail === '' ? null : $pe; // phần chồng lấp thuộc trang cuối của chunk trước
            }
            if ($buf === '') {
                $ps = $page;
                $carry = 0;
            }
            $buf .= ($buf === '' ? '' : "\n") . $text;
            $pe = $page;
        }
        $flush();

        return $chunks;
    }

    /** Cắt dòng quá dài theo câu; câu quá dài thì cắt cứng theo kích thước. */
    private function splitLong(string $line): array
    {
        if (mb_strlen($line) <= $this->size) {
            return [$line];
        }
        $out = [];
        $cur = '';
        foreach (preg_split('/(?<=[\.\!\?;:])\s+/u', $line) as $sentence) {
            while (mb_strlen($sentence) > $this->size) {
                if ($cur !== '') {
                    $out[] = $cur;
                    $cur = '';
                }
                $out[] = mb_substr($sentence, 0, $this->size);
                $sentence = mb_substr($sentence, $this->size);
            }
            if ($cur !== '' && mb_strlen($cur) + mb_strlen($sentence) + 1 > $this->size) {
                $out[] = $cur;
                $cur = '';
            }
            $cur .= ($cur === '' ? '' : ' ') . $sentence;
        }
        if ($cur !== '') {
            $out[] = $cur;
        }

        return $out;
    }

    private function tail(string $buf): string
    {
        if ($this->overlap <= 0) {
            return '';
        }
        $t = mb_substr($buf, -$this->overlap);
        $pos = mb_strpos($t, ' ');
        if ($pos !== false && $pos < mb_strlen($t) - 1) {
            $t = mb_substr($t, $pos + 1); // bắt đầu ở ranh giới từ
        }

        return trim($t);
    }
}
