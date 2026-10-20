<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }
}
