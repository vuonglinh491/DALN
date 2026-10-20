<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
