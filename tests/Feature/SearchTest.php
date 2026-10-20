<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_only_published_documents(): void
    {
        $o = User::factory()->create();
        Document::factory()->approved()->create(['user_id' => $o->id, 'title' => 'Giao trinh Vat ly dai cuong']);
        Document::factory()->pending()->create(['user_id' => $o->id, 'title' => 'Vat ly cho duyet']);
        Document::factory()->create(['user_id' => $o->id, 'title' => 'Vat ly rieng tu']);

        $this->get('/tim-kiem?q=vat+ly')->assertOk()->assertSee('Giao trinh Vat ly dai cuong')->assertDontSee('Vat ly cho duyet')->assertDontSee('Vat ly rieng tu');
        $this->get('/tim-kiem?q=khongtontai')->assertOk()->assertSee('Không tìm thấy');
    }

    public function test_content_search_requires_indexed_published_document(): void
    {
        $o = User::factory()->create();
        $indexed = Document::factory()->approved()->indexed()->create(['user_id' => $o->id, 'title' => 'Tai lieu A']);
        $notIndexed = Document::factory()->approved()->create(['user_id' => $o->id, 'title' => 'Tai lieu B']); // processing_status = pending
        $pending = Document::factory()->pending()->indexed()->create(['user_id' => $o->id, 'title' => 'Tai lieu C']);
        foreach ([$indexed, $notIndexed, $pending] as $i => $d) {
            DocumentChunk::create(['document_id' => $d->id, 'chunk_index' => 0, 'content' => 'hien tuong quantum entanglement trong co hoc', 'char_count' => 45]);
        }

        $this->get('/tim-kiem?q=entanglement')->assertOk()->assertSee('Tai lieu A')->assertDontSee('Tai lieu B')->assertDontSee('Tai lieu C');
    }

    public function test_sorting_and_filters_work(): void
    {
        $o = User::factory()->create();
        $a = Document::factory()->approved()->create(['user_id' => $o->id, 'title' => 'Alpha', 'download_count' => 1]);
        $b = Document::factory()->approved()->create(['user_id' => $o->id, 'title' => 'Beta', 'download_count' => 9]);
        $this->get('/tai-lieu?sort=popular')->assertOk()->assertSeeInOrder(['Beta', 'Alpha']);
        $this->get('/tai-lieu?category=' . $a->category_id)->assertOk()->assertSee('Alpha')->assertDontSee('Beta');
        $this->get('/tim-kiem/goi-y?q=alp')->assertOk()->assertJsonFragment(['title' => 'Alpha']);
    }
}
