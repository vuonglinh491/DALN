<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Vai trò hệ thống (admin, user)
return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Mã vai trò: admin | user');
            $table->string('label', 100)->comment('Tên hiển thị');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
