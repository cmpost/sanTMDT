<?php
require_once __DIR__ . '/../includes/init.php';

// Chỉ cho phép tạo tài khoản đầu tiên; nếu đã có admin thì khoá trang này lại.
if (admin_count($pdo) > 0) {
    flash_set('error', 'Hệ thống đã có tài khoản quản trị. Vui lòng đăng nhập.');
    redirect('login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9_.]{3,50}$/', $username)) {
        $errors[] = 'Tên đăng nhập chỉ gồm chữ, số, dấu chấm/gạch dưới, từ 3-50 ký tự.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Mật khẩu cần tối thiểu 8 ký tự.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Mật khẩu nhập lại không khớp.';
    }

    if (!$errors) {
        // Kiểm tra lại lần nữa ngay trước khi ghi, tránh trường hợp có 2 người
        // cùng mở trang setup.php một lúc.
        if (admin_count($pdo) > 0) {
            redirect('login.php');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash, full_name) VALUES (?, ?, ?)');
        $stmt->execute([$username, $hash, $fullName ?: $username]);
        flash_set('ok', 'Đã tạo tài khoản quản trị thành công. Vui lòng đăng nhập.');
        redirect('login.php');
    }
}
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Thiết lập tài khoản quản trị - Bưu điện Cà Mau</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@500;600;700;800&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div id="adminGate">
  <div class="gatecard">
    <div class="mark"><svg viewBox="0 0 24 24" fill="none"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7z" stroke="#0A1E42" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
    <h2>Thiết lập tài khoản quản trị</h2>
    <p>Đây là lần đầu tiên hệ thống được cài đặt. Hãy tạo tài khoản quản trị viên đầu tiên cho Bưu điện Cà Mau.</p>
    <?php if ($errors): foreach ($errors as $er): ?>
      <div class="alert alert-error" style="margin:0 0 12px;"><?= e($er) ?></div>
    <?php endforeach; endif; ?>
    <form method="post" action="setup.php">
      <?= csrf_field() ?>
      <div class="field"><label>Tên đăng nhập</label><input name="username" required value="<?= e($_POST['username'] ?? '') ?>" autofocus></div>
      <div class="field"><label>Họ tên hiển thị</label><input name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>"></div>
      <div class="field"><label>Mật khẩu (tối thiểu 8 ký tự)</label><input type="password" name="password" required></div>
      <div class="field"><label>Nhập lại mật khẩu</label><input type="password" name="confirm" required></div>
      <button type="submit" class="btn btn-navy" style="width:100%;justify-content:center;">Tạo tài khoản</button>
    </form>
    <div class="gate-hint">Sau khi tạo, trang này sẽ tự khoá lại — chỉ dùng được một lần duy nhất cho tài khoản quản trị đầu tiên.</div>
  </div>
</div>
</body>
</html>
