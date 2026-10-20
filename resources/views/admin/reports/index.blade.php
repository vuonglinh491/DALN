@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Bình luận & báo cáo')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-flag text-primary"></i> Quản lý Bình luận & Báo cáo
    </h1>
    <p class="small text-muted mb-0">Xử lý các báo cáo vi phạm bản quyền, nội dung không phù hợp và quản lý bình luận</p>
</div>

<ul class="nav nav-pills mb-4 gap-1">
    <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ $tab === 'reports' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('admin.reports.index', ['tab' => 'reports']) }}">
            <i class="bi bi-shield-exclamation me-1"></i>Báo cáo vi phạm
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ $tab === 'comments' ? 'active' : 'bg-white border text-dark' }}" href="{{ route('admin.reports.index', ['tab' => 'comments']) }}">
            <i class="bi bi-chat-left-text me-1"></i>Tất cả bình luận
        </a>
    </li>
</ul>

@if($tab === 'comments')
    @if($comments->isEmpty())
        <x-empty icon="bi-chat" title="Chưa có bình luận nào trên hệ thống" />
    @else
        <div class="card border-0 shadow-xs table-responsive overflow-hidden">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nội dung bình luận</th>
                        <th>Tài liệu liên quan</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comments as $c)
                        <tr>
                            <td>
                                <div class="small text-muted mb-1">
                                    <strong class="text-dark">{{ $c->user->name ?? '—' }}</strong> · {{ $c->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-dark small">{{ \Illuminate\Support\Str::limit($c->body, 220) }}</div>
                            </td>
                            <td class="small">
                                @if($c->document)
                                    <a href="{{ route('library.show', $c->document) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ \Illuminate\Support\Str::limit($c->document->title, 40) }}
                                    </a>
                                @else
                                    <span class="text-muted">(Đã xóa)</span>
                                @endif
                            </td>
                            <td>
                                @if($c->status === 'visible')
                                    <x-status-badge label="Đang hiển thị" tone="success"/>
                                @else
                                    <x-status-badge label="Đã ẩn" tone="danger"/>
                                @endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.comments.toggle', $c) }}">@csrf
                                    <button class="btn btn-sm {{ $c->status === 'visible' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $c->status === 'visible' ? 'Ẩn bình luận' : 'Hiện lại' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $comments->links() }}
        </div>
    @endif
@else
    {{-- Status filter buttons for reports --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach(['open' => 'Chưa xử lý', 'resolved' => 'Đã xử lý', 'dismissed' => 'Bác bỏ', 'all' => 'Tất cả'] as $k => $l)
            <a class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-light border text-dark' }}" href="{{ route('admin.reports.index', ['tab' => 'reports', 'status' => $k]) }}">
                {{ $l }}
            </a>
        @endforeach
    </div>

    @if($reports->isEmpty())
        <x-empty icon="bi-flag" title="Không có báo cáo nào trong mục này" />
    @else
        <div class="vstack gap-3">
            @foreach($reports as $r)
                <div class="card border-0 shadow-xs p-3">
                    @php $t = $r->reportable; @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft badge-pending">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ \App\Models\Report::REASONS[$r->reason] ?? $r->reason }}
                            </span>
                            <span class="small text-muted">{{ $r->reporter->name ?? 'Người dùng' }} · {{ $r->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <x-status-badge :label="['open'=>'Chưa xử lý','resolved'=>'Đã xử lý','dismissed'=>'Bác bỏ'][$r->status]" :tone="$r->status === 'open' ? 'pending' : 'secondary'"/>
                    </div>

                    <div class="p-2 rounded bg-light small mb-2">
                        <strong>Đối tượng bị báo cáo ({{ $r->reportable_type === 'document' ? 'Tài liệu' : 'Bình luận' }}):</strong>
                        @if(!$t)
                            <span class="text-danger ms-1">(Đối tượng đã bị xóa khỏi hệ thống)</span>
                        @elseif($t instanceof \App\Models\Document)
                            <a href="{{ route('admin.moderation.show', $t) }}" class="fw-semibold text-dark ms-1">{{ $t->title }}</a>
                            <span class="text-muted ms-1">({{ $t->moderation_label[0] }})</span>
                        @else
                            <div class="mt-1 ps-2 border-start border-2 text-muted fst-italic">“{{ \Illuminate\Support\Str::limit($t->body, 200) }}”</div>
                        @endif
                    </div>

                    @if($r->details)
                        <div class="small text-secondary mb-3">
                            <strong>Chi tiết từ người báo cáo:</strong> {{ $r->details }}
                        </div>
                    @endif

                    @if($r->status === 'open')
                        <form method="POST" action="{{ route('admin.reports.handle', $r) }}" class="row g-2 pt-2 border-top">@csrf
                            <div class="col-md-6">
                                <label class="visually-hidden" for="nt{{ $r->id }}">Ghi chú xử lý</label>
                                <input id="nt{{ $r->id }}" name="note" class="form-control form-control-sm" placeholder="Ghi chú xử lý hoặc lý do ẩn vi phạm…" maxlength="1000">
                            </div>
                            <div class="col-md-6 d-flex flex-wrap gap-1 justify-content-md-end">
                                <button name="action" value="resolve_hide" class="btn btn-sm btn-danger" @disabled(!$t)>
                                    <i class="bi bi-eye-slash me-1"></i>Ẩn nội dung & đóng
                                </button>
                                <button name="action" value="resolve" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-check2 me-1"></i>Đã xử lý
                                </button>
                                <button name="action" value="dismiss" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x me-1"></i>Bác bỏ
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="small text-muted pt-2 border-top">
                            <i class="bi bi-check2-circle text-success me-1"></i>Đã xử lý bởi <strong>{{ $r->handler->name ?? 'Quản trị viên' }}</strong> lúc {{ $r->handled_at?->format('d/m/Y H:i') }}
                            @if($r->resolution_note)
                                — <em>{{ $r->resolution_note }}</em>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-4 d-flex justify-content-center">
            {{ $reports->links() }}
        </div>
    @endif
@endif

@endsection
