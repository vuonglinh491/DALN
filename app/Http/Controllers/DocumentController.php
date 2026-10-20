<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Jobs\ProcessDocumentJob;
use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Tag;
use App\Services\Audit\AuditLogger;
use App\Services\Documents\DocumentStorage;
use App\Services\Documents\DocumentWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function create()
    {
        return view('documents.create', ['categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get()]);
    }

    public function store(StoreDocumentRequest $request, DocumentStorage $storage)
    {
        $user = $request->user();   // chủ sở hữu luôn lấy từ phiên đăng nhập
        $d = $request->validated();
        $file = $request->file('file');

        if ($this->isDuplicate(hash_file('sha256', $file->getRealPath()), $user->id, null)) {
            return back()->withInput()->withErrors(['file' => 'Tệp này đã được tải lên trước đó (trùng nội dung).']);
        }

        $meta = $storage->store($file, $user);
        $public = $d['visibility'] === 'public';

        $doc = DB::transaction(function () use ($d, $user, $meta, $public) {
            $doc = Document::create($meta + [
                'user_id' => $user->id,
                'category_id' => $d['category_id'],
                'title' => $d['title'],
                'description' => $d['description'] ?? null,
                'subject' => $d['subject'] ?? null,
                'visibility' => $d['visibility'],
                'moderation_status' => $public ? Document::PENDING : Document::DRAFT, // tài liệu mới công khai luôn chờ duyệt
                'processing_status' => Document::P_PENDING,
                'submitted_at' => $public ? now() : null,
                'current_version' => 1,
            ]);
            DocumentVersion::create([
                'document_id' => $doc->id, 'version_number' => 1, 'original_filename' => $meta['original_filename'],
                'storage_path' => $meta['storage_path'], 'size_bytes' => $meta['size_bytes'], 'checksum' => $meta['checksum'],
                'created_by' => $user->id,
            ]);
            $doc->tags()->sync($this->tagIds($d['tags'] ?? ''));

            return $doc;
        });

        AuditLogger::record('document.upload', $doc, ['title' => $doc->title, 'visibility' => $doc->visibility]);
        ProcessDocumentJob::dispatch($doc->id);

        return redirect()->route('documents.mine')->with('success', $public
            ? 'Đã tải lên. Tài liệu đang chờ quản trị viên kiểm duyệt và đang được xử lý nội dung.'
            : 'Đã tải lên tài liệu riêng tư. Nội dung đang được xử lý.');
    }

    public function mine(Request $request)
    {
        $status = $request->query('status');
        $docs = Document::where('user_id', $request->user()->id)
            ->when(in_array($status, array_keys(Document::MODERATION_LABELS), true), fn ($q) => $q->where('moderation_status', $status))
            ->with('category:id,name')->latest()->paginate(10)->withQueryString();

        return view('documents.mine', ['documents' => $docs, 'status' => $status]);
    }

    public function edit(Document $document)
    {
        $this->authorize('update', $document);

        return view('documents.edit', [
            'document' => $document->load('tags', 'reviews.admin:id,name'),
            'categories' => Category::active()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document, DocumentStorage $storage)
    {
        $this->authorize('update', $document);
        $user = $request->user();
        $d = $request->validated();
        $file = $request->file('file');

        if ($file && $this->isDuplicate(hash_file('sha256', $file->getRealPath()), $user->id, $document->id)) {
            return back()->withInput()->withErrors(['file' => 'Tệp này đã được tải lên trước đó (trùng nội dung).']);
        }

        $metaChanged = $d['title'] !== $document->title
            || ($d['description'] ?? null) !== $document->description
            || ($d['subject'] ?? null) !== $document->subject
            || (int) $d['category_id'] !== (int) $document->category_id;

        $reprocess = false;
        DB::transaction(function () use ($d, $document, $file, $storage, $user, &$reprocess) {
            $document->fill([
                'title' => $d['title'], 'description' => $d['description'] ?? null,
                'subject' => $d['subject'] ?? null, 'category_id' => $d['category_id'],
            ]);

            if ($file) {
                $meta = $storage->store($file, $user);
                $version = $document->current_version + 1;
                DocumentVersion::create([
                    'document_id' => $document->id, 'version_number' => $version, 'original_filename' => $meta['original_filename'],
                    'storage_path' => $meta['storage_path'], 'size_bytes' => $meta['size_bytes'], 'checksum' => $meta['checksum'],
                    'created_by' => $user->id,
                ]);
                $document->fill($meta + ['current_version' => $version, 'processing_status' => Document::P_PENDING, 'processing_error' => null, 'page_count' => null]);
                $reprocess = true;
            }
            $document->save();
            $document->tags()->sync($this->tagIds($d['tags'] ?? ''));
        });

        $this->applyVisibilityRules($document, $d['visibility'], $file !== null || $metaChanged);
        AuditLogger::record('document.update', $document, ['new_file' => $file !== null]);
        if ($reprocess) {
            ProcessDocumentJob::dispatch($document->id);
        }

        return redirect()->route('documents.mine')->with('success', 'Đã cập nhật tài liệu.' . ($document->moderation_status === Document::PENDING ? ' Tài liệu đang ở trạng thái chờ duyệt.' : ''));
    }

    /** Chính sách tái kiểm duyệt + đổi chế độ chia sẻ. */
    private function applyVisibilityRules(Document $doc, string $wantVisibility, bool $contentChanged): void
    {
        $status = $doc->moderation_status;
        if ($wantVisibility === 'public' && $doc->visibility === 'private') {
            $doc->update(['visibility' => 'public', 'moderation_status' => Document::PENDING, 'submitted_at' => now()]);
        } elseif ($wantVisibility === 'private' && $doc->visibility === 'public') {
            $doc->update(['visibility' => 'private', 'moderation_status' => Document::DRAFT]);
        } elseif ($wantVisibility === 'public' && $status === Document::APPROVED && $contentChanged) {
            // Sửa tệp/tiêu đề/mô tả/danh mục của tài liệu đã duyệt => quay lại hàng chờ, tạm thời ngừng công khai
            $doc->update(['moderation_status' => Document::PENDING, 'submitted_at' => now()]);
        }
    }

    public function resubmit(Document $document, DocumentWorkflow $workflow)
    {
        $this->authorize('update', $document);
        try {
            $workflow->submit($document);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã gửi lại tài liệu để kiểm duyệt.');
    }

    public function reprocess(Document $document)
    {
        $this->authorize('update', $document);
        if (!in_array($document->processing_status, [Document::P_FAILED, Document::P_NEEDS_OCR, Document::P_PENDING], true)) {
            return back()->with('error', 'Tài liệu không cần xử lý lại.');
        }
        $document->update(['processing_status' => Document::P_PENDING, 'processing_error' => null]);
        ProcessDocumentJob::dispatch($document->id);

        return back()->with('success', 'Đã đưa tài liệu vào hàng đợi xử lý lại.');
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);
        $title = $document->title;
        $document->delete(); // xóa mềm: giữ lại lịch sử, tệp vẫn nằm trong storage để khôi phục qua database nếu cần
        AuditLogger::record('document.delete', $document, ['title' => $title]);

        return redirect()->route('documents.mine')->with('success', 'Đã xóa tài liệu «' . $title . '».');
    }

    /** Trùng nếu cùng người dùng đã có tệp này, hoặc tệp này đã công khai trong thư viện. */
    private function isDuplicate(string $checksum, int $userId, ?int $ignoreId): bool
    {
        return Document::where('checksum', $checksum)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere(fn ($p) => $p->published());
            })->exists();
    }

    private function tagIds(string $raw): array
    {
        $names = collect(explode(',', $raw))->map(fn ($t) => trim(Str::limit($t, 40, '')))->filter()->unique(fn ($t) => Str::lower($t))->take(8);
        $ids = [];
        foreach ($names as $name) {
            $slug = Str::slug($name) ?: Str::lower(Str::random(6));
            $ids[] = Tag::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
        }

        return $ids;
    }
}
