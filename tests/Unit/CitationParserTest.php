<?php

namespace Tests\Unit;

use App\Services\Ai\CitationParser;
use PHPUnit\Framework\TestCase;

class CitationParserTest extends TestCase
{
    public function test_extracts_valid_refs_only(): void
    {
        $this->assertSame([1, 2, 3], CitationParser::extract('A [1]. B [2, 3]. C [9]. D [0].', 3));
        $this->assertSame([], CitationParser::extract('Không có nguồn', 3));
    }

    public function test_strips_invalid_markers(): void
    {
        $this->assertSame('A [1]. B.', CitationParser::stripInvalid('A [1]. B [7].', 3));
    }
}
