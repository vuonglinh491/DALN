<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);
        $user->fill($data)->save();

        return back()->with('success', 'Đã cập nhật hồ sơ.');
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:current_password'],
        ], ['password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.']);
        $request->user()->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('success', 'Đã đổi mật khẩu.');
    }
}
