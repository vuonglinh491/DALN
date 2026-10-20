<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Báo cáo nội dung vi phạm (đa hình: document | comment)
return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reportable_type', 50)->comment('document | comment');
            $table->unsignedBigInteger('reportable_id')->comment('ID đối tượng bị báo cáo');
            $table->string('reason', 100)->comment('Lý do chọn sẵn');
            $table->text('details')->nullable()->comment('Chi tiết bổ sung');
            $table->string('status', 20)->default('open')->comment('open | resolved | dismissed');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable()->comment('Thời điểm xử lý');
            $table->string('resolution_note', 1000)->nullable()->comment('Ghi chú xử lý');
            $table->timestamps();
            $table->index(['reportable_type', 'reportable_id']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
