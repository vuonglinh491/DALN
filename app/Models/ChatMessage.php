<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'conversation_id' => 'integer',
        'grounded' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sources()
    {
        return $this->hasMany(MessageSource::class)->orderBy('ref_no');
    }

    /** HTML an toàn: markdown được render, HTML thô bị loại bỏ, liên kết nguy hiểm bị chặn. */
    public function getContentHtmlAttribute(): string
    {
        if ($this->role === 'user') {
            return nl2br(e($this->content));
        }
        return Str::markdown($this->content, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }
}
