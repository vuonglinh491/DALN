<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Hội thoại với trợ lý AI
return new class extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255)->comment('Tiêu đề (rút từ câu hỏi đầu)');
            $table->string('scope', 20)->default('all')->comment('document | mine | public | all');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};
