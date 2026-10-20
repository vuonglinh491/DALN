@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Tổng quan quản trị')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-speedometer2 text-primary"></i> Tổng quan hệ thống
    </h1>
    <p class="small text-muted mb-0">Theo dõi số liệu tài liệu, kiểm duyệt, người dùng và tình trạng vận hành nền</p>
</div>

{{-- Top Metrics Grid --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['Tổng tài liệu', $totalDocs, 'bi-files', 'primary', route('admin.moderation.index', ['status' => 'all'])],
        ['Chờ kiểm duyệt', $byModeration['pending'] ?? 0, 'bi-hourglass-split', 'pending', route('admin.moderation.index')],
        ['Người dùng', $users['total'] . ($users['locked'] ? ' (' . $users['locked'] . ' khóa)' : ''), 'bi-people', 'success', route('admin.users.index')],
        ['Lượt tải về', $downloads, 'bi-cloud-arrow-down', 'info', null],
        ['Báo cáo mở', $openReports, 'bi-flag', 'danger', route('admin.reports.index')],
        ['Câu hỏi AI hôm nay', $aiToday, 'bi-stars', 'primary', null],
        ['Lỗi AI (24h)', $aiFailed24h, 'bi-exclamation-triangle', 'danger', route('admin.monitoring.index', ['tab' => 'ai'])],
        ['Tài liệu lỗi xử lý', ($byProcessing['failed'] ?? 0) + ($byProcessing['needs_ocr'] ?? 0), 'bi-bug', 'pending', route('admin.monitoring.index', ['status' => 'failed'])],
    ];
    @endphp
    @foreach($cards as [$label, $val, $icon, $tone, $url])
        <div class="col-6 col-md-3">
            <div class="stat h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">{{ $label }}</span>
                    <span class="stat-icon {{ $tone }} mb-0" style="width:32px;height:32px;font-size:1rem;">
                        <i class="bi {{ $icon }}"></i>
                    </span>
                </div>
                <div class="n mt-auto">
                    @if($url)
                        <a class="text-decoration-none text-dark" href="{{ $url }}">{{ $val }}</a>
                    @else
                        {{ $val }}
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Breakdown Cards --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-xs h-100 p-4">
            <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-pie-chart text-primary"></i> Trạng thái kiểm duyệt
            </h2>
            <div class="vstack gap-2 mb-4">
                @foreach(\App\Models\Document::MODERATION_LABELS as $k => $v)
                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                        <x-status-badge :label="$v[0]" :tone="$v[1]" />
                        <strong class="text-dark">{{ $byModeration[$k] ?? 0 }}</strong>
                    </div>
                @endforeach
            </div>

            <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-cpu text-info"></i> Tình trạng xử lý văn bản
            </h2>
            <div class="vstack gap-2 mb-4">
                @foreach(\App\Models\Document::PROCESSING_LABELS as $k => $v)
                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light">
                        <x-status-badge :label="$v[0]" :tone="$v[1]" />
                        <strong class="text-dark">{{ $byProcessing[$k] ?? 0 }}</strong>
                    </div>
                @endforeach
            </div>

            <h2 class="h6 fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                <i class="bi bi-layers text-secondary"></i> Hàng đợi nền (Queue Worker)
            </h2>
            <div class="p-3 rounded bg-light small">
                <div class="d-flex justify-content-between mb-1">
                    <span>Tác vụ đang chờ:</span>
                    <strong>{{ $queue['pending'] ?? 'n/a' }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Tác vụ thất bại:</span>
                    <strong class="text-danger">{{ $queue['failed'] ?? 'n/a' }}</strong>
                </div>
                @if(($queue['pending'] ?? 0) > 0)
                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small border-0">
                        <i class="bi bi-info-circle me-1"></i>Chạy <code>chay-queue.bat</code> để xử lý các tác vụ chờ.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        {{-- Longest pending documents --}}
        <div class="card border-0 shadow-xs p-4 mb-3">
            <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-hourglass-top text-warning"></i> Chờ duyệt lâu nhất
            </h2>
            <div class="vstack gap-2">
                @forelse($pendingDocs as $d)
                    <div class="p-2 rounded bg-light d-flex justify-content-between align-items-center small">
                        <a href="{{ route('admin.moderation.show', $d) }}" class="fw-semibold text-dark text-truncate me-2 text-decoration-none">
                            {{ $d->title }}
                        </a>
                        <span class="text-muted text-nowrap">{{ $d->owner->name }}</span>
                    </div>
                @empty
                    <span class="text-muted small text-center py-2">Tuyệt vời! Không có tài liệu nào tồn đọng chờ duyệt.</span>
                @endforelse
            </div>
        </div>

        {{-- Recent Audit Logs --}}
        <div class="card border-0 shadow-xs p-4">
            <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-secondary"></i> Hoạt động gần đây
            </h2>
            <div class="vstack gap-2">
                @forelse($recentAudit as $l)
                    <div class="small p-2 rounded bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted">{{ $l->created_at->format('d/m H:i') }}</span>
                            <strong class="text-dark ms-1">{{ $l->user->name ?? 'Hệ thống' }}</strong>:
                            <code class="ms-1">{{ $l->action }}</code>
                        </div>
                    </div>
                @empty
                    <span class="text-muted small text-center py-2">Chưa ghi nhận hoạt động nào.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
