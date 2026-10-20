@extends('layouts.panel')
@section('title', 'Hồ sơ cá nhân')
@section('panel')

{{-- Profile Information --}}
<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <span class="user-avatar-pill" style="width:54px;height:54px;font-size:1.5rem;">
            {{ mb_substr($user->name, 0, 1) }}
        </span>
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">{{ $user->name }}</h1>
            <div class="small text-muted">{{ $user->email }} · Vai trò: <strong>{{ $user->roles->pluck('label')->implode(', ') ?: 'Người dùng' }}</strong></div>
        </div>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" novalidate>@csrf @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <x-input name="name" label="Họ và tên" :value="$user->name" required />
            </div>
            <div class="col-md-6">
                <x-input name="email" label="Địa chỉ Email" type="email" :value="$user->email" required />
            </div>
        </div>
        <x-input name="bio" label="Giới thiệu bản thân" type="textarea" :rows="3" :value="$user->bio" placeholder="Chia sẻ đôi nét về bản thân, chuyên ngành học tập hoặc sở thích…" />
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-check2-circle me-1"></i>Lưu thông tin hồ sơ
        </button>
    </form>
</div>

{{-- Change Password --}}
<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="mb-3 pb-2 border-bottom">
        <h2 class="h5 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock text-primary"></i> Đổi mật khẩu
        </h2>
        <p class="small text-muted mb-0">Cập nhật mật khẩu định kỳ để bảo vệ tài khoản của bạn</p>
    </div>

    <form method="POST" action="{{ route('profile.password') }}" novalidate>@csrf @method('PUT')
        <div class="row">
            <div class="col-md-4">
                <x-input name="current_password" label="Mật khẩu hiện tại" type="password" required autocomplete="current-password" />
            </div>
            <div class="col-md-4">
                <x-input name="password" label="Mật khẩu mới" type="password" required autocomplete="new-password" help="Ít nhất 8 ký tự, gồm cả chữ và số." />
            </div>
            <div class="col-md-4">
                <x-input name="password_confirmation" label="Xác nhận mật khẩu mới" type="password" required autocomplete="new-password" />
            </div>
        </div>
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-key me-1"></i>Cập nhật mật khẩu
        </button>
    </form>
</div>

{{-- AI Consent --}}
<div class="card border-0 shadow-sm p-4">
    <div class="mb-3 pb-2 border-bottom">
        <h2 class="h5 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-stars text-primary"></i> Trợ lý AI & Quyền riêng tư tài liệu
        </h2>
        <p class="small text-muted mb-0">Mặc định AI chỉ dùng tài liệu công khai đã duyệt trên hệ thống để trả lời.</p>
    </div>

    <p class="small text-muted mb-3">
        Nếu bạn cấp quyền đồng ý, khi bạn hỏi đáp với các tài liệu <strong>riêng tư hoặc chưa công khai của chính bạn</strong>, các trích đoạn văn bản liên quan sẽ được gửi tới dịch vụ AI Gemini để tạo câu trả lời và dẫn nguồn số trang.
    </p>

    @if($user->ai_consent_at)
        <div class="alert alert-success border-0 shadow-xs mb-3 d-flex align-items-center justify-content-between">
            <div>
                <i class="bi bi-check-circle-fill me-1"></i>
                <span>Bạn đã đồng ý cấp quyền xử lý AI lúc <strong>{{ $user->ai_consent_at->format('d/m/Y H:i') }}</strong>.</span>
            </div>
            <form method="POST" action="{{ route('ai.consent.revoke') }}" data-confirm="Thu hồi sự đồng ý? AI sẽ không thể truy cập tài liệu riêng tư của bạn nữa.">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Thu hồi quyền</button>
            </form>
        </div>
    @else
        <form method="POST" action="{{ route('ai.consent') }}">@csrf
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="agree" id="agree" value="1" required>
                <label class="form-check-label small" for="agree">
                    Tôi đồng ý cho phép Trợ lý AI xử lý nội dung tài liệu riêng tư của chính mình khi đặt câu hỏi.
                </label>
            </div>
            <button class="btn btn-outline-primary btn-sm">
                <i class="bi bi-shield-check me-1"></i>Xác nhận đồng ý
            </button>
        </form>
    @endif
</div>

@endsection
