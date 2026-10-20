<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Các đoạn văn bản đã trích xuất (phục vụ tìm kiếm và RAG)
return new class extends Migration
{
    public function up()
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index')->comment('Thứ tự đoạn trong tài liệu');
            $table->unsignedSmallInteger('page_start')->nullable()->comment('Trang bắt đầu (PDF)');
            $table->unsignedSmallInteger('page_end')->nullable()->comment('Trang kết thúc (PDF)');
            $table->text('content')->comment('Nội dung đoạn');
            $table->unsignedInteger('char_count')->comment('Số ký tự');
            $table->timestamps();
            $table->unique(['document_id', 'chunk_index']);
        });
        // FULLTEXT chỉ có trên MySQL/MariaDB (môi trường test dùng SQLite sẽ bỏ qua)
        if (DB::getDriverName() === 'mysql') {
            Schema::table('document_chunks', function (Blueprint $table) {
                $table->fullText(['content']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('document_chunks');
    }
};
