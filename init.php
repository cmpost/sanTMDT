<?php
/**
 * File khởi tạo chung — MỌI trang (public lẫn admin) phải require file này
 * đầu tiên, trước khi in bất kỳ nội dung nào ra trình duyệt.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

/** Giỏ hàng lưu trong session dạng [product_id => qty]. */
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function cart_count() {
    return array_sum($_SESSION['cart']);
}
