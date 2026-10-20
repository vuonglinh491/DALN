@extends('layouts.panel')
@section('title', 'Thông báo hệ thống')
@section('panel')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-bell text-primary"></i> Thông báo của bạn
        </h1>
        <p class="small text-muted mb-0">Cập nhật về kết quả kiểm duyệt tài liệu và hoạt động liên quan</p>
    </div>
    @if(auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('notifications.readAll') }}">@csrf
            <button class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-check2-all me-1"></i>Đánh dấu tất cả đã đọc
            </button>
        </form>
    @endif
</div>

@if($notifications->isEmpty())
    <x-empty icon="bi-bell" title="Bạn không có thông báo mới">
        Khi tài liệu của bạn được duyệt hoặc có phản hồi, thông báo sẽ xuất hiện tại đây.
    </x-empty>
@else
    <div class="vstack gap-2">
        @foreach($notifications as $n)
            <form method="POST" action="{{ route('notifications.read', $n->id) }}">@csrf
                <button class="btn text-start p-3 w-100 rounded-3 border-0 transition-all {{ $n->read_at ? 'bg-white shadow-xs' : 'bg-primary-soft border-primary shadow-sm' }}" type="submit">
                    <div class="d-flex align-items-start gap-3">
                        <span class="stat-icon {{ $n->read_at ? 'info' : 'primary' }} mb-0 flex-shrink-0" style="width:36px;height:36px;font-size:1.1rem;">
                            <i class="bi {{ $n->read_at ? 'bi-bell' : 'bi-bell-fill' }}"></i>
                        </span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="fw-bold text-dark">{{ $n->data['message'] ?? 'Thông báo hệ thống' }}</span>
                                <span class="small text-muted text-nowrap ms-2">{{ $n->created_at->diffForHumans() }}</span>
                            </div>
                            @if(!empty($n->data['reason']))
                                <div class="small text-muted bg-white p-2 rounded border mt-1">
                                    <strong>Chi tiết / Lý do:</strong> {{ $n->data['reason'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                </button>
            </form>
        @endforeach
    </div>
    <div class="mt-4 d-flex justify-content-center">
        {{ $notifications->links() }}
    </div>
@endif

@endsection
