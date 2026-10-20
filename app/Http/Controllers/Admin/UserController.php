<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $users = User::with('roles')->withCount('documents')
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($request->query('role') === 'admin', fn ($w) => $w->whereHas('roles', fn ($r) => $r->where('name', 'admin')))
            ->when($request->query('status') === 'locked', fn ($w) => $w->where('is_active', false))
            ->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function lock(Request $request, User $user)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của mình.');
        }
        $user->forceFill(['is_active' => false, 'locked_reason' => $data['reason']])->save();
        AuditLogger::record('user.lock', $user, ['reason' => $data['reason'], 'email' => $user->email]);

        return back()->with('success', 'Đã khóa tài khoản ' . $user->email . '.');
    }

    public function unlock(User $user)
    {
        $user->forceFill(['is_active' => true, 'locked_reason' => null])->save();
        AuditLogger::record('user.unlock', $user, ['email' => $user->email]);

        return back()->with('success', 'Đã mở khóa tài khoản ' . $user->email . '.');
    }

    public function role(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'in:admin,user']]);
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự thay đổi vai trò của mình.');
        }
        $labels = ['admin' => 'Quản trị viên', 'user' => 'Người dùng'];
        $role = Role::firstOrCreate(['name' => $data['role']], ['label' => $labels[$data['role']]]);
        $user->roles()->sync([$role->id]);
        AuditLogger::record('user.role', $user, ['email' => $user->email, 'role' => $data['role']]);

        return back()->with('success', 'Đã đổi vai trò của ' . $user->email . '.');
    }
}
