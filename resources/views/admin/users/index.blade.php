@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Quản lý người dùng')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-people text-primary"></i> Quản lý người dùng
    </h1>
    <p class="small text-muted mb-0">Quản lý tài khoản, phân quyền quản trị và kiểm soát khóa tài khoản vi phạm</p>
</div>

{{-- Search & Filter --}}
<form method="GET" class="card border-0 shadow-xs p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label small fw-semibold" for="q">Tìm kiếm</label>
            <div class="search-input-wrap">
                <i class="bi bi-search search-icon"></i>
                <input id="q" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Tìm theo tên hoặc địa chỉ email…">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold" for="status">Trạng thái tài khoản</label>
            <select id="status" name="status" class="form-select form-select-sm">
                <option value="">Tất cả</option>
                <option value="locked" @selected(request('status') === 'locked')>Đang bị khóa</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-sm w-100">
                <i class="bi bi-funnel me-1"></i>Lọc danh sách
            </button>
        </div>
    </div>
</form>

<div class="card border-0 shadow-xs table-responsive overflow-hidden">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Người dùng</th>
                <th>Vai trò</th>
                <th>Tài liệu</th>
                <th>Trạng thái</th>
                <th class="text-end">Hành động quản lý</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar-pill" style="width:32px;height:32px;font-size:0.85rem;">
                                {{ mb_substr($u->name, 0, 1) }}
                            </span>
                            <div>
                                <strong class="text-dark">{{ $u->name }}</strong>
                                <div class="small text-muted">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($u->isAdmin())
                            <span class="badge badge-soft badge-primary"><i class="bi bi-shield-check me-1"></i>Quản trị viên</span>
                        @else
                            <span class="badge badge-soft badge-secondary"><i class="bi bi-person me-1"></i>Người dùng</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge text-bg-light border">{{ $u->documents_count }}</span>
                    </td>
                    <td>
                        @if($u->is_active)
                            <x-status-badge label="Hoạt động" tone="success"/>
                        @else
                            <x-status-badge label="Đang bị khóa" tone="danger"/>
                            @if($u->locked_reason)
                                <div class="small text-muted text-truncate" style="max-width: 150px;">{{ $u->locked_reason }}</div>
                            @endif
                        @endif
                    </td>
                    <td class="text-end">
                        @if($u->id !== auth()->id())
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                @if($u->is_active)
                                    <form method="POST" action="{{ route('admin.users.lock', $u) }}" class="d-flex gap-1" data-confirm="Bạn chắc chắn muốn khóa tài khoản {{ $u->email }}?">@csrf
                                        <label class="visually-hidden" for="r{{ $u->id }}">Lý do khóa</label>
                                        <input id="r{{ $u->id }}" name="reason" class="form-control form-control-sm" placeholder="Lý do khóa" required maxlength="255" style="width: 140px;">
                                        <button class="btn btn-sm btn-outline-danger" title="Khóa">Khóa</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.unlock', $u) }}">@csrf
                                        <button class="btn btn-sm btn-outline-success">Mở khóa</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.users.role', $u) }}" class="d-flex gap-1" data-confirm="Đổi quyền vai trò của {{ $u->email }}?">@csrf
                                    <label class="visually-hidden" for="ro{{ $u->id }}">Vai trò</label>
                                    <select id="ro{{ $u->id }}" name="role" class="form-select form-select-sm" style="width: 120px;">
                                        <option value="user" @selected(!$u->isAdmin())>Người dùng</option>
                                        <option value="admin" @selected($u->isAdmin())>Quản trị</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Lưu</button>
                                </form>
                            </div>
                        @else
                            <span class="badge text-bg-light border text-muted">Tài khoản của bạn</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $users->links() }}
</div>

@endsection
