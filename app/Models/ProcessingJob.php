<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessingJob extends Model
{
    protected $guarded = [];

    public function document()
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }

    protected $casts = ['started_at' => 'datetime', 'finished_at' => 'datetime'];
}
