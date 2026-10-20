<?php

namespace App\Services\Search;

use App\Models\DocumentChunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Truy xuất các đoạn (chunk) liên quan cho RAG bằng MySQL FULLTEXT (từ khóa), có dự phòng LIKE.
 * ĐÂY KHÔNG PHẢI semantic search (chưa dùng embeddings).
 * $docQuery: truy vấn Eloquent chọn các tài liệu NGƯỜI DÙNG ĐƯỢC PHÉP dùng (đã áp dụng quyền).
 */
class Retriever
{
    private const STOP = [
        'là', 'và', 'của', 'có', 'không', 'những', 'các', 'một', 'cho', 'được', 'trong', 'với', 'này', 'đó', 'gì', 'nào',
        'như', 'thế', 'sao', 'hãy', 'về', 'tôi', 'bạn', 'khi', 'để', 'đã', 'sẽ', 'ra', 'vào', 'theo', 'từ', 'tại', 'hay',
        'hoặc', 'thì', 'mà', 'cũng', 'rất', 'nhiều', 'ít', 'bao', 'nhiêu', 'ơi', 'ạ', 'nhé', 'cần', 'muốn', 'biết',
        'the', 'and', 'what', 'how', 'is', 'are', 'of', 'to', 'in', 'a', 'an',
    ];

    public function terms(string $question): array
    {
        $parts = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($question), -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_filter($parts, fn ($p) => mb_strlen($p) >= 2 && !in_array($p, self::STOP, true));

        return array_slice(array_values(array_unique($parts)), 0, 12);
    }

    public function search($docQuery, string $question, int $k): Collection
    {
        $terms = $this->terms($question);
        if (!$terms) {
            return collect();
        }
        $ids = $docQuery->select('documents.id');

        if (DB::connection()->getDriverName() === 'mysql') {
            $match = 'MATCH(document_chunks.content) AGAINST (? IN NATURAL LANGUAGE MODE)';
            $query = implode(' ', $terms);
            $rows = DocumentChunk::query()
                ->select('document_chunks.*')
                ->selectRaw("$match as score", [$query])
                ->whereIn('document_chunks.document_id', $ids)
                ->whereRaw($match, [$query])
                ->orderByDesc('score')
                ->limit($k)
                ->get();
            if ($rows->isNotEmpty()) {
                return $rows;
            }
        }

        return $this->likeFallback($ids, $terms, $k);
    }

    private function likeFallback($ids, array $terms, int $k): Collection
    {
        $candidates = DocumentChunk::query()
            ->whereIn('document_chunks.document_id', $ids)
            ->where(function ($w) use ($terms) {
                foreach ($terms as $t) {
                    $w->orWhere('document_chunks.content', 'like', '%' . $t . '%');
                }
            })
            ->limit(400)->get();

        $asciiTerms = array_map(fn ($t) => Str::lower(Str::ascii($t)), $terms);
        $need = count($terms) >= 3 ? 2 : 1;

        return $candidates->map(function ($c) use ($asciiTerms) {
            $hay = Str::lower(Str::ascii($c->content));
            $matched = 0;
            $freq = 0;
            foreach ($asciiTerms as $t) {
                $n = substr_count($hay, $t);
                if ($n > 0) {
                    $matched++;
                    $freq += $n;
                }
            }
            $c->score = $matched * 10 + min($freq, 5);
            $c->matched_terms = $matched;

            return $c;
        })->filter(fn ($c) => $c->matched_terms >= $need)->sortByDesc('score')->take($k)->values();
    }
}
