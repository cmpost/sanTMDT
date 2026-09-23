-- =====================================================================
-- Bưu điện tỉnh Cà Mau — Cấu trúc cơ sở dữ liệu + dữ liệu mẫu
-- Import file này bằng phpMyAdmin (hoặc lệnh mysql) vào database rỗng
-- đã tạo sẵn trên hosting, trùng tên với DB_NAME trong config.php.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Danh mục sản phẩm
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(150) NOT NULL UNIQUE,
  icon VARCHAR(10) NOT NULL DEFAULT '🏷️',
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Sản phẩm
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NULL,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  price DECIMAL(12,0) NOT NULL DEFAULT 0,
  sale_price DECIMAL(12,0) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  icon VARCHAR(10) NOT NULL DEFAULT '📦',
  description TEXT NULL,
  status ENUM('active','hidden') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_products_category (category_id),
  INDEX idx_products_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tin tức / Bài đăng
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  tag VARCHAR(100) NOT NULL DEFAULT 'Thông báo',
  excerpt VARCHAR(500) NULL,
  content MEDIUMTEXT NULL,
  image VARCHAR(255) NULL,
  icon VARCHAR(10) NOT NULL DEFAULT '📰',
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_posts_status (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Đơn hàng
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(150) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  customer_address VARCHAR(500) NOT NULL,
  total DECIMAL(14,0) NOT NULL DEFAULT 0,
  status ENUM('pending','shipping','completed','cancelled') NOT NULL DEFAULT 'pending',
  note VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_status (status),
  INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NULL,
  product_name VARCHAR(255) NOT NULL,
  price DECIMAL(12,0) NOT NULL,
  qty INT NOT NULL,
  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tài khoản quản trị (tạo lần đầu qua admin/setup.php, KHÔNG seed sẵn ở đây)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Cài đặt chung của website (luôn chỉ có 1 dòng, id = 1)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  id INT PRIMARY KEY,
  site_name VARCHAR(200) NOT NULL DEFAULT 'Bưu điện tỉnh Cà Mau',
  hotline VARCHAR(50) NOT NULL DEFAULT '',
  address VARCHAR(300) NOT NULL DEFAULT '',
  email VARCHAR(150) NOT NULL DEFAULT '',
  fanpage VARCHAR(200) NOT NULL DEFAULT '',
  banner_text VARCHAR(300) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (id, site_name, hotline, address, email, fanpage, banner_text) VALUES
(1, 'Bưu điện tỉnh Cà Mau', '0290 383 9090', 'Số 01, đường Lý Thường Kiệt, Phường 5, TP. Cà Mau, tỉnh Cà Mau',
 'cskh@buudiencamau.vn', 'facebook.com/BuudienCaMau',
 'Bưu điện tỉnh Cà Mau — Kết nối đặc sản quê hương, giao hàng toàn quốc')
ON DUPLICATE KEY UPDATE id = id;

-- =====================================================================
-- Dữ liệu mẫu — danh mục
-- =====================================================================
INSERT INTO categories (name, slug, icon, sort_order) VALUES
('Đặc sản Cà Mau', 'dac-san-ca-mau', '🦐', 1),
('Nông sản OCOP', 'nong-san-ocop', '🌾', 2),
('Bưu phẩm - Dịch vụ', 'buu-pham-dich-vu', '📮', 3),
('Đồ gia dụng', 'do-gia-dung', '🏠', 4),
('Chăm sóc cá nhân', 'cham-soc-ca-nhan', '🧴', 5),
('Combo quà tặng', 'combo-qua-tang', '🎁', 6);

-- =====================================================================
-- Dữ liệu mẫu — sản phẩm (category_id tương ứng thứ tự thêm ở trên: 1..6)
-- =====================================================================
INSERT INTO products (category_id, name, slug, price, sale_price, stock, icon, description, status) VALUES
(1, 'Tôm khô Rạch Gốc loại 1 (500g)', 'tom-kho-rach-goc-loai-1-500g', 420000, 380000, 40, '🦐', 'Tôm khô nguyên con, phơi nắng tự nhiên, không chất bảo quản.', 'active'),
(1, 'Cua biển Năm Căn (size 300-400g/con)', 'cua-bien-nam-can-size-300-400g', 320000, 0, 15, '🦀', 'Cua gạch tươi sống, đóng thùng giữ lạnh khi vận chuyển.', 'active'),
(1, 'Ba khía muối Rạch Gốc (hũ 500g)', 'ba-khia-muoi-rach-goc-hu-500g', 95000, 0, 60, '🦞', 'Đặc sản trứ danh vùng rừng ngập mặn Cà Mau.', 'active'),
(1, 'Mắm cá đồng U Minh (hũ 500g)', 'mam-ca-dong-u-minh-hu-500g', 85000, 0, 0, '🍶', 'Mắm cá lóc đồng, lên men tự nhiên theo phương pháp truyền thống.', 'active'),
(1, 'Cá khô sặc bổi U Minh Hạ (300g)', 'ca-kho-sac-boi-u-minh-ha-300g', 150000, 135000, 25, '🐟', 'Cá sặc bổi nuôi tự nhiên trong rừng tràm U Minh.', 'active'),
(1, 'Tôm khô Đất Mũi đặc biệt (1kg)', 'tom-kho-dat-mui-dac-biet-1kg', 780000, 0, 10, '🦐', 'Size tôm lớn, thịt dai ngọt, hàng tuyển đặc biệt.', 'active'),
(1, 'Chả cá phi Cà Mau (500g)', 'cha-ca-phi-ca-mau-500g', 90000, 0, 30, '🐠', 'Chả cá phi tươi, dai giòn, không hàn the.', 'active'),
(1, 'Khô cá lóc đồng (300g)', 'kho-ca-loc-dong-300g', 160000, 0, 18, '🐟', 'Cá lóc đồng phơi khô tự nhiên, thịt chắc thơm.', 'active'),
(2, 'Mật ong rừng U Minh Hạ (chai 500ml)', 'mat-ong-rung-u-minh-ha-chai-500ml', 260000, 230000, 50, '🍯', 'Mật ong nguyên chất khai thác từ rừng tràm U Minh Hạ, chứng nhận OCOP 4 sao.', 'active'),
(2, 'Gạo sạch Thới Bình (túi 5kg)', 'gao-sach-thoi-binh-tui-5kg', 130000, 0, 70, '🌾', 'Gạo trồng theo mô hình lúa - tôm hữu cơ, hạt dẻo thơm.', 'active'),
(2, 'Chuối khô Cà Mau (gói 400g)', 'chuoi-kho-ca-mau-goi-400g', 55000, 0, 45, '🍌', 'Chuối xiêm phơi nắng tự nhiên, ngọt dẻo, không đường hoá học.', 'active'),
(2, 'Muối tôm Cà Mau (hũ 200g)', 'muoi-tom-ca-mau-hu-200g', 35000, 0, 100, '🧂', 'Muối tôm chuẩn vị miền Tây, chấm trái cây, hải sản.', 'active'),
(2, 'Bánh phồng tôm Cà Mau (300g)', 'banh-phong-tom-ca-mau-300g', 48000, 0, 0, '🍘', 'Bánh phồng làm từ tôm đất tươi, giòn rụm đậm vị.', 'active'),
(2, 'Trà mật ong rừng U Minh (hộp 20 gói lọc)', 'tra-mat-ong-rung-u-minh-hop-20-goi', 65000, 0, 38, '🍵', 'Trà thảo mộc pha mật ong rừng, tiện lợi mỗi ngày.', 'active'),
(3, 'Dịch vụ chuyển phát nhanh EMS nội tỉnh', 'dich-vu-chuyen-phat-nhanh-ems-noi-tinh', 22000, 0, 999, '📮', 'Giao trong ngày nội thành/nội huyện Cà Mau.', 'active'),
(3, 'Bao bì carton gói hàng size M (bộ 10)', 'bao-bi-carton-goi-hang-size-m-bo-10', 45000, 0, 200, '📦', 'Thùng carton 3 lớp, đóng gói hàng hoá an toàn.', 'active'),
(3, 'Tem bưu chính lưu niệm Cà Mau (bộ 4 mẫu)', 'tem-buu-chinh-luu-niem-ca-mau-bo-4-mau', 40000, 0, 60, '📫', 'Bộ tem chủ đề Đất Mũi Cà Mau, dành cho người sưu tầm.', 'active'),
(4, 'Bình giữ nhiệt in logo Bưu điện (500ml)', 'binh-giu-nhiet-in-logo-buu-dien-500ml', 120000, 99000, 40, '🍶', 'Giữ nhiệt 8-10 giờ, chất liệu inox 304 an toàn.', 'active'),
(4, 'Nồi cơm điện mini 1L', 'noi-com-dien-mini-1l', 350000, 0, 12, '🍚', 'Phù hợp phòng trọ, văn phòng, công suất 400W.', 'active'),
(4, 'Quạt tích điện mini', 'quat-tich-dien-mini', 280000, 0, 0, '🌀', 'Pin sử dụng liên tục 6 giờ, 3 mức gió.', 'active'),
(5, 'Xà phòng tinh dầu tràm Cà Mau (90g)', 'xa-phong-tinh-dau-tram-ca-mau-90g', 18000, 0, 80, '🧼', 'Chiết xuất tràm gió U Minh, dịu nhẹ cho da.', 'active'),
(5, 'Dầu tràm nguyên chất U Minh (100ml)', 'dau-tram-nguyen-chat-u-minh-100ml', 75000, 65000, 33, '🌿', 'Dầu tràm nguyên chất, giữ ấm, kháng khuẩn tự nhiên.', 'active'),
(6, 'Combo quà Tết Cà Mau (tôm khô, mật ong, chuối khô)', 'combo-qua-tet-ca-mau', 450000, 399000, 20, '🎁', 'Set quà biếu ý nghĩa dịp lễ Tết, hộp quà sang trọng.', 'active'),
(6, 'Set quà biếu đặc sản Đất Mũi (hộp gỗ)', 'set-qua-bieu-dac-san-dat-mui-hop-go', 890000, 0, 8, '🎁', 'Bộ quà cao cấp gồm tôm khô, cua khô, mật ong, ba khía.', 'active');

-- =====================================================================
-- Dữ liệu mẫu — tin tức
-- =====================================================================
INSERT INTO posts (title, slug, tag, excerpt, content, icon, status, published_at) VALUES
('Bưu điện Cà Mau đưa 15 sản phẩm OCOP lên gian hàng số', 'buu-dien-ca-mau-dua-15-san-pham-ocop-len-gian-hang-so', 'Chuyển đổi số',
 'Chương trình hợp tác đưa nông sản, đặc sản địa phương lên sàn thương mại điện tử, mở rộng đầu ra cho bà con nông ngư dân.',
 'Trong khuôn khổ chương trình chuyển đổi số nông nghiệp, Bưu điện tỉnh Cà Mau đã phối hợp cùng các hợp tác xã đưa 15 sản phẩm OCOP tiêu biểu như tôm khô Rạch Gốc, mật ong rừng U Minh Hạ, ba khía muối… lên gian hàng số. Đây là bước đi quan trọng giúp sản phẩm địa phương tiếp cận người tiêu dùng cả nước thông qua nền tảng bán hàng trực tuyến và mạng lưới bưu cục rộng khắp.',
 '📰', 'published', '2026-09-12 08:00:00'),
('Chương trình tri ân khách hàng nhân 79 năm ngành Bưu điện', 'chuong-trinh-tri-an-khach-hang-nhan-79-nam-nganh-buu-dien', 'Khuyến mãi',
 'Nhiều phần quà và ưu đãi hấp dẫn dành cho khách hàng thân thiết trong tháng kỷ niệm ngành.',
 'Nhân dịp kỷ niệm 79 năm Ngày truyền thống ngành Bưu điện Việt Nam, Bưu điện tỉnh Cà Mau triển khai chương trình tri ân với nhiều ưu đãi: giảm giá đến 15% cho các đơn hàng đặc sản, miễn phí vận chuyển nội tỉnh và tặng quà cho 100 khách hàng may mắn.',
 '🎉', 'published', '2026-09-05 08:00:00'),
('Hướng dẫn gửi bưu phẩm nhanh dịp Tết Nguyên đán 2026', 'huong-dan-gui-buu-pham-nhanh-dip-tet-nguyen-dan-2026', 'Dịch vụ',
 'Thông tin lịch nhận gửi, thời gian phát hàng và lưu ý đóng gói trong cao điểm Tết.',
 'Để phục vụ tốt nhu cầu gửi quà Tết của người dân, Bưu điện Cà Mau thông báo lịch nhận gửi bưu phẩm cao điểm từ ngày 20 tháng Chạp, khuyến khích khách hàng gửi sớm để đảm bảo hàng hoá được phát trước Tết. Quý khách vui lòng đóng gói cẩn thận, ghi rõ thông tin người nhận.',
 '📮', 'published', '2026-08-28 08:00:00'),
('Tôm khô Rạch Gốc chính thức đạt chuẩn OCOP 4 sao', 'tom-kho-rach-goc-chinh-thuc-dat-chuan-ocop-4-sao', 'Đặc sản',
 'Sản phẩm tôm khô đặc trưng của huyện Ngọc Hiển được công nhận OCOP 4 sao cấp tỉnh.',
 'Hội đồng đánh giá, phân hạng sản phẩm OCOP tỉnh Cà Mau vừa công nhận tôm khô Rạch Gốc đạt chuẩn OCOP 4 sao. Đây là tin vui cho các hộ sản xuất tại huyện Ngọc Hiển, đồng thời khẳng định chất lượng sản phẩm được Bưu điện Cà Mau phân phối đến tay người tiêu dùng.',
 '🏅', 'published', '2026-08-20 08:00:00'),
('Bưu điện Cà Mau mở rộng điểm giao dịch tại Năm Căn, Ngọc Hiển', 'buu-dien-ca-mau-mo-rong-diem-giao-dich-tai-nam-can-ngoc-hien', 'Thông báo',
 'Hai điểm giao dịch mới giúp người dân vùng sâu, vùng xa tiếp cận dịch vụ bưu chính thuận tiện hơn.',
 'Nhằm phục vụ tốt hơn nhu cầu của người dân, Bưu điện Cà Mau chính thức đưa vào hoạt động hai điểm giao dịch mới tại huyện Năm Căn và Ngọc Hiển, cung cấp đầy đủ các dịch vụ bưu chính, chuyển phát, tài chính bưu điện và mua sắm đặc sản địa phương.',
 '📣', 'published', '2026-08-10 08:00:00');

SET FOREIGN_KEY_CHECKS = 1;
