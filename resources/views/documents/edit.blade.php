@extends('layouts.panel')
@section('title', 'Chỉnh sửa: ' . $document->title)
@section('panel')

<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="mb-3 pb-2 border-bottom">
        <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-pencil-square text-primary"></i> Chỉnh sửa tài liệu
        </h1>
        <p class="text-muted small mb-0">
            <i class="bi bi-info-circle me-1"></i>Chỉnh sửa tài liệu đã duyệt sẽ đưa tài liệu về trạng thái chờ Ban quản trị duyệt lại trước khi xuất hiện công khai.
        </p>
    </div>
    <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data" novalidate>@csrf @method('PUT')
        @include('documents._form')
        <div class="d-flex align-items-center gap-2 pt-2">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-check2-circle me-1"></i>Lưu thay đổi
            </button>
            <a class="btn btn-light" href="{{ route('documents.mine') }}">Hủy bỏ</a>
        </div>
    </form>
</div>

@if($document->reviews->isNotEmpty())
    <div class="card border-0 shadow-sm p-3">
        <h2 class="h6 fw-bold text-dark mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-secondary"></i> Lịch sử kiểm duyệt của tài liệu
        </h2>
        <div class="vstack gap-2 small">
            @foreach($document->reviews as $r)
                <div class="p-2 rounded bg-light border border-light-subtle d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted me-1">{{ $r->created_at->format('d/m/Y H:i') }}</span>
                        <strong class="text-dark">v{{ $r->version_number }}: {{ ['approve'=>'Đã duyệt','reject'=>'Từ chối','changes'=>'Yêu cầu sửa đổi','hide'=>'Đã ẩn','restore'=>'Hiện lại'][$r->decision] ?? $r->decision }}</strong>
                        @if($r->reason)<div class="text-muted fst-italic ps-2 border-start border-2 mt-1">“{{ $r->reason }}”</div>@endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@endsection
