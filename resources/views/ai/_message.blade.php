<div class="msg {{ $m->role }} {{ $m->status === 'failed' ? 'failed' : '' }}">
    <div class="msg-content">
        {!! $m->content_html !!}
    </div>
    @if($m->role === 'assistant' && $m->sources->isNotEmpty())
        <div class="sources">
            <div class="fw-semibold text-muted small mb-1">
                <i class="bi bi-journal-check text-primary me-1"></i>Nguồn tài liệu đối chiếu:
            </div>
            @foreach($m->sources as $s)
                <div class="src">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <span>
                            <span class="ref-tag">[{{ $s->ref_no }}]</span>
                            <strong class="text-dark">{{ $s->document->title ?? 'Tài liệu' }}</strong>
                            @if($s->page_start)
                                <span class="badge text-bg-light border ms-1">Trang {{ $s->page_start }}</span>
                            @endif
                        </span>
                        @if($s->document && Gate::allows('view', $s->document))
                            <a class="btn btn-xs btn-outline-primary py-0 px-2 small text-nowrap" target="_blank" rel="noopener" href="{{ $s->document->extension === 'pdf' ? route('library.file', $s->document) . ($s->page_start ? '#page=' . $s->page_start : '') : route('library.show', $s->document) }}">
                                <i class="bi bi-box-arrow-up-right"></i> Xem gốc
                            </a>
                        @endif
                    </div>
                    <div class="text-muted small fst-italic ps-2 border-start border-2">“{{ $s->excerpt }}”</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
