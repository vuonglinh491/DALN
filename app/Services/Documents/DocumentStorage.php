<?php

namespace App\Services\Documents;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/** Lưu tệp vào đĩa private (storage/app/documents/{userId}), tên ngẫu nhiên, không truy cập trực tiếp qua URL. */
class DocumentStorage
{
    public function store(UploadedFile $file, User $user): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('documents/' . $user->id, Str::uuid() . '.' . $ext, 'local');

        return [
            'storage_path' => $path,
            'extension' => $ext,
            'mime_type' => $this->mimeFor($ext),
            'size_bytes' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'original_filename' => Str::limit(preg_replace('/[\x00-\x1F\/\\\\]/', '_', $file->getClientOriginalName()), 250, ''),
        ];
    }

    public function mimeFor(string $ext): string
    {
        return [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
        ][$ext] ?? 'application/octet-stream';
    }
}
