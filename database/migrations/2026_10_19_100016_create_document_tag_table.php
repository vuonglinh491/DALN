<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bảng trung gian n-n giữa tài liệu và thẻ
return new class extends Migration
{
    public function up()
    {
        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['document_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_tag');
    }
};
