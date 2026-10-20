<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bình luận
return new class extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->comment('Nội dung');
            $table->string('status', 20)->default('visible')->comment('visible | hidden');
            $table->timestamps();
            $table->index(['document_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
