@extends('layouts.app')
@section('title', 'Trang chủ · Hệ thống tra cứu & Trợ lý học tập thông minh')
@section('content')

{{-- Hero Section --}}
<section class="hero text-center mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="hero-pill-badge mb-3">
                <i class="bi bi-stars text-primary"></i> Trợ lý học tập thông minh tích hợp AI Gemini
            </div>
            <h1 class="display-6 fw-bolder mb-3 text-dark">
                Tra cứu tài liệu học tập & Hỏi đáp AI kèm trích dẫn
            </h1>
            <p class="lead text-muted mb-4 fs-6">
                Chia sẻ, tìm kiếm và giải đáp thắc mắc trực tiếp trên tài liệu học tập. AI trả lời chính xác, dẫn nguồn minh bạch với số trang cụ thể.
            </p>

            <form action="{{ route('search') }}" method="GET" class="mb-3" role="search">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden bg-white border p-1">
                    <span class="input-group-text bg-white border-0 ps-3 pe-2 text-muted">
                        <i class="bi bi-search fs-5"></i>
                    </span>
                    <label for="heroQ" class="visually-hidden">Từ khóa</label>
                    <input id="heroQ" type="search" name="q" class="form-control border-0 shadow-none px-2" placeholder="Nhập tên môn học, giáo trình, bài tập, tác giả…" maxlength="100">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">
                        <span>Tìm kiếm</span>
                    </button>
                </div>
            </form>

            <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 small text-muted">
                <span>Gợi ý tìm nhanh:</span>
                <a href="{{ route('search', ['q' => 'Giải tích']) }}" class="badge badge-soft badge-primary text-decoration-none">Giải tích</a>
                <a href="{{ route('search', ['q' => 'Đại số tuyến tính']) }}" class="badge badge-soft badge-primary text-decoration-none">Đại số tuyến tính</a>
                <a href="{{ route('search', ['q' => 'Lập trình']) }}" class="badge badge-soft badge-primary text-decoration-none">Lập trình</a>
                <a href="{{ route('search', ['q' => 'Kinh tế']) }}" class="badge badge-soft badge-primary text-decoration-none">Kinh tế</a>
            </div>
        </div>
    </div>
</section>

{{-- Key Stats --}}
<div class="row g-3 mb-5">
    <div class="col-4">
        <div class="stat text-center">
            <div class="stat-icon primary mx-auto"><i class="bi bi-file-earmark-text"></i></div>
            <div class="n">{{ number_format($stats['documents']) }}</div>
            <div class="text-muted small mt-1 fw-medium">Tài liệu công khai</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat text-center">
            <div class="stat-icon success mx-auto"><i class="bi bi-people"></i></div>
            <div class="n">{{ number_format($stats['users']) }}</div>
            <div class="text-muted small mt-1 fw-medium">Thành viên học tập</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat text-center">
            <div class="stat-icon info mx-auto"><i class="bi bi-cloud-arrow-down"></i></div>
            <div class="n">{{ number_format($stats['downloads']) }}</div>
            <div class="text-muted small mt-1 fw-medium">Lượt tải tài liệu</div>
        </div>
    </div>
</div>

{{-- Categories Section --}}
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0 text-dark">
            <i class="bi bi-grid text-primary me-2"></i>Danh mục tài liệu
        </h2>
        <a href="{{ route('library.index') }}" class="small fw-semibold">Xem tất cả <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @forelse($categories as $c)
            <a class="btn btn-light btn-sm shadow-xs border" href="{{ route('library.index', ['category' => $c->id]) }}">
                <i class="bi bi-folder2 text-primary me-1"></i>
                <span class="fw-medium">{{ $c->name }}</span>
                <span class="badge rounded-pill text-bg-light border ms-1">{{ $c->documents_count }}</span>
            </a>
        @empty
            <span class="text-muted small">Chưa có danh mục. Quản trị viên cần chạy <code>php artisan db:seed</code>.</span>
        @endforelse
    </div>
</div>

{{-- Latest Documents --}}
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1 text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>Mới cập nhật
            </h2>
            <div class="small text-muted">Những tài liệu mới nhất được kiểm duyệt và chia sẻ trên hệ thống</div>
        </div>
        <a href="{{ route('library.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
    </div>
    @if($latest->isEmpty())
        <x-empty icon="bi-journal-x" title="Chưa có tài liệu công khai">
            Hãy là người đầu tiên chia sẻ tài liệu hữu ích đến cộng đồng!
            <div class="mt-2">
                @auth
                    <a class="btn btn-primary btn-sm" href="{{ route('documents.create') }}"><i class="bi bi-cloud-arrow-up me-1"></i>Tải lên ngay</a>
                @else
                    <a class="btn btn-primary btn-sm" href="{{ route('register') }}"><i class="bi bi-person-plus me-1"></i>Đăng ký để bắt đầu</a>
                @endauth
            </div>
        </x-empty>
    @else
        <div class="row g-3">
            @foreach($latest as $d)
                <div class="col-sm-6 col-lg-3">
                    <x-doc-card :doc="$d" />
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Popular Documents --}}
@if($popular->isNotEmpty())
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1 text-dark">
                <i class="bi bi-fire text-danger me-2"></i>Được tải nhiều nhất
            </h2>
            <div class="small text-muted">Tài liệu hữu ích được nhiều bạn học quan tâm và tải về</div>
        </div>
        <a href="{{ route('library.index', ['sort' => 'downloads']) }}" class="btn btn-sm btn-outline-primary">Xem bảng xếp hạng</a>
    </div>
    <div class="row g-3">
        @foreach($popular as $d)
            <div class="col-sm-6 col-lg-3">
                <x-doc-card :doc="$d" />
            </div>
        @endforeach
    </div>
</div>
@endif

@endsection
