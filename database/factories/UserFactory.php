<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // "password" (chỉ dùng cho test)
            'remember_token' => Str::random(10),
        ];
    }

    /** is_active không nằm trong fillable nên đặt qua afterMaking/afterCreating. */
    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'user'], ['label' => 'Người dùng']);
            if (!$user->roles()->exists()) {
                $user->roles()->attach($role->id);
            }
        });
    }

    public function admin()
    {
        return $this->afterCreating(function (User $user) {
            $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Quản trị viên']);
            $user->roles()->sync([$role->id]);
        });
    }

    public function locked()
    {
        return $this->afterCreating(fn (User $u) => $u->forceFill(['is_active' => false, 'locked_reason' => 'test'])->save());
    }
}
