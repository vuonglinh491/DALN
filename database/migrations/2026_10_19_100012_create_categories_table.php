<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Danh mục tài liệu
return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique()->comment('Tên danh mục');
            $table->string('slug', 160)->unique()->comment('Đường dẫn thân thiện');
            $table->string('description', 500)->nullable()->comment('Mô tả');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->boolean('is_active')->default(1)->comment('1 = đang dùng');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
