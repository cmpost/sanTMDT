<?php
/** Xác thực quản trị viên — dùng session PHP, không lưu thông tin nhạy cảm ở client. */

function is_admin() {
    return !empty($_SESSION['admin_id']);
}

/** Gọi ở đầu mọi trang admin (trừ login.php, setup.php) để bắt buộc đăng nhập. */
function require_admin() {
    if (!is_admin()) {
        redirect('login.php');
    }
}

function admin_count(PDO $pdo) {
    return (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
}

/**
 * Giới hạn số lần đăng nhập sai trong 1 khoảng thời gian để giảm rủi ro dò mật khẩu.
 * Đây là biện pháp cơ bản dựa trên session; với site có lượng truy cập lớn nên
 * cân nhắc thêm giải pháp chống brute-force ở tầng máy chủ/CDN.
 */
function login_throttle_check() {
    $tries = $_SESSION['login_tries'] ?? 0;
    $last = $_SESSION['login_last_try'] ?? 0;
    if ($tries >= 5 && (time() - $last) < 300) {
        flash_set('error', 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau 5 phút.');
        return false;
    }
    return true;
}

function login_throttle_fail() {
    $_SESSION['login_tries'] = ($_SESSION['login_tries'] ?? 0) + 1;
    $_SESSION['login_last_try'] = time();
}

function login_throttle_reset() {
    unset($_SESSION['login_tries'], $_SESSION['login_last_try']);
}
