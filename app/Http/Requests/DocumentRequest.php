<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Quy tắc chung cho tải lên / chỉnh sửa tài liệu. Người dùng KHÔNG gửi user_id: chủ sở hữu luôn lấy từ phiên đăng nhập. */
abstract class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function baseRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'subject' => ['nullable', 'string', 'max:150'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', 1)],
            'tags' => ['nullable', 'string', 'max:300'],
            'visibility' => ['required', 'in:public,private'],
        ];
    }

    protected function fileRules(bool $required): array
    {
        $maxKb = (int) Setting::get('upload_max_kb');

        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:' . $maxKb,
            function ($attribute, $value, $fail) {
                $this->checkFile($value, $fail);
            },
        ];
    }

    /** Kiểm tra phần mở rộng + chữ ký (magic bytes) thật của tệp, không tin tên tệp/MIME do client gửi. */
    protected function checkFile($file, \Closure $fail): void
    {
        if (!$file || !$file->isValid()) {
            return;
        }
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, config('daln.upload.extensions'), true)) {
            $fail('Chỉ chấp nhận tệp PDF, DOCX hoặc TXT.');
            return;
        }
        if ($file->getSize() === 0) {
            $fail('Tệp rỗng, không thể tải lên.');
            return;
        }
        $head = (string) file_get_contents($file->getRealPath(), false, null, 0, 4096);

        if ($ext === 'pdf' && strncmp($head, '%PDF', 4) !== 0) {
            $fail('Nội dung tệp không phải PDF hợp lệ.');
        } elseif ($ext === 'docx') {
            if (strncmp($head, "PK\x03\x04", 4) !== 0) {
                $fail('Nội dung tệp không phải DOCX hợp lệ.');
            } elseif (class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive();
                if ($zip->open($file->getRealPath()) === true) {
                    if ($zip->locateName('word/document.xml') === false) {
                        $fail('Nội dung tệp không phải DOCX hợp lệ.');
                    }
                    $zip->close();
                } else {
                    $fail('Nội dung tệp không phải DOCX hợp lệ.');
                }
            }
        } elseif ($ext === 'txt' && (str_contains($head, "\0") || strncmp($head, '%PDF', 4) === 0 || strncmp($head, 'PK', 2) === 0)) {
            $fail('Nội dung tệp không phải văn bản thuần (TXT).');
        }
    }
}
