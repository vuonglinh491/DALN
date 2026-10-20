@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Kiểm duyệt: ' . $document->title)
@section('panel')

<div class="mb-3">
    <a href="{{ route('admin.moderation.index') }}" class="btn btn-light btn-sm text-muted">
        <i class="bi bi-arrow-left me-1"></i>Quay lại hàng chờ kiểm duyệt
    </a>
</div>

{{-- Document Details Card --}}
<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <x-status-badge :label="$document->moderation_label[0]" :tone="$document->moderation_label[1]" />
        <x-status-badge :label="$document->processing_label[0]" :tone="$document->processing_label[1]" />
        @php
            $ext = strtolower($document->extension ?? '');
            $extClass = match($ext) {
                'pdf' => 'ext-pdf',
                'docx' => 'ext-docx',
                default => 'ext-txt',
            };
        @endphp
        <span class="file-ext-badge {{ $extClass }}">{{ strtoupper($document->extension) }}</span>
    </div>

    <h1 class="h4 fw-bold text-dark mb-3">{{ $document->title }}</h1>

    <div class="p-3 rounded bg-light mb-3">
        <dl class="row small mb-0">
            <dt class="col-sm-3 text-muted">Người đăng tải:</dt>
            <dd class="col-sm-9 fw-semibold">{{ $document->owner->name }} ({{ $document->owner->email }})</dd>

            <dt class="col-sm-3 text-muted">Danh mục / Môn học:</dt>
            <dd class="col-sm-9">{{ $document->category->name ?? '—' }} / {{ $document->subject ?: 'Chưa chỉ định' }}</dd>

            <dt class="col-sm-3 text-muted">Tệp tin gốc:</dt>
            <dd class="col-sm-9"><code>{{ $document->original_filename }}</code> · {{ $document->human_size }} · v{{ $document->current_version }}@if($document->page_count) · {{ $document->page_count }} trang @endif</dd>

            <dt class="col-sm-3 text-muted">Thẻ từ khóa:</dt>
            <dd class="col-sm-9">{{ $document->tags->pluck('name')->implode(', ') ?: 'Không có' }}</dd>

            <dt class="col-sm-3 text-muted">Thời điểm gửi duyệt:</dt>
            <dd class="col-sm-9">{{ $document->submitted_at?->format('d/m/Y H:i:s') ?? '—' }}</dd>
        </dl>
    </div>

    @if($document->description)
        <div class="mb-3 text-secondary">
            <strong>Mô tả tóm tắt:</strong> {{ $document->description }}
        </div>
    @endif

    @if($document->processing_error)
        <div class="alert alert-danger py-2 small mb-3 border-0">
            <i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Lỗi xử lý nền:</strong> {{ $document->processing_error }}
        </div>
    @endif

    {{-- Extracted Preview Box --}}
    <h2 class="h6 fw-bold text-dark mb-2">Văn bản đã trích xuất (Dùng cho tìm kiếm & AI):</h2>
    @if($preview)
        <div class="preview-box mb-3">{{ $preview }}</div>
    @else
        <div class="text-muted small py-3 mb-3 border rounded text-center">Chưa có nội dung văn bản trích xuất.</div>
    @endif

    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
        @if(in_array($document->extension, ['pdf','txt']))
            <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="{{ route('library.file', $document) }}">
                <i class="bi bi-box-arrow-up-right me-1"></i>Mở tệp gốc
            </a>
        @endif
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('library.download', $document) }}">
            <i class="bi bi-download me-1"></i>Tải tệp về máy
        </a>
        @if(in_array($document->processing_status, ['failed','needs_ocr','pending']))
            <form method="POST" action="{{ route('admin.moderation.reprocess', $document) }}">@csrf
                <button class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-arrow-clockwise me-1"></i>Xử lý lại tệp
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Decision Card --}}
@php $avail = collect($decisions)->filter(fn ($d) => in_array($document->moderation_status, $d[0]))->keys(); @endphp
<div class="card border-0 shadow-sm p-4 mb-4">
    <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-gavel text-primary"></i> Quyết định kiểm duyệt
    </h2>
    @if($avail->isEmpty())
        <p class="text-muted mb-0 small">Không có quyết định nào khả dụng ở trạng thái “{{ $document->moderation_label[0] }}”.</p>
    @else
        <form method="POST" action="{{ route('admin.moderation.decide', $document) }}">@csrf
            @php $labels = ['approve'=>'Duyệt công khai','reject'=>'Từ chối tài liệu','changes'=>'Yêu cầu chỉnh sửa','hide'=>'Ẩn tài liệu (vi phạm)','restore'=>'Hiện lại tài liệu']; @endphp
            <div class="d-flex flex-wrap gap-3 mb-3">
                @foreach($avail as $k)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="decision" id="d-{{ $k }}" value="{{ $k }}" required @checked(old('decision') === $k)>
                        <label class="form-check-label fw-semibold" for="d-{{ $k }}">{{ $labels[$k] }}</label>
                    </div>
                @endforeach
            </div>
            @error('decision')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

            <x-input name="reason" label="Lý do phản hồi (Bắt buộc khi từ chối, yêu cầu sửa đổi hoặc ẩn)" type="textarea" :rows="3" placeholder="Nhập lý do chi tiết để người đăng tài liệu nắm rõ thông tin…" />

            <button class="btn btn-primary" type="submit">
                <i class="bi bi-check2-circle me-1"></i>Ghi nhận quyết định
            </button>
        </form>
    @endif

    <div class="pt-3 mt-3 border-top d-flex justify-content-end">
        <form method="POST" action="{{ route('admin.moderation.destroy', $document) }}" data-confirm="Xóa tài liệu này vĩnh viễn? Chủ sở hữu sẽ không còn thấy tài liệu.">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash me-1"></i>Xóa tài liệu hoàn toàn
            </button>
        </form>
    </div>
</div>

{{-- Moderation History --}}
<div class="card border-0 shadow-sm p-4">
    <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-secondary"></i> Lịch sử các lần duyệt trước
    </h2>
    <div class="vstack gap-2">
        @forelse($document->reviews as $r)
            <div class="small p-2 rounded bg-light border border-light-subtle d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">{{ $r->created_at->format('d/m/Y H:i') }}</span> ·
                    <strong class="text-dark">{{ $r->admin->name ?? 'Quản trị viên' }}</strong>:
                    <span class="badge text-bg-primary ms-1">{{ $r->decision }} (v{{ $r->version_number }})</span>
                    @if($r->reason)<div class="text-muted fst-italic ps-2 border-start border-2 mt-1">“{{ $r->reason }}”</div>@endif
                </div>
            </div>
        @empty
            <span class="text-muted small">Chưa có lịch sử kiểm duyệt nào trước đó.</span>
        @endforelse
    </div>
</div>

@endsection
