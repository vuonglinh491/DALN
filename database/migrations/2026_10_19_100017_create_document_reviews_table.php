<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Lịch sử từng lần kiểm duyệt
return new class extends Migration
{
    public function up()
    {
        Schema::create('document_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->restrictOnDelete();
            $table->string('decision', 30)->comment('approve | reject | changes | hide | restore');
            $table->string('reason', 1000)->nullable()->comment('Lý do');
            $table->unsignedInteger('version_number')->comment('Phiên bản tài liệu được duyệt');
            $table->timestamps();
            $table->index(['document_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_reviews');
    }
};
