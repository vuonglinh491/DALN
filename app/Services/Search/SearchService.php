<?php

namespace App\Services\Search;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Tìm kiếm TỪ KHÓA (không phải ngữ nghĩa):
 *  - siêu dữ liệu (tiêu đề, mô tả, môn học, thẻ): LIKE theo từng từ;
 *  - nội dung tệp: MySQL FULLTEXT (BOOLEAN MODE, khớp tất cả từ >= 3 ký tự); môi trường không có MySQL (SQLite test) dùng LIKE.
 * Luôn giới hạn ở tài liệu công khai đã duyệt; tìm trong nội dung chỉ với tài liệu đã lập chỉ mục thành công.
 */
class SearchService
{
    public const SORTS = [
        'relevance' => 'Liên quan nhất',
        'newest' => 'Mới nhất',
        'popular' => 'Tải nhiều nhất',
        'views' => 'Xem nhiều nhất',
        'rating' => 'Đánh giá cao',
    ];

    public function search(array $f, int $perPage = 12): LengthAwarePaginator
    {
        $term = trim((string) ($f['q'] ?? ''));
        $words = $this->words($term);

        $q = Document::query()->published()
            ->with(['owner:id,name', 'category:id,name'])
            ->withAvg('ratings', 'score')
            ->withCount('ratings');

        if (!empty($f['category'])) {
            $q->where('category_id', (int) $f['category']);
        }
        if (!empty($f['tag'])) {
            $q->whereHas('tags', fn ($t) => $t->where('slug', $f['tag']));
        }
        if (!empty($f['type']) && in_array($f['type'], config('daln.upload.extensions'), true)) {
            $q->where('extension', $f['type']);
        }

        if ($words) {
            $q->where(function ($w) use ($words) {
                foreach ($words as $word) {
                    $like = '%' . $word . '%';
                    $w->orWhere('documents.title', 'like', $like)
                        ->orWhere('documents.description', 'like', $like)
                        ->orWhere('documents.subject', 'like', $like)
                        ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', $like));
                }
                $w->orWhereIn('documents.id', $this->contentMatchIds($words));
            });
        }

        $sort = $f['sort'] ?? ($words ? 'relevance' : 'newest');
        $this->applySort($q, $sort, $words);

        return $q->paginate($perPage)->withQueryString();
    }

    /** Gợi ý nhanh theo tiêu đề cho ô tìm kiếm (AJAX). */
    public function suggest(string $term, int $limit = 6): array
    {
        $words = $this->words($term);
        if (!$words) {
            return [];
        }
        $q = Document::query()->published();
        foreach ($words as $w) {
            $q->where('title', 'like', '%' . $w . '%');
        }

        return $q->orderByDesc('download_count')->limit($limit)->get(['id', 'title'])->all();
    }

    public function words(string $term): array
    {
        $term = mb_strtolower($term);
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array_values(array_unique(array_filter($parts, fn ($p) => mb_strlen($p) >= 2)));

        return array_slice($parts, 0, 6);
    }

    /** Truy vấn con trả về document_id có nội dung khớp. */
    private function contentMatchIds(array $words)
    {
        $sub = DocumentChunk::query()->select('document_chunks.document_id')
            ->whereIn('document_chunks.document_id', Document::query()->published()->indexed()->select('documents.id'));

        if (DB::connection()->getDriverName() === 'mysql') {
            $long = array_filter($words, fn ($w) => mb_strlen($w) >= 3);
            if (!$long) {
                return $sub->whereRaw('1 = 0');
            }
            $boolean = implode(' ', array_map(fn ($w) => '+' . $w . '*', $long));
            $sub->whereRaw('MATCH(document_chunks.content) AGAINST (? IN BOOLEAN MODE)', [$boolean]);
        } else {
            foreach ($words as $w) {
                $sub->where('document_chunks.content', 'like', '%' . $w . '%');
            }
        }

        return $sub;
    }

    private function applySort($q, string $sort, array $words): void
    {
        switch ($sort) {
            case 'popular':
                $q->orderByDesc('documents.download_count')->orderByDesc('documents.id');
                break;
            case 'views':
                $q->orderByDesc('documents.view_count')->orderByDesc('documents.id');
                break;
            case 'rating':
                $q->orderByDesc('ratings_avg_score')->orderByDesc('ratings_count')->orderByDesc('documents.id');
                break;
            case 'relevance':
                if ($words) {
                    $parts = [];
                    $bind = [];
                    foreach ($words as $w) {
                        $like = '%' . $w . '%';
                        $parts[] = '(CASE WHEN documents.title LIKE ? THEN 3 ELSE 0 END + CASE WHEN documents.subject LIKE ? THEN 2 ELSE 0 END + CASE WHEN documents.description LIKE ? THEN 1 ELSE 0 END)';
                        array_push($bind, $like, $like, $like);
                    }
                    $q->orderByRaw('(' . implode(' + ', $parts) . ') DESC', $bind)->orderByDesc('documents.id');
                    break;
                }
                // không có từ khóa: rơi xuống newest
            default:
                $q->orderByDesc('documents.approved_at')->orderByDesc('documents.id');
        }
    }
}
