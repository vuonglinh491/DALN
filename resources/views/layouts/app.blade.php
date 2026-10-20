<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Trang chủ') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<a class="visually-hidden-focusable skip-link" href="#main">Bỏ qua điều hướng</a>

<nav class="navbar navbar-expand-lg app-nav sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <span class="brand-icon"><i class="bi bi-journal-richtext"></i></span>
            <span>Smart Document Hub</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('library.*') ? 'active' : '' }}" href="{{ route('library.index') }}">
                        <i class="bi bi-collection me-1"></i>Thư viện
                    </a>
                </li>
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('ai.*') ? 'active' : '' }}" href="{{ route('ai.index') }}">
                            <i class="bi bi-stars text-primary me-1"></i>Trợ lý AI
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('documents.create') ? 'active' : '' }}" href="{{ route('documents.create') }}">
                            <i class="bi bi-cloud-arrow-up me-1"></i>Tải lên
                        </a>
                    </li>
                @endauth
            </ul>
            <form class="search-form position-relative me-lg-3 mb-2 mb-lg-0" action="{{ route('search') }}" method="GET" role="search">
                <label for="navSearch" class="visually-hidden">Tìm tài liệu</label>
                <div class="search-input-wrap">
                    <i class="bi bi-search search-icon"></i>
                    <input id="navSearch" name="q" type="search" class="form-control" placeholder="Tìm tài liệu, giáo trình, đề thi…" autocomplete="off"
                           value="{{ request()->routeIs('search') ? request('q') : '' }}" data-suggest-url="{{ route('search.suggest') }}" maxlength="100">
                </div>
                <ul class="list-group suggest-box d-none" id="suggestBox" role="listbox" aria-label="Gợi ý"></ul>
            </form>
            <ul class="navbar-nav align-items-lg-center">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm ms-lg-2" href="{{ route('register') }}"><i class="bi bi-person-plus me-1"></i>Đăng ký</a></li>
                @else
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    <li class="nav-item me-1">
                        <a class="nav-link position-relative py-2 px-2" href="{{ route('notifications.index') }}" aria-label="Thông báo" title="Thông báo">
                            <i class="bi bi-bell fs-5"></i>
                            @if($unread)
                                <span class="badge rounded-pill bg-danger notif-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="user-avatar-pill">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                            <span class="fw-semibold">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 18) }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-md border-0 py-2">
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-semibold text-dark text-truncate">{{ auth()->user()->name }}</div>
                                <div class="small text-muted text-truncate">{{ auth()->user()->email }}</div>
                            </li>
                            <li><a class="dropdown-item py-2" href="{{ route('documents.mine') }}"><i class="bi bi-folder2-open text-primary me-2"></i>Tài liệu của tôi</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('bookmarks.index') }}"><i class="bi bi-heart text-danger me-2"></i>Yêu thích</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('downloads.index') }}"><i class="bi bi-download text-success me-2"></i>Lịch sử tải xuống</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('ai.history') }}"><i class="bi bi-chat-left-text text-info me-2"></i>Lịch sử trò chuyện</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}"><i class="bi bi-person-gear text-secondary me-2"></i>Hồ sơ cá nhân</a></li>
                            @can('admin')
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item py-2 text-primary fw-semibold" href="{{ route('admin.dashboard') }}"><i class="bi bi-shield-check me-2"></i>Quản trị hệ thống</a></li>
                            @endcan
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item py-2 text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<main id="main" class="container py-4 page-enter">
    @php
        $alertMeta = [
            'success' => ['cls' => 'success', 'icon' => 'bi-check-circle-fill'],
            'error'   => ['cls' => 'danger',  'icon' => 'bi-exclamation-triangle-fill'],
            'status'  => ['cls' => 'info',    'icon' => 'bi-info-circle-fill'],
        ];
    @endphp
    @foreach($alertMeta as $key => $meta)
        @if(session($key))
            <div class="alert alert-{{ $meta['cls'] }} alert-dismissible fade show d-flex align-items-center gap-2 border-0 shadow-sm" role="alert">
                <i class="bi {{ $meta['icon'] }} fs-5 flex-shrink-0"></i>
                <div class="flex-grow-1">{{ session($key) }}</div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        @endif
    @endforeach
    @yield('content')
</main>

<footer class="app-footer py-4 mt-5">
    <div class="container">
        <div class="row g-4 align-items-center justify-content-between mb-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="brand-icon" style="width:26px;height:26px;font-size:0.9rem;"><i class="bi bi-journal-richtext"></i></span>
                    <strong class="text-dark">Smart Document Hub</strong>
                </div>
                <p class="small text-muted mb-0">Hệ thống chia sẻ, tra cứu và hỏi đáp tài liệu thông minh kết hợp trợ lý AI Gemini trích xuất nguồn và số trang chuẩn xác.</p>
            </div>
            <div class="col-md-5 text-md-end small">
                <div class="d-flex flex-wrap gap-3 justify-content-md-end mb-2">
                    <a href="{{ route('library.index') }}" class="text-muted text-decoration-none">Thư viện</a>
                    <a href="{{ route('search') }}" class="text-muted text-decoration-none">Tìm kiếm</a>
                    @auth
                        <a href="{{ route('ai.index') }}" class="text-muted text-decoration-none">Trợ lý AI</a>
                        <a href="{{ route('documents.create') }}" class="text-muted text-decoration-none">Tải lên</a>
                    @endauth
                </div>
                <div class="text-muted fst-italic">Câu trả lời AI chỉ mang tính tham khảo; hãy đối chiếu với tài liệu gốc.</div>
            </div>
        </div>
        <div class="border-top pt-3 text-center small text-muted">
            © {{ date('Y') }} Smart Document Hub. Nền tảng học tập số thông minh.
        </div>
    </div>
</footer>

{{-- Hộp thoại xác nhận dùng chung cho thao tác nguy hiểm --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fs-5 d-flex align-items-center gap-2" id="confirmTitle">
                    <i class="bi bi-exclamation-diamond text-danger"></i> Xác nhận
                </h2>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body py-3 text-muted" id="confirmBody"></div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" class="btn btn-danger" id="confirmOk">Đồng ý thực hiện</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
