@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')
@section('content')

<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-md p-4">
            <div class="text-center mb-4">
                <span class="brand-icon mx-auto mb-2" style="width:42px;height:42px;font-size:1.4rem;">
                    <i class="bi bi-shield-lock-fill"></i>
                </span>
                <h1 class="h4 fw-bold text-dark mb-1">Thiết lập mật khẩu mới</h1>
                <p class="small text-muted mb-0">Tạo mật khẩu an toàn mới cho tài khoản của bạn</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" novalidate>@csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-input name="email" label="Địa chỉ Email" type="email" :value="$email" required />
                <x-input name="password" label="Mật khẩu mới" type="password" required autocomplete="new-password" help="Ít nhất 8 ký tự, gồm cả chữ và số." placeholder="Tối thiểu 8 ký tự" />
                <x-input name="password_confirmation" label="Xác nhận mật khẩu mới" type="password" required autocomplete="new-password" placeholder="Nhập lại mật khẩu mới" />
                <button class="btn btn-primary w-100 py-2 fw-semibold mt-2" type="submit">
                    <i class="bi bi-check2-circle me-1"></i>Đặt lại mật khẩu
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
