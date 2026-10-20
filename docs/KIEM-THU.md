# Tài liệu kiểm thử

## Cách chạy
```bash
php artisan test
```
Bộ test dùng **SQLite trong bộ nhớ** (cấu hình trong `phpunit.xml`) nên **không đụng tới database `smart_document_hub`** của bạn. Cần extension `pdo_sqlite` (bật sẵn trong XAMPP). Gemini được mô phỏng bằng `Http::fake()` — test không gọi API thật.

> **Lưu ý trung thực:** các test được viết cùng mã nguồn nhưng môi trường tạo ra gói này không có Packagist nên **chưa chạy được `php artisan test`** ở đó. Phần đã xác minh thực tế: cú pháp toàn bộ PHP (`php -l`), logic `Chunker`/`TextExtractor` bằng script độc lập, file SQL import vào MariaDB 10.11 và truy vấn FULLTEXT tiếng Việt. Hãy chạy `php artisan test` trên máy bạn và báo lại kết quả nếu có test đỏ.

## Phạm vi
| File | Nội dung |
|---|---|
| `tests/Unit/ChunkerTest` | Chia đoạn: giới hạn kích thước, giữ số trang, dòng quá dài, văn bản rỗng |
| `tests/Unit/TextExtractorTest` | TXT, DOCX, tệp rỗng, DOCX/PDF hỏng |
| `tests/Unit/CitationParserTest` | Lọc số nguồn hợp lệ, loại số nguồn bịa |
| `tests/Feature/AuthAccessTest` | Đăng ký (không leo thang vai trò), đăng nhập, khóa tài khoản, giới hạn đăng nhập, `/quan-tri` chỉ admin, trang cần đăng nhập |
| `tests/Feature/DocumentVisibilityTest` | Tài liệu chờ duyệt/riêng tư không xuất hiện công khai, 404 khi truy cập trái phép, quyền tải xuống, chỉ chủ sở hữu sửa/xóa |
| `tests/Feature/UploadTest` | Tệp sai định dạng/sai chữ ký/rỗng/quá lớn/thiếu, trùng lặp, chủ sở hữu lấy từ phiên, đẩy job xử lý, sửa tài liệu đã duyệt ⇒ chờ duyệt lại |
| `tests/Feature/DocumentProcessingTest` | Xử lý TXT thành công + xác minh chunk, xử lý lại thay chunk cũ, tệp hỏng/rỗng không bao giờ `completed` |
| `tests/Feature/ModerationTest` | Duyệt (lịch sử, audit, thông báo), bắt buộc lý do, chuyển trạng thái sai bị từ chối, ẩn, user thường bị 403 |
| `tests/Feature/SearchTest` | Chỉ tài liệu công khai; tìm trong nội dung chỉ với tài liệu đã lập chỉ mục; sắp xếp, lọc, gợi ý |
| `tests/Feature/AiChatTest` | Có nguồn (trang + trích đoạn), không có nguồn ⇒ không gọi Gemini, AI nói không đủ thông tin, bịa số nguồn bị loại, quota 429, timeout, thiếu API key, prompt injection bị cô lập, không lộ tài liệu riêng tư người khác, cần đồng ý với tài liệu riêng tư, tài liệu chưa xử lý, hội thoại riêng tư, giới hạn ngày/độ dài |

## Kiểm thử thủ công nên làm (giao diện)
1. Thu nhỏ trình duyệt ≈ 360px, 768px, 1280px: navbar thu gọn, thẻ tài liệu xuống dòng, chat dùng được.
2. Dùng phím Tab: có liên kết "Bỏ qua điều hướng", vòng focus hiển thị, form có nhãn, lỗi hiện đúng trường.
3. Tải PDF quét (ảnh) ⇒ trạng thái "Cần OCR", không thể hỏi AI.
