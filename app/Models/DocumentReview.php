<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentReview extends Model
{
    protected $guarded = [];

    protected $casts = [
        'document_id' => 'integer',
        'admin_id' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
