<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        abort_unless($user && $user->is_active && $user->hasRole($role), 403, 'Bạn không có quyền truy cập khu vực này.');

        return $next($request);
    }
}
