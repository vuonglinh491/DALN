@props(['icon' => 'bi-inbox', 'title' => 'Chưa có dữ liệu'])
<div class="empty-state text-center py-5">
    <i class="bi {{ $icon }}"></i>
    <h2 class="h5 mt-2">{{ $title }}</h2>
    <div class="text-muted">{{ $slot }}</div>
</div>
