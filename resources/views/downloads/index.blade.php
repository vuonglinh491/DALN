@extends('layouts.panel')
@section('title', 'Lịch sử tải xuống')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-download text-primary"></i> Lịch sử tải xuống
    </h1>
    <p class="small text-muted mb-0">Nhật ký các tài liệu bạn đã tải về máy tính để học tập</p>
</div>

@if($downloads->isEmpty())
    <x-empty icon="bi-download" title="Bạn chưa tải tài liệu nào">
        Khám phá kho tài liệu phong phú để tìm kiếm tài liệu bạn cần.
        <div class="mt-3">
            <a href="{{ route('library.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-collection me-1"></i>Đến Thư viện
            </a>
        </div>
    </x-empty>
@else
    <div class="card border-0 shadow-xs table-responsive overflow-hidden">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tài liệu đã tải</th>
                    <th>Phiên bản</th>
                    <th>Thời điểm tải</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($downloads as $dl)
                    <tr>
                        <td>
                            @if($dl->document && Gate::allows('view', $dl->document))
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-check text-success fs-5"></i>
                                    <div>
                                        <a href="{{ route('library.show', $dl->document) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $dl->document->title }}
                                        </a>
                                        <div class="small text-muted">{{ $dl->document->category->name ?? '' }} · {{ strtoupper($dl->document->extension ?? '') }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted"><i class="bi bi-file-earmark-x me-1"></i>{{ $dl->document->title ?? '(Tài liệu đã bị xóa)' }} (Không còn khả dụng)</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-light border">v{{ $dl->version_number }}</span>
                        </td>
                        <td class="small text-muted">
                            <i class="bi bi-calendar3 me-1"></i>{{ $dl->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-end">
                            @if($dl->document && Gate::allows('view', $dl->document))
                                <a href="{{ route('library.download', $dl->document) }}" class="btn btn-light btn-sm border text-primary">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Tải lại
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $downloads->links() }}
    </div>
@endif

@endsection
