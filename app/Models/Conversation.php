<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    public const SCOPES = [
        'document' => 'Một tài liệu cụ thể',
        'mine' => 'Tài liệu của tôi',
        'public' => 'Thư viện công khai',
        'all' => 'Tất cả tài liệu tôi được phép dùng',
    ];

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

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }
}
