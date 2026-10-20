@extends('layouts.app')
@section('title', $document->title)
@section('content')

<div class="row g-4">
<div class="col-lg-8">
    {{-- Document Header --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
            <span class="badge badge-soft badge-primary fs-7">
                <i class="bi bi-folder2 me-1"></i>{{ $document->category->name ?? 'Tài liệu' }}
            </span>
            @php
                $ext = strtolower($document->extension ?? '');
                $extClass = match($ext) {
                    'pdf' => 'ext-pdf',
                    'docx' => 'ext-docx',
                    default => 'ext-txt',
                };
                $extIcon = match($ext) {
                    'pdf' => 'bi-file-earmark-pdf-fill',
                    'docx' => 'bi-file-earmark-word-fill',
                    default => 'bi-file-earmark-text-fill',
                };
            @endphp
            <span class="file-ext-badge {{ $extClass }}">
                <i class="bi {{ $extIcon }}"></i> {{ strtoupper($document->extension) }}
            </span>
            <x-status-badge :label="$document->moderation_label[0]" :tone="$document->moderation_label[1]" />
            @if(auth()->check() && auth()->id() === $document->user_id)
                <x-status-badge :label="$document->processing_label[0]" :tone="$document->processing_label[1]" />
            @endif
        </div>

        <h1 class="h3 fw-bold text-dark mb-2">{{ $document->title }}</h1>

        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small py-2 mb-3 border-bottom border-top">
            <span><i class="bi bi-person text-secondary me-1"></i>{{ $document->owner->name }}</span>
            <span><i class="bi bi-calendar3 text-secondary me-1"></i>{{ $document->updated_at->format('d/m/Y') }}</span>
            <span><i class="bi bi-eye text-secondary me-1"></i>{{ number_format($document->view_count) }} lượt xem</span>
            <span><i class="bi bi-download text-secondary me-1"></i>{{ number_format($document->download_count) }} lượt tải</span>
            <span><i class="bi bi-file-earmark-binary text-secondary me-1"></i>{{ $document->human_size }}</span>
            @if($document->page_count)
                <span><i class="bi bi-book text-secondary me-1"></i>{{ $document->page_count }} trang</span>
            @endif
        </div>

        @if($document->subject)
            <div class="mb-2 text-muted small">
                <strong class="text-dark"><i class="bi bi-mortarboard me-1"></i>Môn học / Lĩnh vực:</strong> {{ $document->subject }}
            </div>
        @endif

        @if($document->description)
            <div class="text-secondary mb-3">{{ $document->description }}</div>
        @endif

        @if($document->tags->isNotEmpty())
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach($document->tags as $t)
                    <a href="{{ route('library.index', ['tag' => $t->slug]) }}" class="badge badge-soft badge-secondary text-decoration-none">
                        #{{ $t->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if(auth()->check() && auth()->id() === $document->user_id && in_array($document->moderation_status, ['rejected','changes_requested','hidden']) && $document->review_note)
            <div class="alert alert-warning border-0 shadow-xs mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Ghi chú kiểm duyệt:</strong> {{ $document->review_note }}
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="d-flex flex-wrap gap-2 pt-2">
            @auth
                <a class="btn btn-primary" href="{{ route('library.download', $document) }}">
                    <i class="bi bi-download"></i> Tải xuống ({{ strtoupper($document->extension) }})
                </a>
                @if(in_array($document->extension, ['pdf','txt']))
                    <a class="btn btn-outline-primary" target="_blank" rel="noopener" href="{{ route('library.file', $document) }}">
                        <i class="bi bi-box-arrow-up-right"></i> Đọc tệp gốc
                    </a>
                @endif
                @if($document->processing_status === 'completed')
                    <form method="POST" action="{{ route('ai.start') }}" data-no-lock="1" class="d-inline">@csrf
                        <input type="hidden" name="scope" value="document">
                        <input type="hidden" name="document_id" value="{{ $document->id }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-stars text-primary"></i> Hỏi AI về tài liệu
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('bookmarks.toggle', $document) }}" class="d-inline">@csrf
                    <button class="btn btn-light border text-secondary" type="submit">
                        <i class="bi {{ $bookmarked ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
                        <span>{{ $bookmarked ? 'Đã yêu thích' : 'Yêu thích' }}</span>
                    </button>
                </form>
            @else
                <a class="btn btn-primary" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập để tải xuống & hỏi AI
                </a>
            @endauth
        </div>
    </div>

    {{-- Preview Section --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 fw-bold mb-0 text-dark">
                <i class="bi bi-file-text text-primary me-2"></i>Bản xem trước nội dung
            </h2>
            @if($preview)
                <button type="button" id="copyPreviewBtn" class="btn btn-light btn-sm border text-muted">
                    <i class="bi bi-clipboard"></i> Sao chép
                </button>
            @endif
        </div>
        @if($preview)
            <div class="preview-box">{{ $preview }}</div>
            <p class="small text-muted mt-2 mb-0">
                <i class="bi bi-info-circle me-1"></i>Đây là trích đoạn nội dung văn bản được lập chỉ mục bởi hệ thống. Vui lòng tải xuống để xem định dạng đầy đủ.
            </p>
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-file-earmark-x fs-1 text-secondary opacity-50 mb-2"></i>
                <p class="mb-0">Chưa có bản xem trước (tài liệu đang chờ xử lý hoặc là tệp quét ảnh chưa có OCR).</p>
            </div>
        @endif
    </div>

    {{-- Comments Section --}}
    <div class="card border-0 shadow-sm p-4" id="binh-luan">
        <h2 class="h5 fw-bold mb-3 text-dark">
            <i class="bi bi-chat-left-text text-primary me-2"></i>Bình luận ({{ $comments->total() }})
        </h2>

        @if($canComment)
            <form method="POST" action="{{ route('comments.store', $document) }}" class="mb-4">@csrf
                <x-input name="body" label="Ý kiến của bạn về tài liệu" type="textarea" :rows="3" placeholder="Chia sẻ cảm nhận, bổ sung hoặc góp ý về tài liệu…" required />
                <button class="btn btn-primary btn-sm" type="submit">
                    <i class="bi bi-send me-1"></i>Đăng bình luận
                </button>
            </form>
        @elseif(!auth()->check() && $document->isPublished())
            <div class="alert alert-light border small text-muted mb-4">
                Vui lòng <a href="{{ route('login') }}" class="fw-semibold">đăng nhập</a> để tham gia bình luận tài liệu này.
            </div>
        @endif

        <div class="vstack gap-3">
            @forelse($comments as $c)
                <div class="p-3 rounded-3 bg-light border border-light-subtle">
                    <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="user-avatar-pill" style="width:24px;height:24px;font-size:0.7rem;">
                                {{ mb_substr($c->user->name ?? 'U', 0, 1) }}
                            </span>
                            <strong class="text-dark">{{ $c->user->name ?? 'Người dùng' }}</strong>
                            <span>·</span>
                            <span>{{ $c->created_at->diffForHumans() }}</span>
                        </div>
                        @auth
                            <div class="d-flex align-items-center gap-2">
                                @if($c->user_id === auth()->id() || auth()->user()->isAdmin())
                                    <form method="POST" action="{{ route('comments.destroy', $c) }}" data-confirm="Bạn có chắc muốn xóa bình luận này?">@csrf @method('DELETE')
                                        <button class="btn btn-link btn-sm text-danger p-0 text-decoration-none small">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </form>
                                @endif
                                @if($c->user_id !== auth()->id())
                                    <button class="btn btn-link btn-sm text-muted p-0 text-decoration-none small" type="button" data-bs-toggle="modal" data-bs-target="#reportModal" data-type="comment" data-id="{{ $c->id }}">
                                        <i class="bi bi-flag"></i> Báo cáo
                                    </button>
                                @endif
                            </div>
                        @endauth
                    </div>
                    <div class="text-secondary small">{!! nl2br(e($c->body)) !!}</div>
                </div>
            @empty
                <div class="text-muted text-center py-3 small">Chưa có bình luận nào. Hãy là người đầu tiên để lại ý kiến!</div>
            @endforelse
        </div>
        <div class="mt-3">
            {{ $comments->fragment('binh-luan')->links() }}
        </div>
    </div>
</div>

{{-- Sidebar --}}
<div class="col-lg-4">
    {{-- Ratings Card --}}
    <div class="card border-0 shadow-sm p-3 mb-3">
        <h2 class="h6 fw-bold mb-2 text-dark">
            <i class="bi bi-star-fill text-warning me-1"></i>Đánh giá từ cộng đồng
        </h2>
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="display-6 fw-bold text-dark">{{ number_format($avg, 1) }}</div>
            <div>
                <div class="text-warning small">
                    @for($s = 1; $s <= 5; $s++)
                        <i class="bi {{ $s <= round($avg) ? 'bi-star-fill' : 'bi-star' }}"></i>
                    @endfor
                </div>
                <div class="text-muted small">{{ $ratingCount }} lượt đánh giá</div>
            </div>
        </div>

        @auth
            @if($document->isPublished() && $document->user_id !== auth()->id())
                <div class="pt-2 border-top">
                    <label class="small text-muted mb-1 fw-semibold">Đánh giá của bạn:</label>
                    <form method="POST" action="{{ route('ratings.store', $document) }}">@csrf
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <div class="star-rating" id="interactiveStars" title="Bấm để chọn số sao">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star text-muted star-item" data-val="{{ $i }}"></i>
                                @endfor
                            </div>
                            <label for="score" class="visually-hidden">Số sao</label>
                            <select id="score" name="score" class="form-select form-select-sm d-none">
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" @selected($myRating == $i)>{{ $i }}</option>
                                @endfor
                            </select>
                            <button class="btn btn-sm btn-outline-primary" type="submit">Gửi</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                @if($document->user_id !== auth()->id())
                    <button class="btn btn-link btn-sm text-danger px-0 text-decoration-none" type="button" data-bs-toggle="modal" data-bs-target="#reportModal" data-type="document" data-id="{{ $document->id }}">
                        <i class="bi bi-flag me-1"></i>Báo cáo tài liệu vi phạm
                    </button>
                @else
                    <a class="btn btn-sm btn-outline-secondary w-100" href="{{ route('documents.edit', $document) }}">
                        <i class="bi bi-pencil me-1"></i>Chỉnh sửa tài liệu này
                    </a>
                @endif
            </div>
        @endauth
    </div>

    {{-- Related Documents --}}
    <div class="card border-0 shadow-sm p-3">
        <h2 class="h6 fw-bold mb-3 text-dark">
            <i class="bi bi-collection text-primary me-1"></i>Tài liệu liên quan
        </h2>
        @forelse($related as $r)
            <div class="d-flex align-items-start gap-2 mb-3 pb-2 border-bottom border-light-subtle">
                @php
                    $rExt = strtolower($r->extension ?? '');
                    $rClass = match($rExt) {
                        'pdf' => 'ext-pdf',
                        'docx' => 'ext-docx',
                        default => 'ext-txt',
                    };
                @endphp
                <span class="file-ext-badge {{ $rClass }} mt-1">
                    {{ strtoupper($r->extension) }}
                </span>
                <div class="flex-grow-1 min-w-0">
                    <a href="{{ route('library.show', $r) }}" class="text-dark fw-medium small text-decoration-none text-truncate d-block">
                        {{ $r->title }}
                    </a>
                    <div class="text-muted small d-flex justify-content-between mt-1">
                        <span>{{ $r->owner->name ?? 'Ẩn danh' }}</span>
                        <span><i class="bi bi-eye"></i> {{ number_format($r->view_count) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small mb-0">Chưa có tài liệu tương tự cùng danh mục.</p>
        @endforelse
    </div>
</div>
</div>

@auth
{{-- Report Modal --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="{{ route('reports.store') }}">@csrf
                <input type="hidden" name="type" id="rType">
                <input type="hidden" name="id" id="rId">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title fs-5 text-danger d-flex align-items-center gap-2" id="reportTitle">
                        <i class="bi bi-shield-exclamation"></i> Báo cáo nội dung
                    </h2>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="small text-muted mb-3">Vui lòng cung cấp lý do báo cáo để ban quản trị kiểm tra và xử lý kịp thời.</p>
                    <div class="mb-3">
                        <label for="rReason" class="form-label small fw-semibold">Lý do báo cáo</label>
                        <select id="rReason" name="reason" class="form-select form-select-sm" required>
                            @foreach(\App\Models\Report::REASONS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="rDetails" class="form-label small fw-semibold">Chi tiết bổ sung (tùy chọn)</label>
                        <textarea id="rDetails" name="details" class="form-control form-control-sm" rows="3" maxlength="1000" placeholder="Mô tả cụ thể vấn đề hoặc trích dẫn vi phạm…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button class="btn btn-danger btn-sm" type="submit">Gửi báo cáo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
var rModal = document.getElementById('reportModal');
if (rModal) {
  rModal.addEventListener('show.bs.modal', function (e) {
    document.getElementById('rType').value = e.relatedTarget.dataset.type;
    document.getElementById('rId').value = e.relatedTarget.dataset.id;
  });
}
</script>
@endpush
@endauth

@endsection
