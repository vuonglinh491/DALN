<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Cấu hình ứng dụng chỉnh được trong trang admin
return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('setting_key', 100)->primary()->comment('Khóa cấu hình');
            $table->text('setting_value')->nullable()->comment('Giá trị');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
