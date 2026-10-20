<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_and_private_documents_are_not_public(): void
    {
        $owner = User::factory()->create();
        $pending = Document::factory()->pending()->create(['user_id' => $owner->id, 'title' => 'Tai lieu cho duyet']);
        $private = Document::factory()->create(['user_id' => $owner->id, 'title' => 'Tai lieu rieng tu']);
        $approved = Document::factory()->approved()->create(['user_id' => $owner->id, 'title' => 'Tai lieu cong khai']);

        $this->get('/tai-lieu')->assertOk()->assertSee('Tai lieu cong khai')->assertDontSee('Tai lieu cho duyet')->assertDontSee('Tai lieu rieng tu');
        $this->get('/')->assertOk()->assertDontSee('Tai lieu cho duyet');

        $this->get(route('library.show', $approved))->assertOk();
        $this->get(route('library.show', $pending))->assertNotFound();
        $this->get(route('library.show', $private))->assertNotFound();

        $other = User::factory()->create();
        $this->actingAs($other)->get(route('library.show', $pending))->assertNotFound();
        $this->actingAs($other)->get(route('library.show', $private))->assertNotFound();
        $this->actingAs($owner)->get(route('library.show', $pending))->assertOk();
        $this->actingAs($owner)->get(route('library.show', $private))->assertOk();
    }

    public function test_admin_can_see_pending_public_but_not_others_private(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $pending = Document::factory()->pending()->create(['user_id' => $owner->id]);
        $private = Document::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($admin)->get(route('admin.moderation.show', $pending))->assertOk();
        $this->actingAs($admin)->get(route('admin.moderation.show', $private))->assertNotFound();
        $this->actingAs($admin)->get(route('library.show', $private))->assertNotFound();
    }

    public function test_download_requires_login_and_permission_and_is_recorded(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $doc = Document::factory()->approved()->create(['user_id' => $owner->id]);
        Storage::disk('local')->put($doc->storage_path, 'noi dung');
        $pending = Document::factory()->pending()->create(['user_id' => $owner->id]);
        Storage::disk('local')->put($pending->storage_path, 'cho duyet');

        $this->get(route('library.download', $doc))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get(route('library.download', $doc))->assertOk();
        $this->assertDatabaseHas('downloads', ['user_id' => $user->id, 'document_id' => $doc->id]);
        $this->assertSame(1, $doc->fresh()->download_count);

        $this->actingAs($user)->get(route('library.download', $pending))->assertNotFound();
        $this->assertDatabaseMissing('downloads', ['document_id' => $pending->id]);
    }

    public function test_only_owner_can_edit_or_delete(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $doc = Document::factory()->approved()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get(route('documents.edit', $doc))->assertForbidden();
        $this->actingAs($other)->delete(route('documents.destroy', $doc))->assertForbidden();
        $this->actingAs($owner)->delete(route('documents.destroy', $doc))->assertRedirect();
        $this->assertSoftDeleted('documents', ['id' => $doc->id]);
    }
}
