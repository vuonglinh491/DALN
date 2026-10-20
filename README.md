# Smart Document Hub — Hệ thống hỗ trợ và tra cứu tài liệu thông minh

Smart Document Hub là ứng dụng web chia sẻ, quản lý và tra cứu tài liệu học tập. Người dùng có thể tải tài liệu lên, theo dõi kiểm duyệt, tìm kiếm nội dung và hỏi trợ lý AI. Câu trả lời AI được tạo từ nội dung tài liệu đã truy xuất và có trích dẫn nguồn để người dùng đối chiếu.

## Công nghệ

- **Backend:** PHP 8.0.2+, Laravel 9
- **Cơ sở dữ liệu:** MySQL/MariaDB; SQLite trong bộ nhớ dùng cho kiểm thử
- **Giao diện:** Blade, Bootstrap 5 qua CDN, CSS/JavaScript và Fetch API
- **Xử lý tài liệu:** Laravel Queue với database driver; trích xuất PDF, DOCX, TXT
- **AI:** Gemini API với RAG tự triển khai và truy xuất từ khóa bằng MySQL FULLTEXT

## Chức năng

### Người dùng và thư viện

- Đăng ký, đăng nhập, đăng xuất, đặt lại/đổi mật khẩu và cập nhật hồ sơ.
- Duyệt thư viện, lọc theo danh mục, tìm kiếm, xem trước và tải tài liệu theo quyền.
- Tải lên PDF, DOCX, TXT; quản lý tài liệu của mình, phiên bản và trạng thái xử lý.
- Đánh dấu yêu thích, đánh giá, bình luận, báo cáo nội dung; xem lịch sử tải và thông báo.

### Tìm kiếm và trợ lý AI

- Tìm kiếm theo thông tin tài liệu và nội dung đã lập chỉ mục; có gợi ý tìm kiếm.
- Hỏi đáp, tóm tắt, giải thích khái niệm, tìm ý chính và tạo câu hỏi ôn tập.
- Trợ lý truy xuất nội dung tài liệu được phép sử dụng, lưu lịch sử hội thoại và gắn nguồn vào câu trả lời. Với PDF, trích dẫn có thể bao gồm số trang.
- Có giới hạn sử dụng, timeout/retry và xử lý rõ các trường hợp thiếu nguồn hoặc lỗi Gemini.

### Quản trị

- Dashboard, quản lý người dùng và vai trò, khóa/mở khóa tài khoản.
- Kiểm duyệt tài liệu: duyệt, từ chối, yêu cầu chỉnh sửa, ẩn/hiện lại; lưu lý do và lịch sử.
- Quản lý danh mục, thẻ, bình luận và báo cáo.
- Theo dõi tác vụ xử lý tài liệu, lỗi tích hợp AI, nhật ký kiểm toán và cấu hình giới hạn.

## Yêu cầu

Trên Windows, có thể sử dụng XAMPP:

- PHP **8.0.2 trở lên** (khuyến nghị 8.1/8.2), MySQL hoặc MariaDB.
- Composer 2.x.
- Bật các PHP extension: `mbstring`, `openssl`, `pdo_mysql`, `fileinfo`, `curl`, `zip`, `xml`; thêm `pdo_sqlite` để chạy kiểm thử.
- Kết nối Internet để tải Bootstrap từ CDN và gọi Gemini API.

Không cần Node.js để chạy ứng dụng với các tài nguyên giao diện hiện có. `package.json` chỉ phục vụ các tác vụ Vite khi muốn phát triển/build tài nguyên frontend.

Kiểm tra môi trường:

```powershell
php -v
composer -V
php -m
```

## Cài đặt và cấu hình

Mở Terminal tại thư mục gốc dự án:

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
```

Lệnh chỉ tạo `.env` nếu chưa có để tránh ghi đè cấu hình hiện tại. Nếu cần tăng giới hạn tải lên, cập nhật `UPLOAD_MAX_KB` trong `.env` và bảo đảm `upload_max_filesize` cùng `post_max_size` trong `php.ini` không thấp hơn giới hạn đó.

### Cơ sở dữ liệu

1. Khởi động MySQL/MariaDB trong XAMPP.
2. Tạo database `smart_document_hub` với collation `utf8mb4_unicode_ci`.
3. Cập nhật thông tin `DB_*` trong `.env` nếu khác cấu hình XAMPP mặc định (`root`, mật khẩu trống).
4. Tạo cấu trúc bảng bằng **một** trong hai cách sau, không kết hợp:

   **Cách khuyến nghị — chạy migrations:**

   ```powershell
   php artisan migrate
   ```

   **Hoặc nhập schema có sẵn:** dùng phpMyAdmin → chọn database → *Import* → chọn [`database/schema_smart_document_hub.sql`](database/schema_smart_document_hub.sql).

5. Tạo vai trò và danh mục mặc định:

   ```powershell
   php artisan db:seed
   ```

6. Tạo tài khoản quản trị. Lệnh sẽ hỏi họ tên, email và nhập mật khẩu ẩn; mật khẩu cần có ít nhất 8 ký tự, gồm chữ và số:

   ```powershell
   php artisan daln:create-admin
   ```

   Có thể cấp vai trò quản trị cho email đã tồn tại; lệnh không thay đổi mật khẩu tài khoản đó.

### Khởi động ứng dụng

Mở **hai cửa sổ Terminal** tại thư mục dự án:

**Cửa sổ 1 — web server:**

```powershell
php artisan serve
```

Truy cập http://localhost:8000. Có thể dùng `chay-web.bat` thay cho lệnh trên.

**Cửa sổ 2 — worker xử lý tài liệu:**

```powershell
php artisan queue:work --tries=1 --timeout=600 --sleep=3
```

Hoặc dùng `chay-queue.bat`. Worker cần chạy để trích xuất văn bản và lập chỉ mục tài liệu tải lên. Nếu worker không hoạt động, các tài liệu sẽ ở trạng thái chờ xử lý và chưa thể tìm kiếm nội dung hoặc dùng làm nguồn cho AI.

**Kiểm tra nhanh:** đăng ký tài khoản → tải lên tệp `.txt` có nội dung → chờ trạng thái lập chỉ mục hoàn tất → đăng nhập bằng tài khoản admin → duyệt tài liệu trong mục *Kiểm duyệt tài liệu* → kiểm tra tài liệu xuất hiện trong thư viện.

## Cấu hình Gemini

1. Tạo API key tại [Google AI Studio](https://aistudio.google.com/apikey).
2. Điền key vào `.env`:

   ```dotenv
   GEMINI_API_KEY=your_api_key
   GEMINI_MODEL=gemini-2.5-flash
   ```

3. Nếu ứng dụng đang chạy, nạp lại cấu hình:

   ```powershell
   php artisan config:clear
   ```

   Sau đó khởi động lại web server và worker queue.

Giữ API key trong `.env`, không đưa vào mã nguồn hoặc chia sẻ lên Git. Giới hạn câu hỏi/ngày, độ dài câu hỏi và đầu ra AI có thể điều chỉnh tại *Quản trị → Cấu hình*. Lỗi tích hợp được ghi nhận trong trang giám sát quản trị.

## Email đặt lại mật khẩu

Mặc định `MAIL_MAILER=log`: liên kết đặt lại mật khẩu được ghi vào `storage/logs/laravel.log`. Để gửi email thật, cấu hình SMTP trong `.env`.

## Kiểm thử

Chạy toàn bộ kiểm thử:

```powershell
php artisan test
```

Bộ test sử dụng SQLite trong bộ nhớ theo cấu hình [`phpunit.xml`](phpunit.xml), nên không tác động database MySQL/MariaDB. Các lời gọi Gemini được mô phỏng trong test; không cần API key thật. Cần bật `pdo_sqlite`. Xem phạm vi kiểm thử và các kịch bản kiểm tra giao diện trong [`docs/KIEM-THU.md`](docs/KIEM-THU.md).

## Sao lưu và khôi phục

- **Database:** sao lưu bằng `mysqldump -u root smart_document_hub > backup.sql` (XAMPP thường có công cụ tại `C:\xampp\mysql\bin\mysqldump.exe`). Khôi phục vào database đã tạo bằng `mysql -u root smart_document_hub < backup.sql` hoặc phpMyAdmin.
- **Tệp tài liệu:** sao lưu cùng database thư mục `storage/app/documents`. Tệp được lưu riêng tư và không thể truy cập trực tiếp qua URL.
- **Cấu hình:** lưu bản `.env` ở nơi an toàn; không đưa bản sao này lên Git.

## Giới hạn hiện tại

- Tìm kiếm nội dung và truy xuất RAG hiện dựa trên từ khóa (MySQL FULLTEXT), chưa có semantic search/embeddings. Câu hỏi dùng từ ngữ khác xa tài liệu có thể không tìm thấy nguồn.
- Chưa có OCR. PDF dạng ảnh quét được đánh dấu cần OCR và không dùng được để tìm kiếm nội dung hoặc hỏi AI.
- DOCX/TXT không có số trang gốc để trích dẫn; số trang chỉ có thể cung cấp khi nguồn là PDF.
- Email chưa được xác thực bắt buộc theo mặc định; cần cấu hình SMTP nếu muốn gửi email thật.
- Câu trả lời AI hiện hiển thị sau khi hoàn tất xử lý, chưa streaming từng phần.
- Bootstrap được tải qua CDN, vì vậy cần Internet để tải đầy đủ giao diện.

## Tài liệu dự án

- [`docs/KIEN-TRUC.md`](docs/KIEN-TRUC.md) — yêu cầu, quy tắc nghiệp vụ, kiến trúc, luồng RAG và route.
- [`docs/MO-TA-BANG.md`](docs/MO-TA-BANG.md) — sơ đồ quan hệ và mô tả cơ sở dữ liệu.
- [`docs/KIEM-THU.md`](docs/KIEM-THU.md) — phạm vi và hướng dẫn kiểm thử.
