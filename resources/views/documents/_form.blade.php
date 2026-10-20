@php $d = $document ?? null; @endphp
<x-input name="title" label="Tiêu đề tài liệu" :value="$d?->title" placeholder="Ví dụ: Giáo trình Giải tích 1 - ĐHBK Hà Nội" required />

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="f-category_id" class="form-label">Danh mục <span class="text-danger" aria-hidden="true">*</span></label>
            <select id="f-category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">— Chọn danh mục —</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $d?->category_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <x-input name="subject" label="Môn học / Lĩnh vực" :value="$d?->subject" placeholder="Ví dụ: Toán giải tích, CNTT, Kinh tế lượng" />
    </div>
</div>

<x-input name="description" label="Mô tả nội dung tóm tắt" type="textarea" :rows="3" :value="$d?->description" placeholder="Giới thiệu sơ lược về nội dung chính của tài liệu để người khác dễ tìm kiếm…" />

<x-input name="tags" label="Thẻ từ khóa (cách nhau bởi dấu phẩy)" :value="$d ? $d->tags->pluck('name')->implode(', ') : ''" help="Tối đa 8 thẻ. Ví dụ: giai tich, de thi, on tap, chuong 1" placeholder="giai tich, de thi, on tap" />

{{-- Modern File Dropzone --}}
<div class="mb-4">
    <label class="form-label d-flex justify-content-between">
        <span>Tệp tài liệu @unless($d)<span class="text-danger" aria-hidden="true">*</span>@endunless</span>
        <span class="text-muted small">Tối đa {{ number_format(\App\Models\Setting::get('upload_max_kb') / 1024, 0) }} MB</span>
    </label>
    <div class="file-dropzone">
        <input id="f-file" type="file" name="file" class="@error('file') is-invalid @enderror" accept=".pdf,.docx,.txt" @required(!$d)>

        <div class="dropzone-prompt">
            <div class="dropzone-icon">
                <i class="bi bi-cloud-arrow-up"></i>
            </div>
            <div class="fw-semibold text-dark mb-1">Kéo thả tệp vào đây hoặc bấm để chọn tệp từ máy tính</div>
            <div class="small text-muted mb-2">Hỗ trợ các định dạng PDF (có văn bản), Word (.docx) và văn bản thuần (.txt)</div>
            <div class="d-inline-flex gap-2">
                <span class="file-ext-badge ext-pdf"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</span>
                <span class="file-ext-badge ext-docx"><i class="bi bi-file-earmark-word-fill"></i> DOCX</span>
                <span class="file-ext-badge ext-txt"><i class="bi bi-file-earmark-text-fill"></i> TXT</span>
            </div>
        </div>

        <div class="dropzone-selected d-none">
            <div class="dropzone-icon text-success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="fw-bold text-dark dropzone-filename mb-1">ten_tep.pdf</div>
            <div class="small text-muted dropzone-filesize mb-2">0 KB</div>
            <span class="badge text-bg-primary">Bấm vào đây để chọn tệp khác</span>
        </div>
    </div>
    @if($d)
        <div class="form-text mt-1 text-muted">
            <i class="bi bi-info-circle me-1"></i>Chỉ chọn tệp mới nếu bạn muốn cập nhật phiên bản (hiện tại: <strong>v{{ $d->current_version }}</strong>, <code>{{ $d->original_filename }}</code>).
        </div>
    @endif
    @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

{{-- Visibility Selection --}}
<fieldset class="mb-4">
    <legend class="form-label fs-6 mb-2">Chế độ chia sẻ</legend>
    @php $vis = old('visibility', $d?->visibility ?? 'public'); @endphp
    <div class="row g-2">
        <div class="col-md-6">
            <label class="card p-3 h-100 border cursor-pointer {{ $vis === 'public' ? 'border-primary bg-primary-soft' : '' }}" for="vis-public" style="cursor:pointer;">
                <div class="form-check d-flex align-items-start gap-2 mb-0">
                    <input class="form-check-input mt-1" type="radio" name="visibility" id="vis-public" value="public" @checked($vis === 'public')>
                    <div>
                        <strong class="d-block text-dark"><i class="bi bi-globe me-1 text-primary"></i> Công khai (Public)</strong>
                        <span class="small text-muted">Tài liệu sẽ được gửi tới Ban quản trị kiểm duyệt trước khi xuất hiện trên Thư viện chung cho mọi người.</span>
                    </div>
                </div>
            </label>
        </div>
        <div class="col-md-6">
            <label class="card p-3 h-100 border cursor-pointer {{ $vis === 'private' ? 'border-primary bg-primary-soft' : '' }}" for="vis-private" style="cursor:pointer;">
                <div class="form-check d-flex align-items-start gap-2 mb-0">
                    <input class="form-check-input mt-1" type="radio" name="visibility" id="vis-private" value="private" @checked($vis === 'private')>
                    <div>
                        <strong class="d-block text-dark"><i class="bi bi-lock me-1 text-secondary"></i> Riêng tư (Private)</strong>
                        <span class="small text-muted">Chỉ mình bạn xem và sử dụng với trợ lý AI sau khi đồng ý chia sẻ nội dung riêng tư.</span>
                    </div>
                </div>
            </label>
        </div>
    </div>
    @error('visibility')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</fieldset>
