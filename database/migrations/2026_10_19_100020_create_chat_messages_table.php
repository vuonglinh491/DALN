<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tin nhắn trong hội thoại
return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('role', 20)->comment('user | assistant');
            $table->longText('content')->comment('Nội dung');
            $table->string('mode', 20)->nullable()->comment('ask | summarize | explain | keypoints | quiz');
            $table->string('status', 20)->default('completed')->comment('completed | failed');
            $table->string('error', 500)->nullable()->comment('Mã/ghi chú lỗi tích hợp AI');
            $table->boolean('grounded')->default(0)->comment('1 = câu trả lời có dẫn nguồn đã xác minh');
            $table->timestamps();
            $table->index(['conversation_id', 'id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
