<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentChunk extends Model
{
    protected $guarded = [];

    protected $casts = [
        'document_id' => 'integer',
        'chunk_index' => 'integer',
        'page_start' => 'integer',
        'page_end' => 'integer',
        'char_count' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
