@php
$pendingCount = \App\Models\Document::where('moderation_status', 'pending')->count();
$openReportsCount = \App\Models\Report::where('status', 'open')->count();

$items = [
    ['admin.dashboard', 'bi-speedometer2', 'Tổng quan', 'admin.dashboard', null],
    ['admin.moderation.index', 'bi-patch-check', 'Kiểm duyệt tài liệu', 'admin.moderation.*', $pendingCount ?: null],
    ['admin.users.index', 'bi-people', 'Quản lý người dùng', 'admin.users.*', null],
    ['admin.categories.index', 'bi-tags', 'Danh mục & thẻ', 'admin.categories.*', null],
    ['admin.reports.index', 'bi-flag', 'Bình luận & báo cáo', 'admin.reports.*', $openReportsCount ?: null],
    ['admin.monitoring.index', 'bi-activity', 'Nhật ký & theo dõi', 'admin.monitoring.*', null],
    ['admin.settings.edit', 'bi-sliders', 'Cấu hình hệ thống', 'admin.settings.*', null],
];
@endphp

<div class="card side-card border-0 shadow-xs mb-3">
    <div class="p-3 border-bottom d-flex align-items-center gap-2">
        <span class="stat-icon primary mb-0" style="width:36px;height:36px;font-size:1.1rem;">
            <i class="bi bi-shield-lock-fill"></i>
        </span>
        <div>
            <div class="fw-bold text-dark">Khu vực Quản trị</div>
            <div class="small text-muted">Administrator Console</div>
        </div>
    </div>
    <div class="list-group list-group-flush p-1">
        @foreach($items as [$route, $icon, $label, $match, $count])
            <a href="{{ route($route) }}" class="list-group-item list-group-item-action {{ request()->routeIs($match) ? 'active' : '' }} d-flex justify-content-between align-items-center">
                <span><i class="bi {{ $icon }} me-2"></i>{{ $label }}</span>
                @if($count)
                    <span class="badge rounded-pill bg-warning text-dark">{{ $count }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
