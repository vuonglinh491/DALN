<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'daln:create-admin {--name= : Họ tên} {--email= : Email đăng nhập}';
    protected $description = 'Tạo (hoặc nâng quyền) tài khoản quản trị viên. Mật khẩu được nhập ẩn, không lưu trong mã nguồn.';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Họ tên quản trị viên');
        $email = $this->option('email') ?: $this->ask('Email');
        $existing = User::where('email', $email)->first();

        $password = null;
        if (!$existing) {
            $password = $this->secret('Mật khẩu (tối thiểu 8 ký tự, có chữ và số)');
            if ($password !== $this->secret('Nhập lại mật khẩu')) {
                $this->error('Mật khẩu nhập lại không khớp.');
                return self::FAILURE;
            }
        }

        $v = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            ['name' => ['required', 'max:100'], 'email' => ['required', 'email'], 'password' => $existing ? ['nullable'] : ['required', Password::min(8)->letters()->numbers()]]
        );
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }
            return self::FAILURE;
        }

        $user = $existing ?: User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        $user->forceFill(['is_active' => true, 'email_verified_at' => $user->email_verified_at ?? now()])->save();
        $admin = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Quản trị viên']);
        $user->roles()->syncWithoutDetaching([$admin->id]);

        $this->info(($existing ? 'Đã cấp quyền admin cho ' : 'Đã tạo admin ') . $email);

        return self::SUCCESS;
    }
}
