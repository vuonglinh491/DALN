<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('remember_token')->comment('1 = hoạt động, 0 = bị khóa');
            $table->string('locked_reason', 255)->nullable()->after('is_active')->comment('Lý do khóa tài khoản');
            $table->text('bio')->nullable()->after('locked_reason')->comment('Giới thiệu ngắn');
            $table->timestamp('ai_consent_at')->nullable()->after('bio')->comment('Đồng ý cho AI xử lý tài liệu riêng tư của chính mình');
            $table->timestamp('last_login_at')->nullable()->after('ai_consent_at')->comment('Lần đăng nhập gần nhất');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'locked_reason', 'bio', 'ai_consent_at', 'last_login_at']);
        });
    }
};
