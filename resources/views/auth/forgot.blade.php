@extends('layouts.app')
@section('title', 'Quên mật khẩu')
@section('content')

<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-md p-4">
            <div class="text-center mb-4">
                <span class="brand-icon mx-auto mb-2" style="width:42px;height:42px;font-size:1.4rem;">
                    <i class="bi bi-key-fill"></i>
                </span>
                <h1 class="h4 fw-bold text-dark mb-1">Khôi phục mật khẩu</h1>
                <p class="small text-muted mb-0">Nhập email đăng ký của bạn để nhận liên kết đặt lại mật khẩu</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}" novalidate>@csrf
                <x-input name="email" label="Địa chỉ Email" type="email" required placeholder="name@example.com" />
                <button class="btn btn-primary w-100 py-2 fw-semibold" type="submit">
                    <i class="bi bi-envelope me-1"></i>Gửi liên kết đặt lại mật khẩu
                </button>
            </form>

            <div class="alert alert-light border mt-4 mb-0 small text-muted">
                <i class="bi bi-info-circle me-1"></i>Khi chạy cục bộ (local) với <code>MAIL_MAILER=log</code>, liên kết đặt lại mật khẩu được ghi trực tiếp vào file <code>storage/logs/laravel.log</code>.
            </div>

            <div class="text-center mt-3 small">
                <a href="{{ route('login') }}" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Quay lại trang Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

@endsection
