<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Thẻ (từ khóa) gắn cho tài liệu
return new class extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique()->comment('Tên thẻ');
            $table->string('slug', 70)->unique()->comment('Đường dẫn thân thiện');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tags');
    }
};
