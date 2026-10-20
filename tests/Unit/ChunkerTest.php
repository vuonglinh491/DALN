<?php

namespace Tests\Unit;

use App\Services\Documents\Chunker;
use PHPUnit\Framework\TestCase;

class ChunkerTest extends TestCase
{
    public function test_chunks_are_bounded_and_keep_page_numbers(): void
    {
        $c = new Chunker(200, 40);
        $chunks = $c->chunk([
            ['page' => 1, 'text' => str_repeat('Đạo hàm biểu thị tốc độ thay đổi. ', 10)],
            ['page' => 2, 'text' => str_repeat('Tích phân tính diện tích. ', 15)],
        ]);

        $this->assertGreaterThan(2, count($chunks));
        foreach ($chunks as $i => $ch) {
            $this->assertSame($i, $ch['index']);
            $this->assertLessThanOrEqual(245, mb_strlen($ch['content']));
            $this->assertContains($ch['page_start'], [1, 2]);
            $this->assertGreaterThanOrEqual($ch['page_start'], $ch['page_end']);
        }
        $this->assertSame(1, $chunks[0]['page_start']);
        $this->assertSame(2, end($chunks)['page_end']);
        $this->assertStringContainsString('Tích phân tính diện tích.', implode(' ', array_column($chunks, 'content')));
    }

    public function test_single_very_long_line_is_split(): void
    {
        $chunks = (new Chunker(300, 30))->chunk([['page' => null, 'text' => str_repeat('abc def. ', 500)]]);
        $this->assertGreaterThan(5, count($chunks));
        $this->assertNull($chunks[0]['page_start']);
    }

    public function test_empty_text_gives_no_chunks(): void
    {
        $this->assertSame([], (new Chunker(200, 20))->chunk([['page' => 1, 'text' => "  \n\n "]]));
    }
}
