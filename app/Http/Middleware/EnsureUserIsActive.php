<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /** Tài khoản bị khóa sẽ bị đăng xuất ngay ở request kế tiếp. */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && !$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Tài khoản của bạn đã bị khóa.' . ($user->locked_reason ? ' Lý do: ' . $user->locked_reason : '')]);
        }

        return $next($request);
    }
}
