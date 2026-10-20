@php
$unread = auth()->user()->unreadNotifications()->count();
$items = [
    ['documents.mine', 'bi-folder2-open', 'Tài liệu của tôi', 'documents.mine', null],
    ['documents.create', 'bi-cloud-arrow-up', 'Tải lên tài liệu', 'documents.create', null],
    ['bookmarks.index', 'bi-heart', 'Yêu thích', 'bookmarks.*', null],
    ['downloads.index', 'bi-download', 'Lịch sử tải xuống', 'downloads.*', null],
    ['ai.index', 'bi-stars', 'Trợ lý AI', 'ai.index', null],
    ['ai.history', 'bi-chat-left-text', 'Lịch sử trò chuyện', 'ai.history', null],
    ['notifications.index', 'bi-bell', 'Thông báo', 'notifications.*', $unread ?: null],
    ['profile.show', 'bi-person-gear', 'Hồ sơ cá nhân', 'profile.*', null],
];
@endphp

<div class="card side-card border-0 shadow-xs mb-3">
    <div class="p-3 border-bottom d-flex align-items-center gap-2">
        <span class="user-avatar-pill" style="width:36px;height:36px;font-size:0.95rem;">
            {{ mb_substr(auth()->user()->name, 0, 1) }}
        </span>
        <div class="min-w-0">
            <div class="fw-bold text-dark text-truncate">{{ auth()->user()->name }}</div>
            <div class="small text-muted text-truncate">{{ auth()->user()->email }}</div>
        </div>
    </div>
    <div class="list-group list-group-flush p-1">
        @foreach($items as [$route, $icon, $label, $match, $count])
            <a href="{{ route($route) }}" class="list-group-item list-group-item-action {{ request()->routeIs($match) ? 'active' : '' }} d-flex justify-content-between align-items-center">
                <span><i class="bi {{ $icon }} me-2"></i>{{ $label }}</span>
                @if($count)
                    <span class="badge rounded-pill bg-danger">{{ $count }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
