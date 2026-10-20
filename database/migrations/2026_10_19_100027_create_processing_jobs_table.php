<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nhật ký tác vụ nền xử lý tệp
return new class extends Migration
{
    public function up()
    {
        Schema::create('processing_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('type', 30)->default('extract_text')->comment('Loại tác vụ');
            $table->string('status', 20)->default('queued')->comment('queued | running | completed | failed | needs_ocr');
            $table->unsignedTinyInteger('attempts')->default(0)->comment('Số lần thử');
            $table->text('error')->nullable()->comment('Thông báo lỗi');
            $table->timestamp('started_at')->nullable()->comment('Bắt đầu');
            $table->timestamp('finished_at')->nullable()->comment('Kết thúc');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('processing_jobs');
    }
};
