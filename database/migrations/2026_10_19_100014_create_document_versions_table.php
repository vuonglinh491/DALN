<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Các phiên bản tệp của tài liệu
return new class extends Migration
{
    public function up()
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->comment('Số phiên bản (bắt đầu từ 1)');
            $table->string('original_filename', 255)->comment('Tên tệp gốc');
            $table->string('storage_path', 500)->comment('Đường dẫn tệp của phiên bản');
            $table->unsignedBigInteger('size_bytes')->comment('Dung lượng (byte)');
            $table->char('checksum', 64)->comment('SHA-256');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['document_id', 'version_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_versions');
    }
};
