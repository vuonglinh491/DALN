<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
        // Vai trò luôn là "user"; không có tham số nào từ client có thể đổi vai trò
        $role = Role::firstOrCreate(['name' => 'user'], ['label' => 'Người dùng']);
        $user->roles()->attach($role->id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Đăng ký thành công. Chào mừng bạn đến với Smart Document Hub!');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = Str::lower($cred['email']) . '|' . $request->ip();

        // Giới hạn số lần đăng nhập sai
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => trans('auth.throttle', ['seconds' => RateLimiter::availableIn($key)])]);
        }

        $existing = User::where('email', $cred['email'])->first();
        if ($existing && !$existing->is_active && Hash::check($cred['password'], $existing->password)) {
            throw ValidationException::withMessages(['email' => 'Tài khoản của bạn đã bị khóa.' . ($existing->locked_reason ? ' Lý do: ' . $existing->locked_reason : '')]);
        }

        if (!Auth::attempt(['email' => $cred['email'], 'password' => $cred['password'], 'is_active' => 1], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Bạn đã đăng xuất.');
    }
}
