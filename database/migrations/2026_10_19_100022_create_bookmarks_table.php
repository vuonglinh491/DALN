<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tài liệu yêu thích
return new class extends Migration
{
    public function up()
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'document_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookmarks');
    }
};
