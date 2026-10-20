<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Đánh giá tài liệu (1-5 sao)
return new class extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->comment('Điểm 1..5');
            $table->timestamps();
            $table->unique(['user_id', 'document_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
