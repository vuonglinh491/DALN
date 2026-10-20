@extends('layouts.panel')
@section('title', $conversation->title)
@section('panel')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
    <div>
        <h1 class="h5 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-stars text-primary"></i> {{ $conversation->title }}
        </h1>
        <div class="small text-muted d-flex align-items-center gap-2">
            <span class="badge badge-soft badge-primary">
                {{ \App\Models\Conversation::SCOPES[$conversation->scope] }}
            </span>
            @if($conversation->document)
                <span class="text-truncate" style="max-width: 320px;">
                    <i class="bi bi-file-earmark-text me-1"></i>{{ $conversation->document->title }}
                </span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('ai.index') }}">
            <i class="bi bi-plus-lg me-1"></i>Hội thoại mới
        </a>
        <form method="POST" action="{{ route('ai.destroy', $conversation) }}" data-confirm="Xóa toàn bộ cuộc hội thoại này?">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" title="Xóa hội thoại">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </div>
</div>

@unless($configured)
    <div class="alert alert-warning py-2 border-0 shadow-xs mb-3">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Chưa cấu hình <code>GEMINI_API_KEY</code> — AI chưa thể trả lời.
    </div>
@endunless

@if($problem)
    <div class="alert alert-warning py-2 border-0 shadow-xs mb-3">
        <i class="bi bi-shield-exclamation me-1"></i> {{ $problem }}
        @if(!auth()->user()->ai_consent_at)
            <form method="POST" action="{{ route('ai.consent') }}" class="mt-2 pt-2 border-top">@csrf
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="agree" id="agree" value="1" required>
                    <label class="form-check-label small" for="agree">
                        Tôi đồng ý cho AI xử lý tài liệu riêng tư của chính mình (nội dung liên quan được gửi tới Gemini an toàn)
                    </label>
                </div>
                <button class="btn btn-sm btn-primary mt-2">Xác nhận đồng ý</button>
            </form>
        @endif
    </div>
@endif

<div class="chat-wrap card border-0 shadow-sm p-3">
    <div class="chat-log" id="chatLog" role="log" aria-live="polite" aria-label="Nội dung hội thoại">
        @forelse($messages as $m)
            @include('ai._message', ['m' => $m])
        @empty
            <div class="text-center my-auto py-5" id="chatEmpty">
                <div class="stat-icon info mx-auto mb-2" style="width:52px;height:52px;font-size:1.75rem;">
                    <i class="bi bi-stars"></i>
                </div>
                <h2 class="h6 fw-bold text-dark mb-1">Trợ lý AI sẵn sàng giải đáp</h2>
                <p class="small text-muted mb-4" style="max-width: 460px; margin: 0 auto;">
                    Đặt câu hỏi hoặc chọn các chế độ xử lý chuyên sâu bên dưới. AI sẽ tra cứu trong tài liệu và trích dẫn số trang cho bạn.
                </p>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" class="btn btn-light btn-sm border chat-chip" data-mode="summary" data-prompt="Hãy tóm tắt ngắn gọn những nội dung quan trọng nhất của tài liệu này.">
                        <i class="bi bi-file-earmark-text text-primary me-1"></i>Tóm tắt tài liệu
                    </button>
                    <button type="button" class="btn btn-light btn-sm border chat-chip" data-mode="keypoints" data-prompt="Liệt kê các ý chính và luận điểm cốt lõi trong tài liệu.">
                        <i class="bi bi-list-check text-success me-1"></i>Các ý chính
                    </button>
                    <button type="button" class="btn btn-light btn-sm border chat-chip" data-mode="quiz" data-prompt="Tạo 5 câu hỏi ôn tập kèm đáp án dựa trên nội dung tài liệu.">
                        <i class="bi bi-question-circle text-warning me-1"></i>Câu hỏi ôn tập
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <form id="chatForm" class="mt-3" data-url="{{ route('ai.message', $conversation) }}" data-no-lock="1" autocomplete="off">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1" for="mode">Chế độ xử lý</label>
                <select id="mode" name="mode" class="form-select form-select-sm">
                    @foreach(config('daln.modes') as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-7">
                <label class="visually-hidden" for="question">Câu hỏi</label>
                <textarea id="question" name="question" class="form-control" rows="1" maxlength="{{ $maxChars }}" placeholder="Nhập câu hỏi (Shift+Enter để xuống dòng)…" required style="resize:none;"></textarea>
            </div>
            <div class="col-md-2">
                <button id="sendBtn" class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-send-fill"></i> Gửi
                </button>
            </div>
        </div>
        <div class="text-danger small mt-2 d-none" id="chatError" role="alert"></div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('js/chat.js') }}"></script>
@endpush
@endsection
