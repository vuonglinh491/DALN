@extends('layouts.panel')
@section('title', 'Tài liệu của tôi')
@section('panel')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-folder2-open text-primary"></i> Tài liệu của tôi
        </h1>
        <p class="small text-muted mb-0">Quản lý các tài liệu bạn đã đăng tải, trạng thái duyệt và lập chỉ mục AI</p>
    </div>
    <a class="btn btn-primary btn-sm" href="{{ route('documents.create') }}">
        <i class="bi bi-plus-lg me-1"></i>Tải lên tài liệu
    </a>
</div>

{{-- Filter Status Form --}}
<form method="GET" class="card border-0 shadow-xs p-3 mb-4 d-flex flex-row align-items-center justify-content-between gap-2">
    <div class="d-flex align-items-center gap-2">
        <label for="status" class="small fw-semibold text-muted mb-0 text-nowrap"><i class="bi bi-funnel me-1"></i>Lọc trạng thái:</label>
        <select id="status" name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            @foreach(\App\Models\Document::MODERATION_LABELS as $k => $v)
                <option value="{{ $k }}" @selected($status === $k)>{{ $v[0] }}</option>
            @endforeach
        </select>
        <noscript><button class="btn btn-light btn-sm">Lọc</button></noscript>
    </div>
    @if($status)
        <a href="{{ route('documents.mine') }}" class="small text-muted text-decoration-none">
            <i class="bi bi-x-circle me-1"></i>Bỏ lọc
        </a>
    @endif
</form>

@if($documents->isEmpty())
    <x-empty icon="bi-folder2" title="Bạn chưa có tài liệu nào">
        Chia sẻ tài liệu đầu tiên để lưu trữ hoặc chia sẻ với cộng đồng.
        <div class="mt-3">
            <a class="btn btn-primary btn-sm" href="{{ route('documents.create') }}">
                <i class="bi bi-cloud-arrow-up me-1"></i>Tải lên tài liệu đầu tiên
            </a>
        </div>
    </x-empty>
@else
    <div class="vstack gap-3">
        @foreach($documents as $d)
            <div class="card border-0 shadow-xs p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            @php
                                $ext = strtolower($d->extension ?? '');
                                $extClass = match($ext) {
                                    'pdf' => 'ext-pdf',
                                    'docx' => 'ext-docx',
                                    default => 'ext-txt',
                                };
                            @endphp
                            <span class="file-ext-badge {{ $extClass }}">{{ strtoupper($d->extension) }}</span>
                            <h2 class="h6 mb-0 fw-bold">
                                <a href="{{ route('library.show', $d) }}" class="text-dark text-decoration-none">{{ $d->title }}</a>
                            </h2>
                        </div>
                        <div class="small text-muted">
                            <span><i class="bi bi-folder2"></i> {{ $d->category->name ?? '—' }}</span>
                            <span class="mx-1">·</span>
                            <span>{{ $d->human_size }}</span>
                            <span class="mx-1">·</span>
                            <span>v{{ $d->current_version }}</span>
                            <span class="mx-1">·</span>
                            <span>{{ $d->created_at->format('d/m/Y H:i') }}</span>
                            <span class="mx-1">·</span>
                            <span>{{ $d->visibility === 'public' ? 'Công khai' : 'Riêng tư' }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-wrap align-items-start">
                        <x-status-badge :label="$d->moderation_label[0]" :tone="$d->moderation_label[1]" />
                        <x-status-badge :label="$d->processing_label[0]" :tone="$d->processing_label[1]" />
                    </div>
                </div>

                @if($d->review_note && in_array($d->moderation_status, ['rejected','changes_requested','hidden']))
                    <div class="alert alert-warning py-2 small mt-2 mb-0 border-0 shadow-xs">
                        <i class="bi bi-exclamation-circle-fill me-1"></i><strong>Lý do từ Ban quản trị:</strong> {{ $d->review_note }}
                    </div>
                @endif
                @if($d->processing_error && in_array($d->processing_status, ['failed','needs_ocr']))
                    <div class="alert alert-danger py-2 small mt-2 mb-0 border-0 shadow-xs">
                        <i class="bi bi-x-circle-fill me-1"></i><strong>Lỗi xử lý nội dung:</strong> {{ $d->processing_error }}
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                    @can('update', $d)
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('documents.edit', $d) }}">
                            <i class="bi bi-pencil me-1"></i>Chỉnh sửa
                        </a>
                    @endcan
                    @if(in_array($d->moderation_status, ['rejected','changes_requested']))
                        <form method="POST" action="{{ route('documents.resubmit', $d) }}">@csrf
                            <button class="btn btn-sm btn-primary">
                                <i class="bi bi-send me-1"></i>Gửi lại để duyệt
                            </button>
                        </form>
                    @endif
                    @if(in_array($d->processing_status, ['failed','needs_ocr','pending']))
                        <form method="POST" action="{{ route('documents.reprocess', $d) }}">@csrf
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-1"></i>Xử lý lại
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('documents.destroy', $d) }}" data-confirm="Xóa tài liệu «{{ $d->title }}»? Thao tác này không thể hoàn tác từ giao diện.">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>Xóa
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3 d-flex justify-content-center">{{ $documents->links() }}</div>
@endif

@endsection
