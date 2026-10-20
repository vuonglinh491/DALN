<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Chỉ tạo dữ liệu khởi tạo cần thiết (vai trò + danh mục cơ bản). Không tạo dữ liệu giả, không tạo tài khoản. */
class RolesAndCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin'], ['label' => 'Quản trị viên']);
        Role::firstOrCreate(['name' => 'user'], ['label' => 'Người dùng']);

        foreach (['Công nghệ thông tin', 'Toán học', 'Vật lý', 'Hóa học', 'Sinh học', 'Ngoại ngữ', 'Kinh tế – Quản trị', 'Văn học – Xã hội', 'Khác'] as $i => $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $i * 10, 'is_active' => true]);
        }
    }
}
