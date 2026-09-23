<?php
/** Các hàm dùng chung cho toàn bộ website. */

/** Escape chuỗi an toàn khi in ra HTML. */
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Định dạng số tiền theo VND, giá 0 hiển thị "Liên hệ". */
function money($n) {
    $n = (float)$n;
    if ($n <= 0) return 'Liên hệ';
    return number_format($n, 0, ',', '.') . 'đ';
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/** Chuyển tiêu đề tiếng Việt có dấu thành slug URL không dấu. */
function slugify($text) {
    $map = [
        'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a',
        'ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o',
        'ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
        'đ'=>'d',
    ];
    $text = mb_strtolower(trim((string)$text), 'UTF-8');
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    $text = trim($text, '-');
    if ($text === '') {
        $text = 'muc-' . substr(bin2hex(random_bytes(4)), 0, 6);
    }
    return $text;
}

/** Đảm bảo slug là duy nhất trong bảng, tự thêm hậu tố -2, -3... nếu trùng. */
function unique_slug(PDO $pdo, $table, $base, $ignoreId = null) {
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?" . ($ignoreId ? ' AND id != ?' : '');
        $stmt = $pdo->prepare($sql);
        $params = [$slug];
        if ($ignoreId) $params[] = $ignoreId;
        $stmt->execute($params);
        if (!$stmt->fetch()) return $slug;
        $slug = $base . '-' . $i;
        $i++;
    }
}

/** Sinh / lấy CSRF token cho phiên hiện tại. */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** In sẵn input ẩn CSRF để nhúng vào <form>. */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Kiểm tra CSRF token của request POST, dừng luôn nếu không hợp lệ. */
function csrf_verify() {
    $token = $_POST['csrf_token'] ?? '';
    if ($token === '' || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(400);
        die('Phiên làm việc đã hết hạn hoặc không hợp lệ. Vui lòng tải lại trang và thử lại.');
    }
}

/** Lưu một thông báo flash để hiển thị sau khi chuyển trang (PRG pattern). */
function flash_set($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

/** Lấy và xoá thông báo flash hiện có (chỉ hiển thị được 1 lần). */
function flash_get() {
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

/** In khối thông báo flash (nếu có) dưới dạng HTML. */
function flash_render() {
    $f = flash_get();
    if (!$f) return;
    $cls = $f['type'] === 'error' ? 'alert alert-error' : 'alert alert-ok';
    echo '<div class="' . $cls . '">' . e($f['msg']) . '</div>';
}

/** Sinh mã đơn hàng hiển thị cho khách, ví dụ CM000123. */
function order_code($id) {
    return 'CM' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
}

/**
 * Xử lý upload ảnh cho field $_FILES[$fieldName], lưu vào UPLOAD_DIR/$subdir.
 * Trả về: đường dẫn tương đối (vd "products/abc123.jpg") nếu thành công,
 * null nếu người dùng không chọn ảnh, false nếu có lỗi (đã set flash message).
 */
function upload_image($fieldName, $subdir) {
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        flash_set('error', 'Có lỗi khi tải ảnh lên, vui lòng thử lại.');
        return false;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        flash_set('error', 'Ảnh vượt quá dung lượng cho phép (tối đa 3MB).');
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) {
        flash_set('error', 'Chỉ chấp nhận ảnh định dạng JPG, PNG, WEBP hoặc GIF.');
        return false;
    }
    $ext = $allowed[$mime];
    $dir = rtrim(UPLOAD_DIR, '/') . '/' . $subdir;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        flash_set('error', 'Không thể lưu ảnh lên máy chủ. Vui lòng kiểm tra quyền ghi thư mục uploads/.');
        return false;
    }
    return $subdir . '/' . $filename;
}

/** Xoá file ảnh (nếu tồn tại) khỏi thư mục uploads. Không báo lỗi nếu không có. */
function delete_image($relPath) {
    if (!$relPath) return;
    $full = rtrim(UPLOAD_DIR, '/') . '/' . ltrim($relPath, '/');
    if (is_file($full)) {
        @unlink($full);
    }
}

/** Trả về URL hiển thị của ảnh đã lưu (dùng ở các trang công khai tại thư mục gốc), hoặc null nếu không có ảnh. */
function image_url($relPath) {
    return $relPath ? UPLOAD_URL . '/' . $relPath : null;
}

/** Giống image_url() nhưng dùng cho các trang trong thư mục admin/ (sâu hơn 1 cấp so với gốc). */
function admin_image_url($relPath) {
    return $relPath ? '../' . UPLOAD_URL . '/' . $relPath : null;
}

/** Định dạng ngày kiểu Việt Nam dd/mm/yyyy từ chuỗi ngày MySQL. */
function fmt_date($mysqlDate) {
    if (!$mysqlDate) return '';
    $ts = strtotime($mysqlDate);
    return date('d/m/Y', $ts);
}

function fmt_datetime($mysqlDate) {
    if (!$mysqlDate) return '';
    $ts = strtotime($mysqlDate);
    return date('d/m/Y H:i', $ts);
}

/** Trả về HTML của một thẻ sản phẩm dùng ở trang chủ, danh mục, tìm kiếm. */
function render_product_card(array $p) {
    $oos = ((int)$p['stock']) <= 0;
    $hasSale = (float)$p['sale_price'] > 0;
    $img = image_url($p['image']);
    $thumb = $img ? '<img src="' . e($img) . '" alt="' . e($p['name']) . '">' : e($p['icon']);
    $priceHtml = $hasSale
        ? '<span class="price">' . money($p['sale_price']) . '</span><span class="old">' . money($p['price']) . '</span>'
        : '<span class="price solo">' . money($p['price']) . '</span>';
    ob_start(); ?>
    <div class="card">
      <a class="thumb" href="product.php?id=<?= (int)$p['id'] ?>">
        <?= $thumb ?>
        <?php if ($hasSale): ?><span class="badge">Giảm giá</span><?php endif; ?>
        <?php if ($oos): ?><div class="oos">Hết hàng</div><?php endif; ?>
      </a>
      <div class="body">
        <a class="name" href="product.php?id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a>
        <div class="prices"><?= $priceHtml ?></div>
        <?php if ($oos): ?>
          <button class="buy" disabled>Hết hàng</button>
        <?php else: ?>
          <form method="post" action="cart.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="cart_action" value="add">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="qty" value="1">
            <button type="submit" class="buy">Chọn mua</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php return ob_get_clean();
}

/** Nhãn hiển thị + class CSS cho trạng thái đơn hàng. */
function order_status_label($status) {
    $map = [
        'pending'   => ['Chờ xác nhận', 'warn'],
        'shipping'  => ['Đang giao', 'ok'],
        'completed' => ['Hoàn thành', 'ok'],
        'cancelled' => ['Đã huỷ', 'bad'],
    ];
    return $map[$status] ?? ['—', 'muted'];
}
