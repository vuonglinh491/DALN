<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nguồn trích dẫn của câu trả lời AI (gắn với chunk thực tế)
return new class extends Migration
{
    public function up()
    {
        Schema::create('message_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('document_chunk_id')->nullable()->constrained('document_chunks')->nullOnDelete();
            $table->unsignedTinyInteger('ref_no')->comment('Số [n] hiển thị trong câu trả lời');
            $table->unsignedSmallInteger('page_start')->nullable()->comment('Trang của đoạn trích');
            $table->text('excerpt')->comment('Đoạn trích nguyên văn từ chunk');
            $table->double('score')->nullable()->comment('Điểm truy xuất');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_sources');
    }
};
