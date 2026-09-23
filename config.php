<?php
/**
 * Cấu hình hệ thống — sửa các giá trị bên dưới cho đúng với hosting thật
 * trước khi đưa website lên mạng. Xem hướng dẫn chi tiết trong README.md.
 */

// ==== Thông tin kết nối cơ sở dữ liệu MySQL ====
define('DB_HOST', 'localhost');
define('DB_NAME', 'buudien_camau');
define('DB_USER', 'buudien_camau');
define('DB_PASS', 'doi_mat_khau_nay');
define('DB_CHARSET', 'utf8mb4');

// ==== Thư mục lưu ảnh tải lên (đường dẫn tuyệt đối trên máy chủ) ====
define('UPLOAD_DIR', __DIR__ . '/uploads');
// Đường dẫn tương đối để hiển thị ảnh trên trình duyệt (giữ nguyên nếu website
// được cài ở thư mục gốc của tên miền)
define('UPLOAD_URL', 'uploads');

// ==== Múi giờ ====
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Website vận hành chính thức: TẮT hiển thị lỗi ra trình duyệt (an toàn hơn).
// Nếu cần dò lỗi khi mới cài đặt, tạm đổi '0' thành '1' bên dưới rồi đổi lại sau.
error_reporting(E_ALL);
ini_set('display_errors', '0');
