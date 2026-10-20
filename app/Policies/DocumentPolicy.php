<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /** Xem: công khai đã duyệt; chủ sở hữu; admin (chỉ tài liệu đã gửi công khai, không xem tài liệu riêng tư của người khác). */
    public function view(?User $user, Document $doc): bool
    {
        if ($doc->isPublished()) {
            return true;
        }
        if (!$user || !$user->is_active) {
            return false;
        }
        if ($doc->user_id === $user->id) {
            return true;
        }
        return $user->isAdmin() && $doc->visibility === 'public';
    }

    /** Tải xuống: bắt buộc đăng nhập (user không null) và có quyền xem. */
    public function download(User $user, Document $doc): bool
    {
        return $user->is_active && $this->view($user, $doc);
    }

    /** Chỉnh sửa: chỉ chủ sở hữu, và không khi tài liệu đang bị admin ẩn. */
    public function update(User $user, Document $doc): bool
    {
        return $doc->user_id === $user->id && $doc->moderation_status !== Document::HIDDEN;
    }

    public function delete(User $user, Document $doc): bool
    {
        return $doc->user_id === $user->id || $user->isAdmin();
    }

    /** Dùng AI trên tài liệu này (chưa tính điều kiện xử lý xong / đồng ý — kiểm tra thêm ở RagService). */
    public function useAi(User $user, Document $doc): bool
    {
        return $this->view($user, $doc);
    }
}
