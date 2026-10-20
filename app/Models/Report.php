<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public const REASONS = [
        'spam' => 'Spam / quảng cáo',
        'copyright' => 'Vi phạm bản quyền',
        'inappropriate' => 'Nội dung không phù hợp',
        'wrong' => 'Sai thông tin / sai danh mục',
        'other' => 'Lý do khác',
    ];

    protected $guarded = [];

    protected $casts = [
        'reporter_id' => 'integer',
        'reportable_id' => 'integer',
        'handled_by' => 'integer',
        'handled_at' => 'datetime',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function reportable()
    {
        return $this->morphTo(); // null nếu đối tượng đã bị xóa
    }
}
