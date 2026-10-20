<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // is_active, ai_consent_at... KHÔNG nằm trong fillable: người dùng không thể tự nâng quyền / mở khóa.
    protected $fillable = ['name', 'email', 'password', 'bio'];

    // Mặc định hoạt động (khớp DEFAULT của cột), tránh null trên đối tượng vừa tạo
    protected $attributes = ['is_active' => true];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ai_consent_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function hasRole(string $name): bool
    {
        // Dùng quan hệ đã nạp nếu có để tránh truy vấn lặp
        return $this->roles->contains('name', $name);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
