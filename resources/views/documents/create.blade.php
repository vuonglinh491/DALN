@extends('layouts.panel')
@section('title', 'Tải lên tài liệu mới')
@section('panel')

<div class="card border-0 shadow-sm p-4">
    <div class="mb-3 pb-2 border-bottom">
        <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-cloud-arrow-up text-primary"></i> Tải lên tài liệu học tập
        </h1>
        <p class="small text-muted mb-0">Chia sẻ giáo trình, đề thi, slide bài giảng hoặc tài liệu nghiên cứu của bạn</p>
    </div>
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" novalidate>@csrf
        @include('documents._form')
        <div class="d-flex align-items-center gap-2 pt-2">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i>Bắt đầu tải lên & xử lý
            </button>
            <a class="btn btn-light" href="{{ route('documents.mine') }}">Hủy bỏ</a>
        </div>
    </form>
</div>

@endsection
