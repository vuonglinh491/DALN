<?php

namespace Tests\Feature;

use App\Jobs\ProcessDocumentJob;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'title' => 'Giáo trình giải tích', 'description' => 'Mô tả', 'subject' => 'Toán',
            'category_id' => Category::factory()->create()->id, 'tags' => 'giải tích, đạo hàm', 'visibility' => 'public',
        ], $extra);
    }

    public function test_guest_cannot_upload(): void
    {
        $this->post('/tai-len', [])->assertRedirect(route('login'));
    }

    public function test_valid_txt_upload_is_stored_pending_and_queued(): void
    {
        Storage::fake('local');
        Queue::fake();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('giai-tich.txt', "Đạo hàm biểu thị tốc độ thay đổi của hàm số.\n");

        $this->actingAs($user)->post('/tai-len', $this->payload(['file' => $file, 'user_id' => $other->id]))->assertRedirect(route('documents.mine'));

        $doc = Document::first();
        $this->assertNotNull($doc);
        $this->assertSame($user->id, $doc->user_id, 'Chủ sở hữu phải lấy từ phiên đăng nhập, không từ client');
        $this->assertSame('pending', $doc->moderation_status);
        $this->assertSame('pending', $doc->processing_status);
        $this->assertSame(hash('sha256', "Đạo hàm biểu thị tốc độ thay đổi của hàm số.\n"), $doc->checksum);
        $this->assertSame(2, $doc->tags()->count());
        $this->assertDatabaseHas('document_versions', ['document_id' => $doc->id, 'version_number' => 1]);
        Storage::disk('local')->assertExists($doc->storage_path);
        Queue::assertPushed(ProcessDocumentJob::class, fn ($j) => $j->documentId === $doc->id);
    }

    public function test_private_upload_starts_as_draft(): void
    {
        Storage::fake('local');
        Queue::fake();
        $user = User::factory()->create();
        $this->actingAs($user)->post('/tai-len', $this->payload(['visibility' => 'private', 'file' => UploadedFile::fake()->createWithContent('a.txt', 'noi dung rieng')]));
        $this->assertSame('draft', Document::first()->moderation_status);
    }

    public function test_invalid_files_are_rejected(): void
    {
        Storage::fake('local');
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/tai-len', $this->payload(['file' => UploadedFile::fake()->createWithContent('virus.exe', 'MZ....')]))->assertSessionHasErrors('file');
        $this->actingAs($user)->post('/tai-len', $this->payload(['file' => UploadedFile::fake()->createWithContent('gia.pdf', 'đây không phải pdf')]))->assertSessionHasErrors('file');
        $this->actingAs($user)->post('/tai-len', $this->payload(['file' => UploadedFile::fake()->createWithContent('gia.docx', 'không phải zip')]))->assertSessionHasErrors('file');
        $this->actingAs($user)->post('/tai-len', $this->payload(['file' => UploadedFile::fake()->createWithContent('rong.txt', '')]))->assertSessionHasErrors('file');
        $this->actingAs($user)->post('/tai-len', $this->payload(['file' => UploadedFile::fake()->create('lon.txt', 5000)]))->assertSessionHasErrors('file');
        $this->actingAs($user)->post('/tai-len', $this->payload())->assertSessionHasErrors('file');

        $this->assertSame(0, Document::count());
        Queue::assertNothingPushed();
    }

    public function test_duplicate_file_is_rejected(): void
    {
        Storage::fake('local');
        Queue::fake();
        $user = User::factory()->create();
        $p = fn () => $this->payload(['file' => UploadedFile::fake()->createWithContent('a.txt', 'cung noi dung')]);

        $this->actingAs($user)->post('/tai-len', $p())->assertRedirect(route('documents.mine'));
        $this->actingAs($user)->post('/tai-len', $p())->assertSessionHasErrors('file');
        $this->assertSame(1, Document::count());
    }

    public function test_editing_approved_document_sends_it_back_to_moderation(): void
    {
        Storage::fake('local');
        Queue::fake();
        $user = User::factory()->create();
        $doc = Document::factory()->approved()->create(['user_id' => $user->id, 'title' => 'Cu']);

        $this->actingAs($user)->put(route('documents.update', $doc), $this->payload(['title' => 'Moi', 'category_id' => $doc->category_id]))->assertRedirect(route('documents.mine'));
        $this->assertSame('pending', $doc->fresh()->moderation_status);
        $this->get('/tai-lieu')->assertDontSee('Moi');
    }
}
