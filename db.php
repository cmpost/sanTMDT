<?php
/** Kết nối cơ sở dữ liệu dùng chung (PDO). Không truy cập trực tiếp file này. */

$pdo = null;

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    die(
        '<div style="font-family:sans-serif;max-width:640px;margin:60px auto;padding:24px;' .
        'border:1px solid #e4b8ac;background:#fbeae5;border-radius:10px;color:#7a2a1a;">' .
        '<h2 style="margin-top:0;">Không thể kết nối cơ sở dữ liệu</h2>' .
        '<p>Vui lòng kiểm tra lại thông tin DB_HOST / DB_NAME / DB_USER / DB_PASS trong ' .
        '<code>config.php</code> và đảm bảo cơ sở dữ liệu đã được tạo &amp; import <code>schema.sql</code>.</p>' .
        '<p style="font-size:13px;color:#946200;">Chi tiết kỹ thuật: ' . htmlspecialchars($ex->getMessage()) . '</p>' .
        '</div>'
    );
}
