<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageSource extends Model
{
    protected $guarded = [];

    protected $casts = [
        'chat_message_id' => 'integer',
        'document_id' => 'integer',
        'document_chunk_id' => 'integer',
        'ref_no' => 'integer',
        'page_start' => 'integer',
        'page_end' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class)->withTrashed();
    }

    public function chunk()
    {
        return $this->belongsTo(DocumentChunk::class, 'document_chunk_id');
    }
}
