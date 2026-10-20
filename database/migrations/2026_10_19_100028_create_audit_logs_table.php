<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nhật ký kiểm toán
return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->comment('Mã hành động, vd document.approve');
            $table->string('subject_type', 50)->nullable()->comment('Loại đối tượng');
            $table->unsignedBigInteger('subject_id')->nullable()->comment('ID đối tượng');
            $table->json('metadata')->nullable()->comment('Chi tiết bổ sung (không chứa dữ liệu nhạy cảm)');
            $table->string('ip_address', 45)->nullable()->comment('IP');
            $table->timestamps();
            $table->index(['action']);
            $table->index(['created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
