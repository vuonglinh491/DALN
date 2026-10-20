<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class AuditLogger
{
    /** Ghi nhật ký kiểm toán. KHÔNG truyền mật khẩu/API key vào $meta. */
    public static function record(string $action, ?Model $subject = null, array $meta = [], ?User $actor = null): void
    {
        $actor = $actor ?? auth()->user();
        AuditLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subject ? (array_search(get_class($subject), Relation::morphMap(), true) ?: Str::snake(class_basename($subject))) : null, // document | comment | user | category ...
            'subject_id' => $subject?->getKey(),
            'metadata' => $meta ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }
}
