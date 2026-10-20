@extends('layouts.panel')
@section('title', 'Tài liệu yêu thích')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-heart text-danger"></i> Tài liệu đã lưu yêu thích
    </h1>
    <p class="small text-muted mb-0">Danh sách các tài liệu bạn đã đánh dấu để tiện ôn tập và tra cứu sau này</p>
</div>

@if($bookmarks->isEmpty())
    <x-empty icon="bi-heart" title="Bạn chưa lưu tài liệu nào vào mục yêu thích">
        Hãy khám phá Thư viện và bấm vào nút Yêu thích trên tài liệu bạn quan tâm.
        <div class="mt-3">
            <a href="{{ route('library.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-collection me-1"></i>Khám phá thư viện
            </a>
        </div>
    </x-empty>
@else
    <div class="row g-3">
        @foreach($bookmarks as $b)
            <div class="col-md-6">
                <x-doc-card :doc="$b->document" />
            </div>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $bookmarks->links() }}
    </div>
@endif

@endsection
