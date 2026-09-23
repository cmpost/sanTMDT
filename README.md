# Website Bưu điện tỉnh Cà Mau

Mã nguồn PHP + MySQL đầy đủ: trang bán hàng công khai + trang quản trị (CRUD sản phẩm, đăng tin tức, quản lý đơn hàng, theo dõi doanh thu).

Toàn bộ nội dung trong thư mục **`website/`** (thư mục chứa file README này) chính là những gì cần tải lên hosting — không tải thư mục cha bên ngoài.

## Yêu cầu hosting

- PHP 7.4 trở lên (khuyến nghị PHP 8.x), có bật extension `pdo_mysql`, `fileinfo`, `mbstring`, `gd` hoặc tương đương (hầu hết hosting cPanel tại Việt Nam đều đáp ứng sẵn).
- MySQL 5.7+ hoặc MariaDB 10+.
- Hỗ trợ `.htaccess` (Apache) — nếu hosting dùng Nginx, xem ghi chú cuối file.

## Các bước triển khai

### 1. Tạo cơ sở dữ liệu
Trong cPanel (hoặc công cụ quản trị hosting): tạo một MySQL Database mới + một MySQL User, gán quyền đầy đủ (ALL PRIVILEGES) cho user vào database đó. Ghi lại: tên database, tên user, mật khẩu, host (thường là `localhost`).

### 2. Import dữ liệu mẫu
Mở **phpMyAdmin** → chọn database vừa tạo → tab **Import** → chọn file `schema.sql` trong thư mục này → Go.
File này sẽ tạo đầy đủ các bảng (`categories`, `products`, `posts`, `orders`, `order_items`, `admin_users`, `settings`) và nạp sẵn danh mục/sản phẩm/tin tức mẫu về đặc sản Cà Mau để bạn có ngay nội dung khi mới lên web (có thể sửa/xoá sau trong trang quản trị).

### 3. Cấu hình kết nối
Sao chép file `config.sample.php` thành `config.php` (file `config.php` thật không được đưa lên GitHub — xem mục "Lưu ý về GitHub" cuối trang), sau đó sửa 4 dòng đầu cho đúng với thông tin ở bước 1:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ten_database_cua_ban');
define('DB_USER', 'ten_user_cua_ban');
define('DB_PASS', 'mat_khau_cua_ban');
```

### 4. Tải mã nguồn lên hosting
Dùng FTP (FileZilla) hoặc **File Manager** trong cPanel: tải **toàn bộ nội dung bên trong** thư mục `website/` lên thư mục gốc hiển thị web của bạn (thường là `public_html/`, hoặc `public_html/tenmien/` nếu cài ở tên miền phụ). Lưu ý tải cả các file ẩn bắt đầu bằng dấu chấm (`.htaccess`).

### 5. Cấp quyền ghi cho thư mục ảnh
Đảm bảo thư mục `uploads/` (và các thư mục con `uploads/products/`, `uploads/posts/`) có quyền ghi được, thường là **755** hoặc **775** tuỳ hosting. Có thể chỉnh qua File Manager → chuột phải → Permissions.

### 6. Khởi tạo tài khoản quản trị đầu tiên
Truy cập `https://tenmiencuaban.vn/admin/setup.php` — trang này **chỉ hoạt động một lần** khi hệ thống chưa có tài khoản quản trị nào. Nhập tên đăng nhập + mật khẩu (tối thiểu 8 ký tự) để tạo tài khoản đầu tiên. Sau khi tạo xong, trang này sẽ tự khoá và chuyển sang trang đăng nhập.

### 7. Đăng nhập & bắt đầu quản lý
Vào `https://tenmiencuaban.vn/admin/login.php`, đăng nhập bằng tài khoản vừa tạo. Từ đây bạn có thể:
- **Tổng quan**: xem doanh thu hôm nay/tháng/tổng, biểu đồ 7 ngày, sản phẩm bán chạy, đơn hàng gần đây.
- **Sản phẩm**: thêm/sửa/xoá sản phẩm, tải ảnh, đặt giá gốc/giá khuyến mãi, quản lý tồn kho.
- **Danh mục**: thêm/sửa/xoá nhóm hàng.
- **Tin tức**: đăng bài, lưu nháp, gỡ bài, sửa/xoá.
- **Đơn hàng**: xem chi tiết, cập nhật trạng thái (Chờ xác nhận → Đang giao → Hoàn thành / Đã huỷ). Doanh thu trên Tổng quan chỉ tính các đơn **Hoàn thành**.
- **Cài đặt**: hotline, địa chỉ, fanpage, thông báo đầu trang hiển thị ngoài trang bán hàng.

## Một số lưu ý quan trọng

- **Bảo mật**: đổi ngay mật khẩu quản trị nếu nghi ngờ bị lộ; luôn dùng HTTPS (hầu hết hosting Việt Nam đều có SSL miễn phí — bật trong cPanel mục "SSL/TLS Status" hoặc "Let's Encrypt"); không chia sẻ file `config.php`.
- **Sao lưu định kỳ**: xuất database (Export trong phpMyAdmin) và tải thư mục `uploads/` về định kỳ để phòng sự cố.
- **Đổi tên miền / chuyển thư mục**: hệ thống dùng đường dẫn tương đối nên có thể đặt ở tên miền chính hoặc thư mục con mà không cần sửa code.
- **Nginx**: nếu hosting dùng Nginx thay vì Apache, các file `.htaccess` sẽ không có tác dụng — cần cấu hình quản trị máy chủ để: (1) chặn truy cập trực tiếp `config.php`, `schema.sql`, thư mục `includes/`; (2) chặn thực thi PHP trong thư mục `uploads/`. Liên hệ nhà cung cấp hosting nếu cần hỗ trợ.
- **Sức chứa**: cấu trúc code phù hợp cho quy mô một bưu điện tỉnh (vài trăm sản phẩm, vài nghìn đơn/tháng). Nếu lượng truy cập tăng rất lớn, nên nâng cấp gói hosting hoặc nhờ đơn vị kỹ thuật tối ưu thêm.

## Lưu ý về GitHub

Repo này dùng để **lưu trữ và quản lý phiên bản mã nguồn**. Bản thân GitHub (kể cả GitHub Pages) **không chạy được PHP hay MySQL** — đây là hosting tĩnh (chỉ phục vụ HTML/CSS/JS thuần). Vì vậy quy trình dùng GitHub đúng cách cho website này là:

1. Đẩy (push) mã nguồn lên GitHub để lưu trữ, theo dõi thay đổi, hoặc chia sẻ cho người phụ trách kỹ thuật khác.
2. Trên **hosting PHP + MySQL thật** (cPanel, VPS...), kéo (pull/clone) mã nguồn từ GitHub về, hoặc tải lên qua FTP.
3. Trên hosting đó, tự tạo `config.php` từ `config.sample.php` như hướng dẫn ở bước 3 — file `config.php` chứa mật khẩu CSDL thật nên đã được loại khỏi Git (`.gitignore`) để không bị lộ nếu repo là public.
4. Thư mục `uploads/products/` và `uploads/posts/` cũng không đồng bộ qua Git (ảnh do quản trị viên tải lên trực tiếp trên hosting), chỉ giữ lại cấu trúc thư mục qua file `.gitkeep`.

Nếu muốn tự động triển khai mỗi khi push code (CI/CD), có thể thiết lập GitHub Actions để tự FTP/SSH mã nguồn lên hosting — đây là bước nâng cao, có thể nhờ đơn vị kỹ thuật hỗ trợ sau khi website đã chạy ổn định.

## Cấu trúc thư mục

```
website/
├── config.sample.php        # Mẫu cấu hình — sao chép thành config.php rồi điền thông tin thật
├── config.php               # (Tự tạo, không nằm trong Git) Cấu hình kết nối CSDL thật
├── schema.sql               # Cấu trúc bảng + dữ liệu mẫu
├── index.php, category.php, product.php, ...   # Các trang bán hàng công khai
├── cart.php, checkout.php, order_success.php    # Giỏ hàng & đặt hàng
├── includes/                # Các file lõi dùng chung (không truy cập trực tiếp được)
├── admin/                   # Toàn bộ trang quản trị
│   ├── setup.php            # Tạo tài khoản quản trị đầu tiên (chỉ chạy 1 lần)
│   ├── login.php / logout.php
│   ├── index.php            # Tổng quan / Dashboard
│   ├── products.php, product_edit.php, product_delete.php
│   ├── categories.php
│   ├── posts.php, post_edit.php, post_delete.php
│   ├── orders.php, order_view.php
│   └── settings.php
├── assets/css/style.css     # Toàn bộ giao diện (bán hàng + quản trị)
├── assets/images/           # Ảnh tĩnh dùng trong giao diện (ảnh trụ sở, banner...)
└── uploads/                 # Ảnh sản phẩm & bài viết do quản trị viên tải lên
```
