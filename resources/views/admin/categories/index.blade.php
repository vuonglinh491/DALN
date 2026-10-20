@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Danh mục & thẻ')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-tags text-primary"></i> Quản lý Danh mục & Thẻ
    </h1>
    <p class="small text-muted mb-0">Cấu hình danh mục phân loại tài liệu và quản lý các thẻ từ khóa hệ thống</p>
</div>

{{-- Add Category Card --}}
<div class="card border-0 shadow-sm p-4 mb-4">
    <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-plus-circle text-primary"></i> Thêm danh mục mới
    </h2>
    <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-2">@csrf
        <div class="col-md-4">
            <label class="form-label small fw-semibold" for="n">Tên danh mục <span class="text-danger">*</span></label>
            <input id="n" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="150" placeholder="Ví dụ: Công nghệ thông tin">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-5">
            <label class="form-label small fw-semibold" for="ds">Mô tả danh mục</label>
            <input id="ds" name="description" class="form-control form-control-sm" value="{{ old('description') }}" maxlength="500" placeholder="Mô tả tóm tắt nội dung danh mục…">
        </div>
        <div class="col-md-1">
            <label class="form-label small fw-semibold" for="so">Thứ tự</label>
            <input id="so" type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', 0) }}" min="0">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary btn-sm w-100">
                <i class="bi bi-plus-lg me-1"></i>Thêm mới
            </button>
        </div>
    </form>
</div>

{{-- Categories Table --}}
<div class="card border-0 shadow-xs table-responsive overflow-hidden mb-5">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Danh mục & Mô tả</th>
                <th>Thứ tự</th>
                <th>Tài liệu</th>
                <th>Hiển thị</th>
                <th class="text-end">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $c)
                <tr>
                    <td>
                        <form method="POST" action="{{ route('admin.categories.update', $c) }}" id="cf{{ $c->id }}">@csrf @method('PUT')</form>
                        <label class="visually-hidden" for="cn{{ $c->id }}">Tên</label>
                        <input id="cn{{ $c->id }}" form="cf{{ $c->id }}" name="name" value="{{ $c->name }}" class="form-control form-control-sm fw-semibold" required>
                        <input form="cf{{ $c->id }}" name="description" value="{{ $c->description }}" class="form-control form-control-sm mt-1 text-muted" placeholder="Mô tả" aria-label="Mô tả">
                    </td>
                    <td style="width:90px">
                        <input form="cf{{ $c->id }}" type="number" min="0" name="sort_order" value="{{ $c->sort_order }}" class="form-control form-control-sm text-center" aria-label="Thứ tự">
                    </td>
                    <td>
                        <span class="badge text-bg-light border">{{ $c->documents_count }}</span>
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input form="cf{{ $c->id }}" class="form-check-input" type="checkbox" name="is_active" value="1" id="ac{{ $c->id }}" @checked($c->is_active)>
                            <label class="form-check-label small" for="ac{{ $c->id }}">Bật</label>
                        </div>
                    </td>
                    <td class="text-end text-nowrap">
                        <button form="cf{{ $c->id }}" class="btn btn-sm btn-outline-primary" title="Lưu thay đổi">
                            <i class="bi bi-check2"></i> Lưu
                        </button>
                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="d-inline" data-confirm="Xóa danh mục «{{ $c->name }}»?">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Xóa danh mục">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Tags Management --}}
<div class="card border-0 shadow-sm p-4">
    <div class="mb-3 pb-2 border-bottom">
        <h2 class="h5 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-hash text-primary"></i> Quản lý Thẻ từ khóa (Tags)
        </h2>
        <p class="small text-muted mb-0">Thẻ giúp phân loại nội dung chi tiết theo chủ đề hoặc môn học</p>
    </div>

    <form method="POST" action="{{ route('admin.tags.store') }}" class="d-flex gap-2 mb-3">@csrf
        <label class="visually-hidden" for="tn">Tên thẻ</label>
        <input id="tn" name="name" class="form-control form-control-sm" placeholder="Nhập tên thẻ mới (vd: on-thi, de-thi-2024)…" required maxlength="60" style="max-width: 300px;">
        <button class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Thêm thẻ
        </button>
    </form>

    <div class="d-flex flex-wrap gap-2">
        @forelse($tags as $t)
            <form method="POST" action="{{ route('admin.tags.destroy', $t) }}" class="d-inline" data-confirm="Xóa thẻ #{{ $t->name }} khỏi {{ $t->documents_count }} tài liệu?">@csrf @method('DELETE')
                <span class="badge badge-soft badge-secondary fs-6 py-2 px-3 d-inline-flex align-items-center">
                    #{{ $t->name }}
                    <span class="badge rounded-pill text-bg-light border ms-1">{{ $t->documents_count }}</span>
                    <button class="btn btn-link btn-sm p-0 ms-2 text-danger border-0" aria-label="Xóa thẻ {{ $t->name }}" style="line-height:1;">
                        <i class="bi bi-x"></i>
                    </button>
                </span>
            </form>
        @empty
            <p class="text-muted small">Chưa có thẻ nào trong hệ thống.</p>
        @endforelse
    </div>
</div>

@endsection
