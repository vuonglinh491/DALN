@extends('layouts.panel', ['panel' => 'admin'])
@section('title', 'Cấu hình hệ thống')
@section('panel')

<div class="mb-4">
    <h1 class="h4 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
        <i class="bi bi-sliders text-primary"></i> Cấu hình tham số hệ thống
    </h1>
    <p class="small text-muted mb-0">Tùy chỉnh hạn mức câu hỏi AI, kích thước tệp tải lên và độ dài xem trước tài liệu</p>
</div>

{{-- Integration Status Card --}}
<div class="card border-0 shadow-sm p-4 mb-4">
    <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-link-45deg text-primary"></i> Trạng thái dịch vụ tích hợp (Chỉ đọc)
    </h2>
    <div class="row g-3 small">
        <div class="col-md-6">
            <div class="p-3 rounded bg-light border border-light-subtle">
                <div class="text-muted mb-1">Khóa tích hợp Gemini AI:</div>
                <div class="d-flex align-items-center gap-2">
                    @if($geminiConfigured)
                        <x-status-badge label="Đã cấu hình hợp lệ" tone="success"/>
                    @else
                        <x-status-badge label="Chưa cấu hình (cần GEMINI_API_KEY trong .env)" tone="danger"/>
                    @endif
                </div>
                <div class="mt-2 text-muted small">Mô hình AI: <code>{{ $geminiModel }}</code></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded bg-light border border-light-subtle">
                <div class="text-muted mb-1">Trình điều khiển hàng đợi (Queue):</div>
                <div>
                    <span class="badge text-bg-light border font-monospace">{{ $queue }}</span>
                </div>
                <div class="mt-2 text-muted small">Tài liệu tải lên được xếp hàng bóc tách nền qua Worker.</div>
            </div>
        </div>
    </div>
    <div class="small text-muted mt-3">
        <i class="bi bi-shield-lock me-1"></i>Vì lý do bảo mật, khóa API chỉ được lưu trữ an toàn trong biến môi trường của máy chủ và không bao giờ hiển thị trên giao diện này.
    </div>
</div>

{{-- Settings Form Card --}}
<div class="card border-0 shadow-sm p-4">
    <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-gear-fill text-primary"></i> Tham số hoạt động hệ thống
    </h2>
    <form method="POST" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <x-input name="ai_daily_limit" label="Số câu hỏi AI tối đa mỗi người dùng / ngày" type="number" :value="$values['ai_daily_limit']" required placeholder="50" />
            </div>
            <div class="col-md-6">
                <x-input name="ai_max_question_chars" label="Độ dài tối đa của câu hỏi (ký tự)" type="number" :value="$values['ai_max_question_chars']" required placeholder="500" />
            </div>
            <div class="col-md-6">
                <x-input name="ai_max_output_tokens" label="Giới hạn độ dài phản hồi AI (tokens)" type="number" :value="$values['ai_max_output_tokens']" required placeholder="1024" />
            </div>
            <div class="col-md-6">
                <x-input name="upload_max_kb" label="Dung lượng tệp tải lên tối đa (KB)" type="number" :value="$values['upload_max_kb']" required help="Ví dụ: 15360 = 15 MB. Phải nhỏ hơn upload_max_filesize trong php.ini." placeholder="15360" />
            </div>
            <div class="col-md-12">
                <x-input name="preview_chars" label="Độ dài đoạn xem trước công khai (ký tự)" type="number" :value="$values['preview_chars']" required help="Số lượng ký tự đầu tiên trích xuất hiển thị cho người xem trước khi tải." placeholder="3000" />
            </div>
        </div>
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-check2-circle me-1"></i>Lưu cấu hình hệ thống
        </button>
    </form>
</div>

@endsection
