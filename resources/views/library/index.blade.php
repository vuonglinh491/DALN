@extends('layouts.app')
@section('title', 'Thư viện tài liệu')
@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark">Thư viện tài liệu</h1>
        <p class="text-muted small mb-0">Khám phá và tải xuống hàng ngàn tài liệu học tập, giáo trình chọn lọc</p>
    </div>
    @auth
        <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-cloud-arrow-up me-1"></i>Đăng tải tài liệu
        </a>
    @endauth
</div>

{{-- Filter Card --}}
<form method="GET" action="{{ route('library.index') }}" class="card card-body mb-4 shadow-xs border-0">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold" for="category"><i class="bi bi-folder2 me-1"></i>Danh mục</label>
            <select id="category" name="category" class="form-select form-select-sm">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(($filters['category'] ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold" for="type"><i class="bi bi-file-earmark me-1"></i>Định dạng</label>
            <select id="type" name="type" class="form-select form-select-sm">
                <option value="">Tất cả định dạng</option>
                @foreach(['pdf','docx','txt'] as $t)
                    <option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ strtoupper($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold" for="sort"><i class="bi bi-sort-down me-1"></i>Sắp xếp</label>
            <select id="sort" name="sort" class="form-select form-select-sm">
                @foreach($sorts as $k => $v)
                    <option value="{{ $k }}" @selected(($filters['sort'] ?? 'newest') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button class="btn btn-primary btn-sm w-100" type="submit">
                <i class="bi bi-funnel"></i> Lọc
            </button>
            @if(array_filter($filters ?? []))
                <a href="{{ route('library.index') }}" class="btn btn-light btn-sm text-muted" title="Xóa bộ lọc">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
    @if($tags->isNotEmpty())
        <div class="mt-3 pt-2 border-top small d-flex flex-wrap align-items-center gap-1">
            <span class="text-muted me-1"><i class="bi bi-tags text-secondary"></i> Thẻ phổ biến:</span>
            @foreach($tags as $t)
                <a class="badge badge-soft badge-secondary text-decoration-none {{ ($filters['tag'] ?? '') === $t->slug ? 'bg-primary text-white border-primary' : '' }}" href="{{ route('library.index', ['tag' => $t->slug]) }}">
                    #{{ $t->name }}
                </a>
            @endforeach
        </div>
    @endif
</form>

{{-- Document Grid --}}
@if($documents->isEmpty())
    <x-empty icon="bi-search" title="Không tìm thấy tài liệu phù hợp">
        Thử thay đổi bộ lọc hoặc quay lại danh mục tất cả tài liệu.
        <div class="mt-3">
            <a href="{{ route('library.index') }}" class="btn btn-outline-primary btn-sm">Xem tất cả tài liệu</a>
        </div>
    </x-empty>
@else
    <div class="row g-3">
        @foreach($documents as $d)
            <div class="col-sm-6 col-lg-4">
                <x-doc-card :doc="$d" />
            </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $documents->links() }}
    </div>
@endif

@endsection
