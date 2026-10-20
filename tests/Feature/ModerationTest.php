<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    private function setupDoc(): array
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $doc = Document::factory()->pending()->create(['user_id' => $owner->id, 'title' => 'Bai giang cho duyet']);
        return [$owner, $admin, $doc];
    }

    public function test_admin_approves_with_history_audit_and_notification(): void
    {
        [$owner, $admin, $doc] = $this->setupDoc();
        $this->get('/tai-lieu')->assertDontSee('Bai giang cho duyet');

        $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => 'approve'])->assertRedirect(route('admin.moderation.index'));

        $doc->refresh();
        $this->assertSame('approved', $doc->moderation_status);
        $this->assertSame($admin->id, $doc->reviewed_by);
        $this->assertNotNull($doc->approved_at);
        $this->assertDatabaseHas('document_reviews', ['document_id' => $doc->id, 'admin_id' => $admin->id, 'decision' => 'approve']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'document.approve', 'user_id' => $admin->id, 'subject_type' => 'document', 'subject_id' => $doc->id]);
        $this->assertSame(1, $owner->notifications()->count());
        $this->get('/tai-lieu')->assertSee('Bai giang cho duyet');
    }

    public function test_reject_and_changes_require_reason(): void
    {
        [$owner, $admin, $doc] = $this->setupDoc();
        foreach (['reject', 'changes'] as $d) {
            $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => $d])->assertSessionHasErrors('reason');
            $this->assertSame('pending', $doc->fresh()->moderation_status);
        }
        $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => 'reject', 'reason' => 'Nội dung không phù hợp'])->assertRedirect();
        $doc->refresh();
        $this->assertSame('rejected', $doc->moderation_status);
        $this->assertSame('Nội dung không phù hợp', $doc->review_note);
        $this->get('/tai-lieu')->assertDontSee('Bai giang cho duyet');

        // chủ sở hữu thấy lý do và gửi lại được
        $this->actingAs($owner)->get(route('documents.mine'))->assertSee('Nội dung không phù hợp');
        $this->actingAs($owner)->post(route('documents.resubmit', $doc))->assertRedirect();
        $this->assertSame('pending', $doc->fresh()->moderation_status);
    }

    public function test_invalid_transitions_are_refused(): void
    {
        [$owner, $admin, $doc] = $this->setupDoc();
        $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => 'hide', 'reason' => 'x'])->assertSessionHasErrors('reason'); // chưa duyệt không ẩn được
        $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => 'approve']);
        $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => 'approve'])->assertSessionHasErrors('reason');
        $this->assertSame(1, $doc->reviews()->count());
    }

    public function test_hide_requires_reason_and_removes_from_public(): void
    {
        [$owner, $admin, $doc] = $this->setupDoc();
        $doc->update(['moderation_status' => 'approved']);
        $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => 'hide'])->assertSessionHasErrors('reason');
        $this->actingAs($admin)->post(route('admin.moderation.decide', $doc), ['decision' => 'hide', 'reason' => 'Vi phạm bản quyền']);
        $this->assertSame('hidden', $doc->fresh()->moderation_status);
        $this->actingAs(User::factory()->create())->get(route('library.show', $doc))->assertNotFound();
        $this->actingAs($owner)->get(route('documents.edit', $doc))->assertForbidden();
    }

    public function test_normal_user_cannot_moderate(): void
    {
        [$owner, $admin, $doc] = $this->setupDoc();
        $other = User::factory()->create();
        $this->actingAs($other)->post(route('admin.moderation.decide', $doc), ['decision' => 'approve'])->assertForbidden();
        $this->actingAs($owner)->post(route('admin.moderation.decide', $doc), ['decision' => 'approve'])->assertForbidden();
        $this->assertSame('pending', $doc->fresh()->moderation_status);
    }
}
