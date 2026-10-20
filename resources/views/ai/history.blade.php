@extends('layouts.panel')
@section('title', 'Lịch sử trò chuyện AI')
@section('panel')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-chat-left-text text-primary"></i> Lịch sử trò chuyện AI
        </h1>
        <p class="small text-muted mb-0">Các phiên hỏi đáp và làm việc tài liệu trước đây của bạn</p>
    </div>
    <a href="{{ route('ai.index') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Hội thoại mới
    </a>
</div>

@if($conversations->isEmpty())
    <x-empty icon="bi-chat-left-text" title="Chưa có phiên hội thoại nào">
        Bạn chưa tạo cuộc hội thoại nào với trợ lý AI.
        <div class="mt-3">
            <a href="{{ route('ai.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-stars me-1"></i>Bắt đầu hỏi AI ngay
            </a>
        </div>
    </x-empty>
@else
    <div class="card border-0 shadow-xs table-responsive overflow-hidden">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Tiêu đề hội thoại</th>
                    <th>Phạm vi tài liệu</th>
                    <th>Số tin</th>
                    <th>Thời điểm</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($conversations as $c)
                    <tr>
                        <td>
                            <a href="{{ route('ai.show', $c) }}" class="fw-semibold text-dark text-decoration-none">
                                <i class="bi bi-chat-dots text-primary me-1"></i>{{ $c->title }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-soft badge-primary">{{ \App\Models\Conversation::SCOPES[$c->scope] ?? $c->scope }}</span>
                            @if($c->document)
                                <div class="small text-muted text-truncate mt-1" style="max-width: 250px;">
                                    <i class="bi bi-file-earmark me-1"></i>{{ $c->document->title }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-light border">{{ $c->messages_count }}</span>
                        </td>
                        <td class="small text-muted">
                            {{ $c->updated_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('ai.destroy', $c) }}" data-confirm="Xóa hội thoại này và toàn bộ tin nhắn liên quan?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $conversations->links() }}
    </div>
@endif

@endsection
