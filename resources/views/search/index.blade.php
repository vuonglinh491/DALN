@extends('layouts.app')
@section('title', $q ? 'Tìm kiếm: ' . $q : 'Tìm kiếm tài liệu')
@section('content')

<div class="mb-4">
    <h1 class="h3 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-search text-primary"></i> Tra cứu tài liệu thông minh
    </h1>
    <p class="small text-muted mb-0">Tìm kiếm theo tiêu đề, mô tả, môn học, tác giả và nội dung trích xuất bên trong tài liệu</p>
</div>

{{-- Search Filter Form --}}
<form method="GET" action="{{ route('search') }}" class="card card-body mb-4 border-0 shadow-xs" role="search">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small fw-semibold" for="q">Từ khóa tìm kiếm</label>
            <div class="search-input-wrap">
                <i class="bi bi-search search-icon"></i>
                <input id="q" type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Nhập từ khóa cần tra cứu…" maxlength="100" required>
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold" for="category">Danh mục</label>
            <select id="category" name="category" class="form-select form-select-sm">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(($filters['category'] ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold" for="type">Định dạng</label>
            <select id="type" name="type" class="form-select form-select-sm">
                <option value="">Tất cả</option>
                @foreach(['pdf','docx','txt'] as $t)
                    <option value="{{ $t }}" @selected(($filters['type'] ?? '') === $t)>{{ strtoupper($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold" for="sort">Sắp xếp</label>
            <select id="sort" name="sort" class="form-select form-select-sm">
                @foreach($sorts as $k => $v)
                    <option value="{{ $k }}" @selected(($filters['sort'] ?? 'relevance') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary btn-sm w-100" type="submit" aria-label="Tìm kiếm">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</form>

@if($q === '')
    <x-empty icon="bi-search" title="Nhập từ khóa để bắt đầu tra cứu">
        Hệ thống hỗ trợ tìm kiếm FULLTEXT trong tiêu đề, tóm tắt và nội dung văn bản bên trong tệp tài liệu.
    </x-empty>
    @if(count($recent))
        <div class="text-center mt-3 small">
            <span class="text-muted me-2"><i class="bi bi-clock-history"></i> Tìm kiếm gần đây:</span>
            @foreach($recent as $r)
                <a class="badge badge-soft badge-primary text-decoration-none me-1" href="{{ route('search', ['q' => $r]) }}">{{ $r }}</a>
            @endforeach
        </div>
    @endif
@elseif($documents->isEmpty())
    <x-empty icon="bi-emoji-neutral" :title="'Không tìm thấy kết quả phù hợp cho “' . $q . '”'">
        Thử sử dụng từ khóa ngắn hơn, chung hơn hoặc kiểm tra lỗi chính tả.
        <div class="mt-3">
            <a href="{{ route('library.index') }}" class="btn btn-outline-primary btn-sm">Xem toàn bộ Thư viện</a>
        </div>
    </x-empty>
@else
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted small mb-0">Tìm thấy <strong class="text-dark">{{ $documents->total() }}</strong> kết quả phù hợp cho từ khóa “<strong>{{ $q }}</strong>”</p>
    </div>
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
