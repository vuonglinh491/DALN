-- ============================================================
-- Smart Document Hub - cấu trúc cơ sở dữ liệu (MySQL / MariaDB)
-- Cách dùng: tạo database smart_document_hub rồi import file này (phpMyAdmin > Import).
-- LƯU Ý: dùng MỘT trong hai cách: import file này HOẶC chạy `php artisan migrate`. Không dùng cả hai.
-- Sau khi import, vẫn chạy: php artisan db:seed (tạo vai trò, danh mục) và php artisan daln:create-admin
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `name` varchar(255) NOT NULL COMMENT 'Họ tên',
  `email` varchar(255) NOT NULL COMMENT 'Email đăng nhập (duy nhất)',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm xác thực email',
  `password` varchar(255) NOT NULL COMMENT 'Mật khẩu đã băm (bcrypt)',
  `remember_token` varchar(100) DEFAULT NULL COMMENT 'Token ghi nhớ đăng nhập',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = hoạt động, 0 = bị khóa',
  `locked_reason` varchar(255) DEFAULT NULL COMMENT 'Lý do khóa tài khoản',
  `bio` text DEFAULT NULL COMMENT 'Giới thiệu ngắn',
  `ai_consent_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm đồng ý cho AI xử lý tài liệu riêng tư của chính mình',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Lần đăng nhập gần nhất',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tài khoản người dùng';

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `name` varchar(50) NOT NULL COMMENT 'Mã vai trò: admin | user',
  `label` varchar(100) NOT NULL COMMENT 'Tên hiển thị',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vai trò hệ thống (admin, user)';

CREATE TABLE `role_user` (
  `role_id` bigint unsigned NOT NULL COMMENT 'FK -> roles.id',
  `user_id` bigint unsigned NOT NULL COMMENT 'FK -> users.id',
  PRIMARY KEY (`role_id`,`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng trung gian n-n giữa người dùng và vai trò';

CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `name` varchar(150) NOT NULL COMMENT 'Tên danh mục',
  `slug` varchar(160) NOT NULL COMMENT 'Đường dẫn thân thiện',
  `description` varchar(500) NULL DEFAULT NULL COMMENT 'Mô tả',
  `sort_order` smallint unsigned NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = đang dùng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục tài liệu';

CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `user_id` bigint unsigned NOT NULL COMMENT 'Chủ sở hữu (FK -> users.id)',
  `category_id` bigint unsigned NOT NULL COMMENT 'Danh mục (FK -> categories.id)',
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề',
  `description` text NULL DEFAULT NULL COMMENT 'Mô tả',
  `subject` varchar(150) NULL DEFAULT NULL COMMENT 'Môn học / lĩnh vực',
  `original_filename` varchar(255) NOT NULL COMMENT 'Tên tệp gốc khi tải lên',
  `storage_path` varchar(500) NOT NULL COMMENT 'Đường dẫn tệp hiện hành trong storage/app (đĩa private)',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME type đã xác thực',
  `extension` varchar(10) NOT NULL COMMENT 'pdf | docx | txt',
  `size_bytes` bigint unsigned NOT NULL COMMENT 'Dung lượng (byte)',
  `checksum` char(64) NOT NULL COMMENT 'SHA-256 của tệp, dùng phát hiện trùng',
  `page_count` smallint unsigned NULL DEFAULT NULL COMMENT 'Số trang (PDF) nếu xác định được',
  `visibility` varchar(20) NOT NULL DEFAULT 'private' COMMENT 'public | private',
  `moderation_status` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'draft | pending | approved | rejected | changes_requested | hidden',
  `processing_status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending | processing | completed | failed | needs_ocr',
  `processing_error` text NULL DEFAULT NULL COMMENT 'Lỗi xử lý tệp gần nhất',
  `review_note` varchar(1000) NULL DEFAULT NULL COMMENT 'Lý do của quyết định kiểm duyệt gần nhất',
  `reviewed_by` bigint unsigned NULL DEFAULT NULL COMMENT 'Admin kiểm duyệt gần nhất',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm kiểm duyệt gần nhất',
  `submitted_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm gửi duyệt',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm được duyệt',
  `current_version` int unsigned NOT NULL DEFAULT 1 COMMENT 'Số phiên bản hiện hành',
  `view_count` int unsigned NOT NULL DEFAULT 0 COMMENT 'Lượt xem (mỗi phiên 1 lần, không tính chủ sở hữu)',
  `download_count` int unsigned NOT NULL DEFAULT 0 COMMENT 'Lượt tải (mỗi lượt tải thành công của người dùng đăng nhập)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Xóa mềm',
  PRIMARY KEY (`id`),
  KEY `documents_checksum_index` (`checksum`),
  KEY `documents_moderation_status_visibility_index` (`moderation_status`,`visibility`),
  KEY `documents_processing_status_index` (`processing_status`),
  KEY `documents_user_id_moderation_status_index` (`user_id`,`moderation_status`),
  FULLTEXT KEY `documents_title_description_subject_fulltext` (`title`,`description`,`subject`),
  CONSTRAINT `documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `documents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `documents_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tài liệu do người dùng tải lên';

CREATE TABLE `document_versions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `document_id` bigint unsigned NOT NULL COMMENT 'FK -> documents.id',
  `version_number` int unsigned NOT NULL COMMENT 'Số phiên bản (bắt đầu từ 1)',
  `original_filename` varchar(255) NOT NULL COMMENT 'Tên tệp gốc',
  `storage_path` varchar(500) NOT NULL COMMENT 'Đường dẫn tệp của phiên bản',
  `size_bytes` bigint unsigned NOT NULL COMMENT 'Dung lượng (byte)',
  `checksum` char(64) NOT NULL COMMENT 'SHA-256',
  `created_by` bigint unsigned NOT NULL COMMENT 'Người tải phiên bản',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_versions_document_id_version_number_unique` (`document_id`,`version_number`),
  CONSTRAINT `document_versions_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_versions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Các phiên bản tệp của tài liệu';

CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `name` varchar(60) NOT NULL COMMENT 'Tên thẻ',
  `slug` varchar(70) NOT NULL COMMENT 'Đường dẫn thân thiện',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_name_unique` (`name`),
  UNIQUE KEY `tags_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thẻ (từ khóa) gắn cho tài liệu';

CREATE TABLE `document_tag` (
  `document_id` bigint unsigned NOT NULL COMMENT 'FK -> documents.id',
  `tag_id` bigint unsigned NOT NULL COMMENT 'FK -> tags.id',
  PRIMARY KEY (`document_id`,`tag_id`),
  CONSTRAINT `document_tag_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng trung gian n-n giữa tài liệu và thẻ';

CREATE TABLE `document_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `document_id` bigint unsigned NOT NULL COMMENT 'FK -> documents.id',
  `admin_id` bigint unsigned NOT NULL COMMENT 'Admin ra quyết định',
  `decision` varchar(30) NOT NULL COMMENT 'approve | reject | changes | hide | restore',
  `reason` varchar(1000) NULL DEFAULT NULL COMMENT 'Lý do',
  `version_number` int unsigned NOT NULL COMMENT 'Phiên bản tài liệu được duyệt',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_reviews_document_id_created_at_index` (`document_id`,`created_at`),
  CONSTRAINT `document_reviews_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_reviews_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch sử từng lần kiểm duyệt';

CREATE TABLE `document_chunks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `document_id` bigint unsigned NOT NULL COMMENT 'FK -> documents.id',
  `chunk_index` int unsigned NOT NULL COMMENT 'Thứ tự đoạn trong tài liệu',
  `page_start` smallint unsigned NULL DEFAULT NULL COMMENT 'Trang bắt đầu (PDF)',
  `page_end` smallint unsigned NULL DEFAULT NULL COMMENT 'Trang kết thúc (PDF)',
  `content` text NOT NULL COMMENT 'Nội dung đoạn',
  `char_count` int unsigned NOT NULL COMMENT 'Số ký tự',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_chunks_document_id_chunk_index_unique` (`document_id`,`chunk_index`),
  FULLTEXT KEY `document_chunks_content_fulltext` (`content`),
  CONSTRAINT `document_chunks_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Các đoạn văn bản đã trích xuất (phục vụ tìm kiếm và RAG)';

CREATE TABLE `conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `user_id` bigint unsigned NOT NULL COMMENT 'Chủ hội thoại',
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề (rút từ câu hỏi đầu)',
  `scope` varchar(20) NOT NULL DEFAULT 'all' COMMENT 'document | mine | public | all',
  `document_id` bigint unsigned NULL DEFAULT NULL COMMENT 'Tài liệu được chọn khi scope=document',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_user_id_updated_at_index` (`user_id`,`updated_at`),
  CONSTRAINT `conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hội thoại với trợ lý AI';

CREATE TABLE `chat_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `conversation_id` bigint unsigned NOT NULL COMMENT 'FK -> conversations.id',
  `role` varchar(20) NOT NULL COMMENT 'user | assistant',
  `content` longtext NOT NULL COMMENT 'Nội dung',
  `mode` varchar(20) NULL DEFAULT NULL COMMENT 'ask | summarize | explain | keypoints | quiz',
  `status` varchar(20) NOT NULL DEFAULT 'completed' COMMENT 'completed | failed',
  `error` varchar(500) NULL DEFAULT NULL COMMENT 'Mã/ghi chú lỗi tích hợp AI',
  `grounded` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = câu trả lời có dẫn nguồn đã xác minh',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_conversation_id_id_index` (`conversation_id`,`id`),
  KEY `chat_messages_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tin nhắn trong hội thoại';

CREATE TABLE `message_sources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `chat_message_id` bigint unsigned NOT NULL COMMENT 'FK -> chat_messages.id',
  `document_id` bigint unsigned NOT NULL COMMENT 'FK -> documents.id',
  `document_chunk_id` bigint unsigned NULL DEFAULT NULL COMMENT 'Chunk được trích (NULL nếu đã xử lý lại)',
  `ref_no` tinyint unsigned NOT NULL COMMENT 'Số [n] hiển thị trong câu trả lời',
  `page_start` smallint unsigned NULL DEFAULT NULL COMMENT 'Trang của đoạn trích',
  `excerpt` text NOT NULL COMMENT 'Đoạn trích nguyên văn từ chunk',
  `score` double NULL DEFAULT NULL COMMENT 'Điểm truy xuất',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `message_sources_chat_message_id_foreign` FOREIGN KEY (`chat_message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_sources_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_sources_document_chunk_id_foreign` FOREIGN KEY (`document_chunk_id`) REFERENCES `document_chunks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nguồn trích dẫn của câu trả lời AI (gắn với chunk thực tế)';

CREATE TABLE `bookmarks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `user_id` bigint unsigned NOT NULL COMMENT 'FK -> users.id',
  `document_id` bigint unsigned NOT NULL COMMENT 'FK -> documents.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bookmarks_user_id_document_id_unique` (`user_id`,`document_id`),
  CONSTRAINT `bookmarks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tài liệu yêu thích';

CREATE TABLE `downloads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `user_id` bigint unsigned NOT NULL COMMENT 'Người tải',
  `document_id` bigint unsigned NOT NULL COMMENT 'Tài liệu',
  `version_number` int unsigned NOT NULL DEFAULT 1 COMMENT 'Phiên bản đã tải',
  `ip_address` varchar(45) NULL DEFAULT NULL COMMENT 'IP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `downloads_document_id_index` (`document_id`),
  KEY `downloads_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `downloads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `downloads_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch sử tải xuống';

CREATE TABLE `ratings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `user_id` bigint unsigned NOT NULL COMMENT 'Người đánh giá',
  `document_id` bigint unsigned NOT NULL COMMENT 'Tài liệu',
  `score` tinyint unsigned NOT NULL COMMENT 'Điểm 1..5',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ratings_user_id_document_id_unique` (`user_id`,`document_id`),
  CONSTRAINT `ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đánh giá tài liệu (1-5 sao)';

CREATE TABLE `comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `document_id` bigint unsigned NOT NULL COMMENT 'Tài liệu',
  `user_id` bigint unsigned NOT NULL COMMENT 'Tác giả bình luận',
  `body` text NOT NULL COMMENT 'Nội dung',
  `status` varchar(20) NOT NULL DEFAULT 'visible' COMMENT 'visible | hidden',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_document_id_status_index` (`document_id`,`status`),
  CONSTRAINT `comments_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bình luận';

CREATE TABLE `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `reporter_id` bigint unsigned NOT NULL COMMENT 'Người báo cáo',
  `reportable_type` varchar(50) NOT NULL COMMENT 'document | comment',
  `reportable_id` bigint unsigned NOT NULL COMMENT 'ID đối tượng bị báo cáo',
  `reason` varchar(100) NOT NULL COMMENT 'Lý do chọn sẵn',
  `details` text NULL DEFAULT NULL COMMENT 'Chi tiết bổ sung',
  `status` varchar(20) NOT NULL DEFAULT 'open' COMMENT 'open | resolved | dismissed',
  `handled_by` bigint unsigned NULL DEFAULT NULL COMMENT 'Admin xử lý',
  `handled_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm xử lý',
  `resolution_note` varchar(1000) NULL DEFAULT NULL COMMENT 'Ghi chú xử lý',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_reportable_type_reportable_id_index` (`reportable_type`,`reportable_id`),
  KEY `reports_status_index` (`status`),
  CONSTRAINT `reports_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Báo cáo nội dung vi phạm (đa hình: document | comment)';

CREATE TABLE `processing_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `document_id` bigint unsigned NOT NULL COMMENT 'Tài liệu được xử lý',
  `type` varchar(30) NOT NULL DEFAULT 'extract_text' COMMENT 'Loại tác vụ',
  `status` varchar(20) NOT NULL DEFAULT 'queued' COMMENT 'queued | running | completed | failed | needs_ocr',
  `attempts` tinyint unsigned NOT NULL DEFAULT 0 COMMENT 'Số lần thử',
  `error` text NULL DEFAULT NULL COMMENT 'Thông báo lỗi',
  `started_at` timestamp NULL DEFAULT NULL COMMENT 'Bắt đầu',
  `finished_at` timestamp NULL DEFAULT NULL COMMENT 'Kết thúc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `processing_jobs_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `processing_jobs_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký tác vụ nền xử lý tệp';

CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `user_id` bigint unsigned NULL DEFAULT NULL COMMENT 'Người thực hiện',
  `action` varchar(100) NOT NULL COMMENT 'Mã hành động, vd document.approve',
  `subject_type` varchar(50) NULL DEFAULT NULL COMMENT 'Loại đối tượng',
  `subject_id` bigint unsigned NULL DEFAULT NULL COMMENT 'ID đối tượng',
  `metadata` json NULL DEFAULT NULL COMMENT 'Chi tiết bổ sung (không chứa dữ liệu nhạy cảm)',
  `ip_address` varchar(45) NULL DEFAULT NULL COMMENT 'IP',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký kiểm toán';

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL COMMENT 'Khóa cấu hình',
  `setting_value` text NULL DEFAULT NULL COMMENT 'Giá trị',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cấu hình ứng dụng chỉnh được trong trang admin';

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL COMMENT 'Email yêu cầu đặt lại',
  `token` varchar(255) NOT NULL COMMENT 'Token đã băm',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Token đặt lại mật khẩu (Laravel)';

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Job thất bại (Laravel queue)';

CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hàng đợi database (Laravel queue)';

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL COMMENT 'Lớp thông báo',
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL COMMENT 'ID người nhận',
  `data` text NOT NULL COMMENT 'Nội dung JSON',
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm đã đọc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông báo cho người dùng';

SET FOREIGN_KEY_CHECKS=1;
