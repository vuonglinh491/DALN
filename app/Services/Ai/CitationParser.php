<?php

namespace App\Services\Ai;

class CitationParser
{
    /** Lấy các số nguồn hợp lệ (1..$max) xuất hiện dạng [1], [2, 3], [1][4]. */
    public static function extract(string $answer, int $max): array
    {
        preg_match_all('/\[(\d{1,2}(?:\s*[,;]\s*\d{1,2})*)\]/', $answer, $m);
        $refs = [];
        foreach ($m[1] as $group) {
            foreach (preg_split('/\s*[,;]\s*/', $group) as $n) {
                $n = (int) $n;
                if ($n >= 1 && $n <= $max) {
                    $refs[$n] = $n;
                }
            }
        }
        sort($refs);

        return array_values($refs);
    }

    /** Xóa các dấu [n] mà n không tồn tại trong nguồn đã cấp (tránh AI bịa số nguồn). */
    public static function stripInvalid(string $answer, int $max): string
    {
        return preg_replace_callback('/\s?\[(\d{1,2})\]/', function ($m) use ($max) {
            $n = (int) $m[1];
            return ($n >= 1 && $n <= $max) ? $m[0] : '';
        }, $answer);
    }
}
