<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    // Trạng thái kiểm duyệt
    public const DRAFT = 'draft';                 // riêng tư, không cần duyệt
    public const PENDING = 'pending';             // chờ duyệt
    public const APPROVED = 'approved';           // đã duyệt, công khai (nếu visibility=public)
    public const REJECTED = 'rejected';
    public const CHANGES = 'changes_requested';
    public const HIDDEN = 'hidden';               // admin ẩn do vi phạm

    // Trạng thái xử lý tệp (tách biệt với kiểm duyệt)
    public const P_PENDING = 'pending';
    public const P_PROCESSING = 'processing';
    public const P_COMPLETED = 'completed';
    public const P_FAILED = 'failed';
    public const P_NEEDS_OCR = 'needs_ocr';

    public const MODERATION_LABELS = [
        'draft' => ['Riêng tư', 'secondary'],
        'pending' => ['Chờ duyệt', 'pending'],
        'approved' => ['Đã duyệt', 'success'],
        'rejected' => ['Bị từ chối', 'danger'],
        'changes_requested' => ['Cần chỉnh sửa', 'pending'],
        'hidden' => ['Bị ẩn', 'danger'],
    ];

    public const PROCESSING_LABELS = [
        'pending' => ['Đang chờ xử lý', 'pending'],
        'processing' => ['Đang xử lý', 'info'],
        'completed' => ['Đã lập chỉ mục', 'success'],
        'failed' => ['Xử lý lỗi', 'danger'],
        'needs_ocr' => ['Cần OCR (bản quét)', 'danger'],
    ];

    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'category_id' => 'integer',
        'reviewed_by' => 'integer',
        'page_count' => 'integer',
        'size_bytes' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'current_version' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function reviews()
    {
        return $this->hasMany(DocumentReview::class)->latest('id');
    }

    public function chunks()
    {
        return $this->hasMany(DocumentChunk::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function downloads()
    {
        return $this->hasMany(Download::class);
    }

    public function processingJobs()
    {
        return $this->hasMany(ProcessingJob::class);
    }

    /** Công khai: đã duyệt và chủ sở hữu chọn chia sẻ công khai. */
    public function scopePublished($q)
    {
        return $q->where('moderation_status', self::APPROVED)->where('visibility', 'public');
    }

    /** Đã trích xuất và lập chỉ mục thành công. */
    public function scopeIndexed($q)
    {
        return $q->where('processing_status', self::P_COMPLETED);
    }

    /**
     * Tài liệu AI được phép dùng cho người dùng $user:
     * - đã lập chỉ mục, VÀ
     * - đã công khai (đã duyệt), HOẶC là tài liệu của chính họ khi họ đã đồng ý cho AI xử lý.
     */
    public function scopeAiEligibleFor($q, User $user)
    {
        return $q->indexed()->where(function ($w) use ($user) {
            $w->where(function ($p) {
                $p->where('moderation_status', self::APPROVED)->where('visibility', 'public');
            });
            if ($user->ai_consent_at !== null) {
                $w->orWhere('user_id', $user->id);
            }
        });
    }

    public function isPublished(): bool
    {
        return $this->moderation_status === self::APPROVED && $this->visibility === 'public';
    }

    public function getModerationLabelAttribute(): array
    {
        return self::MODERATION_LABELS[$this->moderation_status] ?? [$this->moderation_status, 'secondary'];
    }

    public function getProcessingLabelAttribute(): array
    {
        return self::PROCESSING_LABELS[$this->processing_status] ?? [$this->processing_status, 'secondary'];
    }

    public function getHumanSizeAttribute(): string
    {
        $b = (int) $this->size_bytes;
        if ($b >= 1048576) {
            return number_format($b / 1048576, 1) . ' MB';
        }
        return number_format(max($b, 0) / 1024, 0) . ' KB';
    }
}
