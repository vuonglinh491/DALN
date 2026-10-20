<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Lịch sử tải xuống
return new class extends Migration
{
    public function up()
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version_number')->default(1)->comment('Phiên bản đã tải');
            $table->string('ip_address', 45)->nullable()->comment('IP');
            $table->timestamps();
            $table->index(['document_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('downloads');
    }
};
