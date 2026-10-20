@extends('layouts.panel')
@section('title', 'Trợ lý AI · Hỏi đáp tài liệu thông minh')
@section('panel')

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="h4 fw-bold mb-1 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-stars text-primary"></i> Trợ lý AI Gemini
        </h1>
        <p class="small text-muted mb-0">Hỏi đáp, tóm tắt và giải thích nội dung học tập dựa trên dữ liệu tài liệu có dẫn chứng số trang</p>
    </div>
    <a href="{{ route('ai.history') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-clock-history me-1"></i>Xem lịch sử
    </a>
</div>

@unless($configured)
    <div class="alert alert-warning border-0 shadow-xs mb-3">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Chưa cấu hình <code>GEMINI_API_KEY</code> trong file <code>.env</code> máy chủ. Vui lòng thêm khóa để bắt đầu sử dụng.
    </div>
@endunless

{{-- Start Conversation Card --}}
<div class="card border-0 shadow-sm p-4 mb-4">
    <h2 class="h6 fw-bold mb-3 text-dark d-flex align-items-center gap-2">
        <i class="bi bi-chat-square-quote text-primary"></i> Bắt đầu phiên làm việc mới
    </h2>
    <form method="POST" action="{{ route('ai.start') }}" class="row g-3" data-no-lock="1">@csrf
        <div class="col-md-5">
            <label class="form-label small fw-semibold" for="scope">Phạm vi tài liệu đối chiếu</label>
            <select id="scope" name="scope" class="form-select form-select-sm" onchange="var d=document.getElementById('docWrap'); if(this.value==='document'){d.classList.remove('d-none');}else{d.classList.add('d-none');}">
                @foreach(\App\Models\Conversation::SCOPES as $k => $v)
                    <option value="{{ $k }}" @selected($preselect ? $k === 'document' : $k === 'all')>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5 {{ $preselect ? '' : 'd-none' }}" id="docWrap">
            <label class="form-label small fw-semibold" for="document_id">Chọn tài liệu cụ thể</label>
            <select id="document_id" name="document_id" class="form-select form-select-sm">
                <option value="">— Chọn một tài liệu —</option>
                @foreach($docs as $d)
                    <option value="{{ $d->id }}" @selected($preselect === $d->id)>
                        {{ \Illuminate\Support\Str::limit($d->title, 55) }}@if($d->user_id === auth()->id()) (của tôi)@endif
                    </option>
                @endforeach
            </select>
            @error('document_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary btn-sm w-100 py-2" type="submit">
                <i class="bi bi-arrow-right-circle me-1"></i>Bắt đầu
            </button>
        </div>
    </form>
    <div class="mt-3 pt-3 border-top small text-muted d-flex align-items-center gap-2">
        <i class="bi bi-shield-check text-success fs-5"></i>
        <span>AI chỉ trả lời dựa trên nội dung tài liệu đã được xử lý văn bản và tự động đính kèm số trang trích dẫn.</span>
    </div>
</div>

{{-- Recent Conversations --}}
<h2 class="h6 fw-bold mb-3 text-dark d-flex align-items-center gap-2">
    <i class="bi bi-clock-history text-secondary"></i> Hội thoại gần đây
</h2>
<div class="vstack gap-2">
    @forelse($recent as $c)
        <a class="card p-3 text-decoration-none text-reset border-0 shadow-xs hover-shadow d-flex flex-row justify-content-between align-items-center" href="{{ route('ai.show', $c) }}">
            <div class="d-flex align-items-center gap-2 text-truncate me-2">
                <i class="bi bi-chat-dots text-primary"></i>
                <strong class="text-dark text-truncate">{{ $c->title }}</strong>
            </div>
            <span class="small text-muted text-nowrap">{{ $c->updated_at->diffForHumans() }}</span>
        </a>
    @empty
        <x-empty icon="bi-chat-dots" title="Chưa có phiên hội thoại nào">
            Chọn phạm vi tài liệu ở trên và bắt đầu khám phá kiến thức với AI.
        </x-empty>
    @endforelse
</div>

@endsection
