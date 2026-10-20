@extends('layouts.app')
@section('title', 'Đăng ký tài khoản')
@section('content')

<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-md p-4">
            <div class="text-center mb-4">
                <span class="brand-icon mx-auto mb-2" style="width:42px;height:42px;font-size:1.4rem;">
                    <i class="bi bi-person-plus-fill"></i>
                </span>
                <h1 class="h4 fw-bold text-dark mb-1">Tạo tài khoản mới</h1>
                <p class="small text-muted mb-0">Tham gia cộng đồng chia sẻ và tra cứu tài liệu học tập thông minh</p>
            </div>

            <form method="POST" action="{{ route('register') }}" novalidate>@csrf
                <x-input name="name" label="Họ và tên" required autocomplete="name" placeholder="Ví dụ: Nguyễn Văn A" />
                <x-input name="email" label="Địa chỉ Email" type="email" required autocomplete="email" placeholder="name@example.com" />
                <x-input name="password" label="Mật khẩu" type="password" required autocomplete="new-password" help="Ít nhất 8 ký tự, gồm cả chữ và số." placeholder="Tối thiểu 8 ký tự" />
                <x-input name="password_confirmation" label="Nhập lại mật khẩu" type="password" required autocomplete="new-password" placeholder="Nhập lại chính xác mật khẩu" />

                <button class="btn btn-primary w-100 py-2 fw-semibold mt-2" type="submit">
                    <i class="bi bi-person-check me-1"></i>Đăng ký tài khoản
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top small text-muted">
                Đã có tài khoản? <a href="{{ route('login') }}" class="fw-bold text-primary">Đăng nhập</a>
            </div>
        </div>
    </div>
</div>

@endsection
