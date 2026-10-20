@props(['doc', 'showStatus' => false])
<div class="card doc-card h-100">
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge badge-soft badge-primary">
                <i class="bi bi-folder2 text-primary me-1"></i>{{ $doc->category->name ?? 'Tài liệu' }}
            </span>
            @php
                $ext = strtolower($doc->extension ?? '');
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
                <i class="bi {{ $extIcon }}"></i> {{ strtoupper($doc->extension) }}
            </span>
        </div>
        <h3 class="card-title mb-2">
            <a class="stretched-link text-decoration-none" href="{{ route('library.show', $doc) }}">{{ $doc->title }}</a>
        </h3>
        @if($doc->description)
            <p class="small text-muted mb-3 line-clamp">{{ $doc->description }}</p>
        @endif
        <div class="mt-auto pt-2 border-top border-light-subtle small text-muted">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-truncate me-2"><i class="bi bi-person text-secondary"></i> {{ $doc->owner->name ?? 'Ẩn danh' }}</span>
                <span class="text-nowrap text-muted"><i class="bi bi-calendar3"></i> {{ ($doc->approved_at ?? $doc->updated_at)->format('d/m/Y') }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-eye text-secondary"></i> {{ number_format($doc->view_count) }}
                    <span class="mx-1">·</span>
                    <i class="bi bi-download text-secondary"></i> {{ number_format($doc->download_count) }}
                </span>
                @if(isset($doc->ratings_avg_score) && $doc->ratings_avg_score)
                    <span class="text-nowrap">
                        <i class="bi bi-star-fill text-warning"></i>
                        <strong class="text-dark">{{ number_format($doc->ratings_avg_score, 1) }}</strong>
                    </span>
                @endif
            </div>
            @if($showStatus)
                <div class="mt-2 pt-1 border-top"><x-status-badge :label="$doc->moderation_label[0]" :tone="$doc->moderation_label[1]" /></div>
            @endif
        </div>
    </div>
</div>
