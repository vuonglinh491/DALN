@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Nhật ký & theo dõi')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-activity text-primary"></i> Nhật ký hệ thống & Theo dõi tác vụ
    </h1>
    <p class="small text-muted mb-0">Giám sát quá trình bóc tách văn bản, lỗi kết nối Gemini API và lịch sử thao tác kiểm toán</p>
</div>

<ul class="nav nav-pills mb-4 gap-1">
    @foreach(['jobs' => 'Tác vụ xử lý tệp', 'ai' => 'Lỗi tích hợp AI', 'audit' => 'Nhật ký kiểm toán'] as $k => $l)
        <li class="nav-item">
            <a class="nav-link py-2 px-3 {{ $tab === $k ? 'active' : 'bg-white border text-dark' }}" href="{{ route('admin.monitoring.index', ['tab' => $k]) }}">
                {{ $l }}
            </a>
        </li>
    @endforeach
</ul>

@if($tab === 'jobs')
    {{-- Status filter buttons for jobs --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach([null => 'Tất cả', 'running' => 'Đang chạy', 'completed' => 'Hoàn thành', 'failed' => 'Lỗi', 'needs_ocr' => 'Cần OCR'] as $k => $l)
            <a class="btn btn-sm {{ ($status ?? null) == $k ? 'btn-primary' : 'btn-light border text-dark' }}" href="{{ route('admin.monitoring.index', array_filter(['tab' => 'jobs', 'status' => $k])) }}">
                {{ $l }}
            </a>
        @endforeach
    </div>

    @if($jobs->isEmpty())
        <x-empty icon="bi-cpu" title="Chưa có tác vụ xử lý nào" />
    @else
        <div class="card border-0 shadow-xs table-responsive overflow-hidden">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Thời điểm</th>
                        <th>Tài liệu</th>
                        <th>Trạng thái</th>
                        <th>Thông tin lỗi</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $j)
                        <tr>
                            <td class="small text-muted text-nowrap">
                                <i class="bi bi-clock me-1"></i>{{ $j->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                <strong class="text-dark">{{ \Illuminate\Support\Str::limit($j->document->title ?? '(Đã xóa)', 40) }}</strong>
                            </td>
                            <td>
                                <x-status-badge :label="$j->status" :tone="in_array($j->status, ['failed','needs_ocr']) ? 'danger' : ($j->status === 'completed' ? 'success' : 'info')"/>
                            </td>
                            <td class="small text-danger">
                                {{ $j->error ?: '—' }}
                            </td>
                            <td class="text-end">
                                @if($j->document && in_array($j->status, ['failed','needs_ocr']) && $j->document->visibility === 'public')
                                    <form method="POST" action="{{ route('admin.moderation.reprocess', $j->document) }}">@csrf
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Xử lý lại
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $jobs->links() }}
        </div>
    @endif
@elseif($tab === 'ai')
    @if($aiErrors->isEmpty())
        <x-empty icon="bi-stars" title="Hệ thống hoạt động ổn định! Chưa ghi nhận lỗi AI nào" />
    @else
        <div class="card border-0 shadow-xs table-responsive overflow-hidden">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Thời điểm</th>
                        <th>Người dùng (ID)</th>
                        <th>Nội dung thông báo lỗi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aiErrors as $e)
                        <tr>
                            <td class="small text-muted text-nowrap">
                                <i class="bi bi-clock me-1"></i>{{ $e->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="small fw-semibold">
                                User #{{ $e->conversation->user_id ?? '—' }}
                            </td>
                            <td class="small">
                                <code class="text-danger p-1 bg-light rounded d-inline-block">{{ $e->error }}</code>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $aiErrors->links() }}
        </div>
    @endif
@else
    {{-- Audit Logs --}}
    <form method="GET" class="card border-0 shadow-xs p-3 mb-4 d-flex flex-row gap-2">
        <input type="hidden" name="tab" value="audit">
        <label class="visually-hidden" for="action">Lọc theo hành động</label>
        <div class="search-input-wrap flex-grow-1">
            <i class="bi bi-search search-icon"></i>
            <input id="action" name="action" value="{{ $action }}" class="form-control form-control-sm" placeholder="Lọc theo hành động (ví dụ: document.approve, user.lock)…">
        </div>
        <button class="btn btn-primary btn-sm px-3">Lọc</button>
    </form>

    @if($logs->isEmpty())
        <x-empty icon="bi-journal-text" title="Chưa có bản ghi nhật ký kiểm toán nào" />
    @else
        <div class="card border-0 shadow-xs table-responsive overflow-hidden">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Thời điểm</th>
                        <th>Người thực hiện</th>
                        <th>Hành động</th>
                        <th>Đối tượng</th>
                        <th>Chi tiết</th>
                        <th>Địa chỉ IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $l)
                        <tr>
                            <td class="small text-muted text-nowrap">
                                {{ $l->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="small fw-semibold text-dark">
                                {{ $l->user->name ?? 'Hệ thống' }}
                            </td>
                            <td>
                                <span class="badge text-bg-light border font-monospace">{{ $l->action }}</span>
                            </td>
                            <td class="small text-muted">
                                {{ $l->subject_type }}@if($l->subject_id) #{{ $l->subject_id }}@endif
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 250px;">
                                {{ $l->metadata ? json_encode($l->metadata, JSON_UNESCAPED_UNICODE) : '—' }}
                            </td>
                            <td class="small text-muted text-nowrap">
                                <code>{{ $l->ip_address }}</code>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $logs->links() }}
        </div>
    @endif
@endif

@endsection
