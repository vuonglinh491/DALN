@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')

<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-md p-4">
            <div class="text-center mb-4">
                <span class="brand-icon mx-auto mb-2" style="width:42px;height:42px;font-size:1.4rem;">
                    <i class="bi bi-journal-richtext"></i>
                </span>
                <h1 class="h4 fw-bold text-dark mb-1">Đăng nhập tài khoản</h1>
                <p class="small text-muted mb-0">Truy cập thư viện và đồng hành cùng trợ lý học tập AI</p>
            </div>

            <form method="POST" action="{{ route('login') }}" novalidate>@csrf
                <x-input name="email" label="Địa chỉ Email" type="email" required autocomplete="email" placeholder="name@example.com" />
                <x-input name="password" label="Mật khẩu" type="password" required autocomplete="current-password" placeholder="Nhập mật khẩu của bạn" />

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label small text-muted" for="remember">Ghi nhớ đăng nhập</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none">Quên mật khẩu?</a>
                </div>

                <button class="btn btn-primary w-100 py-2 fw-semibold" type="submit">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top small text-muted">
                Chưa có tài khoản học tập? <a href="{{ route('register') }}" class="fw-bold text-primary">Đăng ký ngay</a>
            </div>
        </div>
    </div>
</div>

@endsection
