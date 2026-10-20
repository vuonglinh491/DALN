<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $guarded = [];

    protected $casts = [
        'document_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
