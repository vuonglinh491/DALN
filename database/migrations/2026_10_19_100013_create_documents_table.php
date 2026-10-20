<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tài liệu do người dùng tải lên
return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('title', 255)->comment('Tiêu đề');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->string('subject', 150)->nullable()->comment('Môn học / lĩnh vực');
            $table->string('original_filename', 255)->comment('Tên tệp gốc khi tải lên');
            $table->string('storage_path', 500)->comment('Đường dẫn tệp hiện hành trong storage/app (đĩa private)');
            $table->string('mime_type', 100)->comment('MIME type đã xác thực');
            $table->string('extension', 10)->comment('pdf | docx | txt');
            $table->unsignedBigInteger('size_bytes')->comment('Dung lượng (byte)');
            $table->char('checksum', 64)->index()->comment('SHA-256 của tệp, dùng phát hiện trùng');
            $table->unsignedSmallInteger('page_count')->nullable()->comment('Số trang (PDF) nếu xác định được');
            $table->string('visibility', 20)->default('private')->comment('public | private');
            $table->string('moderation_status', 20)->default('draft')->comment('draft | pending | approved | rejected | changes_requested | hidden');
            $table->string('processing_status', 20)->default('pending')->comment('pending | processing | completed | failed | needs_ocr');
            $table->text('processing_error')->nullable()->comment('Lỗi xử lý tệp gần nhất');
            $table->string('review_note', 1000)->nullable()->comment('Lý do của quyết định kiểm duyệt gần nhất');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->comment('Thời điểm kiểm duyệt gần nhất');
            $table->timestamp('submitted_at')->nullable()->comment('Thời điểm gửi duyệt');
            $table->timestamp('approved_at')->nullable()->comment('Thời điểm được duyệt');
            $table->unsignedInteger('current_version')->default(1)->comment('Số phiên bản hiện hành');
            $table->unsignedInteger('view_count')->default(0)->comment('Lượt xem (mỗi phiên 1 lần, không tính chủ sở hữu)');
            $table->unsignedInteger('download_count')->default(0)->comment('Lượt tải (mỗi lượt tải thành công của người dùng đăng nhập)');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['moderation_status', 'visibility']);
            $table->index(['processing_status']);
            $table->index(['user_id', 'moderation_status']);
        });
        // FULLTEXT chỉ có trên MySQL/MariaDB (môi trường test dùng SQLite sẽ bỏ qua)
        if (DB::getDriverName() === 'mysql') {
            Schema::table('documents', function (Blueprint $table) {
                $table->fullText(['title', 'description', 'subject']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
