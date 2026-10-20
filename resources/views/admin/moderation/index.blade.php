@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Kiểm duyệt tài liệu')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-patch-check text-primary"></i> Hàng chờ kiểm duyệt tài liệu
    </h1>
    <p class="small text-muted mb-0">Xem xét nội dung, kiểm tra tính hợp lệ và phê duyệt tài liệu trước khi công khai</p>
</div>

{{-- Status Filter Tabs --}}
<ul class="nav nav-pills mb-4 flex-wrap gap-1">
    @foreach(['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối', 'changes_requested' => 'Cần sửa đổi', 'hidden' => 'Bị ẩn', 'all' => 'Tất cả'] as $k => $label)
        <li class="nav-item">
            <a class="nav-link py-2 px-3 {{ $status === $k ? 'active' : 'bg-white border text-dark' }}" href="{{ route('admin.moderation.index', ['status' => $k]) }}">
                {{ $label }}
                @if($k !== 'all')
                    <span class="badge rounded-pill {{ $status === $k ? 'bg-white text-primary' : 'bg-light text-dark border' }} ms-1">
                        {{ $counts[$k] ?? 0 }}
                    </span>
                @endif
            </a>
        </li>
    @endforeach
</ul>

{{-- Search Box --}}
<form method="GET" class="card border-0 shadow-xs p-3 mb-4 d-flex flex-row gap-2">
    <input type="hidden" name="status" value="{{ $status }}">
    <label class="visually-hidden" for="q">Tìm kiếm</label>
    <div class="search-input-wrap flex-grow-1">
        <i class="bi bi-search search-icon"></i>
        <input id="q" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Tìm tài liệu theo tiêu đề…">
    </div>
    <button class="btn btn-primary btn-sm px-3">Tìm</button>
</form>

@if($docs->isEmpty())
    <x-empty icon="bi-patch-check" title="Không có tài liệu nào trong mục này" />
@else
    <div class="card border-0 shadow-xs table-responsive overflow-hidden">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tài liệu</th>
                    <th>Người gửi</th>
                    <th>Gửi lúc</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($docs as $d)
                    <tr>
                        <td>
                            @php
                                $ext = strtolower($d->extension ?? '');
                                $extClass = match($ext) {
                                    'pdf' => 'ext-pdf',
                                    'docx' => 'ext-docx',
                                    default => 'ext-txt',
                                };
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <span class="file-ext-badge {{ $extClass }}">{{ strtoupper($d->extension) }}</span>
                                <div>
                                    <strong class="text-dark d-block text-truncate" style="max-width: 340px;">{{ $d->title }}</strong>
                                    <div class="small text-muted">{{ $d->category->name ?? '' }} · {{ $d->human_size }} · v{{ $d->current_version }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $d->owner->name }}</div>
                            <div class="small text-muted">{{ $d->owner->email }}</div>
                        </td>
                        <td class="small text-muted">
                            <i class="bi bi-clock me-1"></i>{{ $d->submitted_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <x-status-badge :label="$d->moderation_label[0]" :tone="$d->moderation_label[1]" />
                                <x-status-badge :label="$d->processing_label[0]" :tone="$d->processing_label[1]" />
                            </div>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-primary" href="{{ route('admin.moderation.show', $d) }}">
                                <i class="bi bi-check2-square me-1"></i>Xem & duyệt
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $docs->links() }}
    </div>
@endif

@endsection
