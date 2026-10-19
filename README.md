# 📚 HỆ THỐNG HỖ TRỢ VÀ TRA CỨU TÀI LIỆU THÔNG MINH

**Smart Document Hub & AI Assistant**

Hệ thống hỗ trợ và tra cứu tài liệu thông minh là một nền tảng web được xây dựng nhằm hỗ trợ người dùng lưu trữ, chia sẻ, tìm kiếm và khai thác thông tin từ tài liệu học tập, nghiên cứu. Hệ thống tích hợp trí tuệ nhân tạo Gemini AI để hỗ trợ hỏi đáp dựa trên nội dung tài liệu, giúp người dùng tiếp cận thông tin nhanh chóng, thuận tiện và chính xác hơn.

Dự án sử dụng PHP và Laravel cho phía máy chủ, MySQL/MariaDB để quản lý dữ liệu, HTML, CSS và JavaScript để xây dựng giao diện người dùng. Hệ thống được thiết kế theo mô hình phân quyền giữa quản trị viên và người dùng, đồng thời áp dụng quy trình kiểm duyệt nhằm đảm bảo tài liệu chỉ được công khai sau khi được phê duyệt.

---

## 📌 1. Thông tin dự án

| Thuộc tính | Nội dung |
|---|---|
| Tên dự án | Hệ thống hỗ trợ và tra cứu tài liệu thông minh |
| Tên tiếng Anh | Smart Document Hub & AI Assistant |
| Loại dự án | Website quản lý, chia sẻ và tra cứu tài liệu |
| Ngôn ngữ backend | PHP |
| Framework | Laravel |
| Frontend | HTML5, CSS3, JavaScript, Blade |
| Cơ sở dữ liệu | MySQL/MariaDB |
| Môi trường phát triển | XAMPP |
| Trí tuệ nhân tạo | Google Gemini API |
| Kiến trúc | Laravel MVC |
| Đối tượng sử dụng | Khách truy cập, người dùng, quản trị viên |

> Lưu ý: Các công nghệ và tính năng trong tài liệu là mục tiêu thiết kế của dự án. Chỉ xác nhận một tính năng đã hoàn thành sau khi mã nguồn thực tế được triển khai và kiểm thử.

---

## 🎯 2. Mục tiêu dự án

- Xây dựng nền tảng quản lý và chia sẻ tài liệu trực tuyến.
- Hỗ trợ tìm kiếm tài liệu theo tiêu đề, mô tả, danh mục, từ khóa và nội dung bên trong tệp.
- Tích hợp Gemini AI để hỗ trợ hỏi đáp, giải thích, tóm tắt và tra cứu thông tin từ tài liệu.
- Cho phép người dùng tải tài liệu lên và gửi quản trị viên kiểm duyệt trước khi công khai.
- Xây dựng cơ chế phân quyền rõ ràng giữa người dùng và quản trị viên.
- Bảo vệ tài liệu cá nhân, thông tin tài khoản và dữ liệu hệ thống.
- Thiết kế giao diện trực quan, thân thiện, responsive và dễ sử dụng.
- Tạo nền tảng có thể mở rộng thêm các tính năng học tập và quản lý tri thức trong tương lai.

---

## ✨ 3. Các chức năng của hệ thống

### 3.1. Chức năng dành cho khách truy cập

- Xem trang chủ và giới thiệu hệ thống.
- Duyệt thư viện tài liệu công khai.
- Tìm kiếm và lọc tài liệu đã được duyệt.
- Xem thông tin chi tiết và phần xem trước được phép.
- Đăng ký tài khoản và đăng nhập.

### 3.2. Chức năng dành cho người dùng

**Quản lý tài khoản**
- Đăng ký, đăng nhập và đăng xuất.
- Cập nhật thông tin cá nhân.
- Đổi mật khẩu và khôi phục mật khẩu theo cấu hình hệ thống.
- Quản lý ảnh đại diện nếu được triển khai.

**Quản lý tài liệu**
- Tải tài liệu lên hệ thống.
- Nhập tiêu đề, mô tả, danh mục và từ khóa.
- Theo dõi trạng thái kiểm duyệt.
- Chỉnh sửa thông tin hoặc gửi lại tài liệu theo yêu cầu.
- Quản lý danh sách tài liệu do mình đóng góp.
- Xóa tài liệu của mình theo chính sách hệ thống.

**Tra cứu tài liệu**
- Tìm kiếm theo từ khóa.
- Lọc theo danh mục, môn học, loại tài liệu và định dạng.
- Xem chi tiết tài liệu.
- Tải xuống tài liệu công khai khi được phép.
- Xem tài liệu liên quan.

**Trợ lý AI**
- Đặt câu hỏi bằng ngôn ngữ tự nhiên.
- Tra cứu nội dung từ tài liệu được phép truy cập.
- Hỗ trợ tóm tắt và giải thích nội dung.
- Trả lời kèm nguồn tài liệu và số trang khi xác minh được.
- Lưu lịch sử hội thoại nếu chức năng này được bật.

**Tương tác cộng đồng**
- Lưu tài liệu yêu thích.
- Đánh giá và bình luận.
- Báo cáo tài liệu vi phạm.
- Nhận thông báo về trạng thái tài liệu.

### 3.3. Chức năng dành cho quản trị viên

- Quản lý tài khoản người dùng.
- Quản lý vai trò và quyền truy cập.
- Xem danh sách tài liệu chờ kiểm duyệt.
- Duyệt, từ chối hoặc yêu cầu chỉnh sửa tài liệu.
- Ghi nhận lý do và lịch sử kiểm duyệt.
- Quản lý danh mục và từ khóa.
- Kiểm duyệt bình luận và xử lý báo cáo.
- Ẩn tài liệu không phù hợp.
- Theo dõi thống kê và nhật ký hoạt động.
- Theo dõi lỗi xử lý tài liệu và lỗi tích hợp AI nếu có.

---

## 🤖 4. Tích hợp Gemini AI và tra cứu tài liệu

Hệ thống định hướng sử dụng Gemini API để xây dựng trợ lý AI hỗ trợ tra cứu tài liệu.

### Quy trình xử lý dự kiến

1. Người dùng tải tài liệu lên hệ thống.
2. Laravel kiểm tra định dạng, dung lượng và quyền tải lên.
3. Tài liệu được lưu trữ an toàn và ghi nhận trong cơ sở dữ liệu.
4. Hệ thống trích xuất văn bản từ định dạng được hỗ trợ.
5. Nội dung được chia thành các đoạn phục vụ tìm kiếm.
6. Hệ thống lập chỉ mục hoặc tạo embedding theo kiến trúc RAG đã chọn.
7. Khi người dùng đặt câu hỏi, hệ thống truy xuất các đoạn liên quan mà người dùng có quyền truy cập.
8. Các đoạn phù hợp được gửi đến Gemini API để tạo câu trả lời.
9. Kết quả được hiển thị cùng nguồn dẫn có thể xác minh.
10. Hội thoại và thông tin nguồn được lưu lại theo chính sách lưu trữ dữ liệu.

### Nguyên tắc sử dụng AI

- API key được lưu trong biến môi trường, không đưa vào JavaScript phía trình duyệt hoặc Git.
- AI không được tự ý truy cập tài liệu chưa được cấp quyền.
- Câu trả lời cần phân biệt thông tin có căn cứ với nội dung chưa xác minh.
- Khi không tìm thấy thông tin phù hợp, hệ thống phải thông báo rõ ràng.
- Nguồn dẫn phải gắn với tài liệu và đoạn nội dung thực tế.
- Lỗi API, giới hạn sử dụng và thời gian chờ phải được xử lý phù hợp.

**Lưu ý:** Gemini API chỉ là một thành phần của quy trình. Để hỏi đáp trên tài liệu do người dùng tải lên, hệ thống còn cần cơ chế trích xuất, lập chỉ mục và truy xuất nội dung. Khả năng hỗ trợ PDF, DOCX, OCR và tìm kiếm ngữ nghĩa phụ thuộc vào các thành phần thực tế được triển khai.

---

## 🛡️ 5. Phân quyền và kiểm duyệt

Hệ thống dự kiến có ba nhóm quyền:

| Vai trò | Quyền chính |
|---|---|
| Khách truy cập | Xem và tìm kiếm tài liệu công khai |
| Người dùng | Quản lý tài khoản, tải lên và quản lý tài liệu của mình, tra cứu trong phạm vi được phép |
| Quản trị viên | Quản lý người dùng, kiểm duyệt tài liệu và quản lý hệ thống |

### Trạng thái tài liệu

- `pending`: Đang chờ kiểm duyệt.
- `approved`: Đã được duyệt.
- `rejected`: Bị từ chối.
- `revision_requested`: Được yêu cầu chỉnh sửa.
- `hidden`: Đã bị ẩn khỏi phạm vi công khai.

Chỉ tài liệu đủ điều kiện công khai mới được hiển thị trong thư viện và kết quả tìm kiếm công khai.

Việc kiểm tra quyền phải được thực hiện ở backend. Không được dựa vào việc ẩn nút trên giao diện để bảo vệ dữ liệu.

---

## 🧰 6. Công nghệ sử dụng

| Công nghệ | Vai trò |
|---|---|
| PHP | Ngôn ngữ lập trình phía máy chủ |
| Laravel | Framework backend và kiến trúc MVC |
| MySQL/MariaDB | Lưu trữ dữ liệu có cấu trúc |
| XAMPP | Môi trường phát triển cục bộ |
| Blade | Template engine của Laravel |
| HTML5 | Xây dựng cấu trúc trang web |
| CSS3 | Thiết kế giao diện |
| JavaScript | Tương tác phía trình duyệt |
| Fetch API/AJAX | Giao tiếp bất đồng bộ với backend |
| Gemini API | Hỗ trợ hỏi đáp bằng AI |
| Composer | Quản lý thư viện PHP |
| Git | Quản lý phiên bản mã nguồn |

Các thư viện bổ sung phục vụ đọc PDF, DOCX, tạo embedding, xác thực hoặc phân quyền sẽ được xác định trong quá trình triển khai.

---

## 🗂️ 7. Cấu trúc thư mục dự kiến

```text
DALN/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   ├── Services/
│   ├── Jobs/
│   └── Notifications/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── css/
│   ├── js/
│   └── images/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── web.php
│   └── console.php
├── storage/
├── tests/
│   ├── Feature/
│   └── Unit/
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
└── README.md
```

Đây là cấu trúc dự kiến. Một số thư mục có thể chưa tồn tại trong dự án mới và sẽ được tạo khi triển khai các phân hệ tương ứng.

Không đưa file `.env`, mật khẩu, API key hoặc tài liệu riêng tư lên kho mã nguồn công khai.

---

## 🗄️ 8. Thiết kế cơ sở dữ liệu

Tên cơ sở dữ liệu dự kiến: `smart_document_hub`.

Các bảng nghiệp vụ chính:

| Bảng | Chức năng |
|---|---|
| `users` | Tài khoản người dùng |
| `roles` | Danh sách vai trò |
| `role_user` | Quan hệ tài khoản và vai trò |
| `categories` | Danh mục tài liệu |
| `documents` | Thông tin tài liệu |
| `document_versions` | Lịch sử phiên bản tài liệu |
| `tags` | Từ khóa |
| `document_tag` | Quan hệ tài liệu và từ khóa |
| `document_reviews` | Lịch sử kiểm duyệt |
| `document_chunks` | Các đoạn nội dung đã trích xuất |
| `document_embeddings` | Vector nội dung nếu tự triển khai RAG |
| `conversations` | Phiên trò chuyện AI |
| `chat_messages` | Tin nhắn hội thoại |
| `message_sources` | Nguồn dẫn của câu trả lời |
| `bookmarks` | Tài liệu yêu thích |
| `downloads` | Lịch sử tải xuống |
| `ratings` | Đánh giá tài liệu |
| `comments` | Bình luận |
| `reports` | Báo cáo vi phạm |
| `notifications` | Thông báo |
| `processing_jobs` | Trạng thái xử lý tài liệu |
| `audit_logs` | Nhật ký thao tác quan trọng |

Laravel migrations sẽ được sử dụng để tạo và cập nhật cấu trúc database. Khóa chính, khóa ngoại, chỉ mục, ràng buộc duy nhất và quy tắc xóa dữ liệu phải được thiết kế theo quan hệ thực tế.

Các bảng mặc định của Laravel có thể được sử dụng để hỗ trợ xác thực, hàng đợi, cache và session.

---

## ⚙️ 9. Yêu cầu môi trường

### Phần mềm cần thiết

- Windows 10/11.
- XAMPP có phiên bản PHP tương thích.
- Composer.
- Laravel tương thích với phiên bản PHP đang sử dụng.
- MySQL hoặc MariaDB.
- Trình duyệt web hiện đại.
- Gemini API key nếu triển khai chức năng AI.

Có thể cần thêm Node.js và NPM nếu lựa chọn quy trình biên dịch frontend bằng Vite.

### Kiểm tra môi trường

Mở PowerShell và chạy:

```powershell
php -v
composer --version
```

Nếu một trong hai lệnh không hoạt động, cần cài đặt hoặc cấu hình biến môi trường `PATH` trước khi tiếp tục.

Kiểm tra phiên bản Laravel sau khi tạo dự án:

```powershell
php artisan --version
```

---

## 🚀 10. Cài đặt dự án trên máy tính cá nhân

### Bước 1: Tạo dự án Laravel

Nếu thư mục `D:\DALN` chưa tồn tại:

```powershell
cd D:\
composer create-project laravel/laravel DALN
cd D:\DALN
```

Nếu đã có source code, hãy sử dụng thư mục dự án hiện tại thay vì tạo đè lên thư mục đó.

### Bước 2: Cài đặt thư viện

Trong thư mục chứa `composer.json`, chạy:

```powershell
composer install
```

Lệnh này cài đặt các thư viện PHP theo cấu hình của dự án.

### Bước 3: Cấu hình file môi trường

Nếu dự án chưa có file `.env`:

```powershell
Copy-Item .env.example .env
```

Tạo khóa ứng dụng:

```powershell
php artisan key:generate
```

Không thay thế file `.env` đang có mà chưa sao lưu cấu hình hiện tại.

### Bước 4: Tạo database

1. Mở XAMPP Control Panel.
2. Khởi động MySQL hoặc MariaDB.
3. Truy cập `http://localhost/phpmyadmin`.
4. Tạo database `smart_document_hub`.
5. Chọn bộ ký tự `utf8mb4` và collation phù hợp.

Cấu hình `.env`:

```dotenv
APP_NAME="Smart Document Hub"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_document_hub
DB_USERNAME=root
DB_PASSWORD=
```

Thông tin đăng nhập database phải được điều chỉnh theo cấu hình MySQL thực tế của máy.

### Bước 5: Tạo bảng database

Sau khi đã có các migration tương ứng:

```powershell
php artisan migrate
```

Chỉ chạy seeder khi đã kiểm tra nội dung của chúng:

```powershell
php artisan db:seed
```

Không chạy lệnh `migrate:fresh` trên database có dữ liệu cần giữ vì lệnh này có thể xóa các bảng hiện có.

### Bước 6: Khởi chạy website

```powershell
php artisan serve
```

Mở trình duyệt:

http://127.0.0.1:8000

Để các lệnh trên hoạt động, dự án phải có đầy đủ source code, file cấu hình và migration cần thiết.

---

## 🔑 11. Cấu hình Gemini API

1. Truy cập Google AI Studio để tạo API key theo hướng dẫn chính thức.
2. Thêm cấu hình vào file `.env`:

```dotenv
GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL=your_supported_model
```

3. Khai báo cấu hình tương ứng trong `config/services.php`.
4. Gọi Gemini từ backend thông qua service riêng.
5. Kiểm tra lỗi xác thực, quota, timeout và phản hồi không hợp lệ.

Không đưa API key thật vào README, GitHub, mã JavaScript hoặc log.

Tên model phải được chọn theo danh sách model thực tế mà tài khoản và API hỗ trợ. Không giả định rằng một tên model cố định luôn khả dụng.

Đọc tài liệu chính thức:

- Gemini API: https://ai.google.dev/gemini-api/docs
- Gemini File Search: https://ai.google.dev/gemini-api/docs/file-search
- Laravel: https://laravel.com/docs

---

## 🧪 12. Kiểm thử

Các nhóm kiểm thử cần có:

- Đăng ký, đăng nhập và đăng xuất.
- Kiểm tra quyền admin/user.
- Upload tệp hợp lệ và từ chối tệp không hợp lệ.
- Kiểm tra tài liệu chờ duyệt không xuất hiện công khai.
- Duyệt, từ chối và gửi lại tài liệu.
- Tìm kiếm, lọc và phân trang.
- Tải xuống và kiểm tra quyền sở hữu.
- Tra cứu AI trên tài liệu được phép.
- Xác minh nguồn trích dẫn.
- Xử lý lỗi API, timeout và giới hạn sử dụng.
- Kiểm tra truy cập trái phép và bảo vệ dữ liệu.

Chạy kiểm thử bằng:

```powershell
php artisan test
```

Kết quả kiểm thử cần được ghi nhận dựa trên lần chạy thực tế.

---

## 🔐 13. Bảo mật và quyền riêng tư

- Sử dụng cơ chế hash mật khẩu của Laravel.
- Bảo vệ các form bằng CSRF và validation phía server.
- Kiểm tra quyền truy cập tại backend.
- Hạn chế loại tệp và dung lượng tải lên.
- Lưu tài liệu ở vị trí không thể thực thi trực tiếp.
- Không để lộ đường dẫn nội bộ của tài liệu riêng tư.
- Giới hạn tốc độ gọi API và các thao tác nhạy cảm.
- Bảo vệ API key và thông tin kết nối database.
- Ghi nhật ký các thao tác quản trị quan trọng.
- Kiểm soát dữ liệu gửi đến dịch vụ AI bên ngoài.
- Có chính sách xóa dữ liệu, lưu trữ và sao lưu phù hợp.

---

## 📈 14. Hướng phát triển trong tương lai

- Tìm kiếm ngữ nghĩa nâng cao.
- Hỗ trợ OCR cho PDF dạng ảnh.
- Hỗ trợ thêm các định dạng tài liệu.
- Gợi ý tài liệu liên quan theo nội dung.
- Tạo câu hỏi ôn tập và bộ flashcard từ tài liệu.
- Phân tích tài liệu dài và so sánh nhiều tài liệu.
- Hỗ trợ đa ngôn ngữ.
- Tích hợp thông báo email.
- Cải thiện dashboard thống kê.
- Tối ưu hiệu năng và mở rộng dung lượng lưu trữ.

---

## 👨‍💻 15. Quy trình phát triển

Dự án được triển khai theo các giai đoạn:

1. Kiểm tra môi trường và khởi tạo Laravel.
2. Phân tích yêu cầu và thiết kế database.
3. Xây dựng xác thực và phân quyền.
4. Phát triển giao diện và thư viện tài liệu.
5. Xây dựng upload và kiểm duyệt.
6. Phát triển tìm kiếm và trích xuất nội dung.
7. Tích hợp Gemini AI và nguồn trích dẫn.
8. Xây dựng các chức năng cộng đồng.
9. Hoàn thiện trang quản trị.
10. Kiểm thử, sửa lỗi và hoàn thiện tài liệu bàn giao.

Mỗi giai đoạn cần có tiêu chí nghiệm thu trước khi chuyển sang giai đoạn tiếp theo.

---

## 📄 16. Giấy phép và ghi chú

Dự án phục vụ mục đích học tập, nghiên cứu và phát triển hệ thống tra cứu tài liệu thông minh.

Giấy phép phân phối mã nguồn cần được xác định riêng trước khi công bố dự án. Các thư viện bên thứ ba và dịch vụ AI phải tuân theo điều khoản sử dụng tương ứng.

**Trạng thái dự án:** Đang trong quá trình phát triển.

**Lưu ý:** README này mô tả mục tiêu, kiến trúc và hướng dẫn cài đặt dự kiến. Những tính năng chưa được triển khai hoặc kiểm thử không được xem là đã hoàn thành.