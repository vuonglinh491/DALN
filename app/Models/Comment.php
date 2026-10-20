<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'document_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }

    public function scopeVisible($q)
    {
        return $q->where('status', 'visible');
    }
}
