<?php
require_once __DIR__ . '/../includes/init.php';

if (is_admin()) {
    redirect('index.php');
}

$needsSetup = admin_count($pdo) === 0;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$needsSetup) {
    csrf_verify();

    if (!login_throttle_check()) {
        $error = 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau 5 phút.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            login_throttle_reset();
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'] ?: $user['username'];
            redirect('index.php');
        } else {
            login_throttle_fail();
            $error = 'Sai tên đăng nhập hoặc mật khẩu.';
        }
    }
}
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập quản trị - Bưu điện Cà Mau</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@500;600;700;800&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div id="adminGate">
  <div class="gatecard">
    <div class="mark"><svg viewBox="0 0 24 24" fill="none"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7z" stroke="#0A1E42" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
    <h2>Đăng nhập quản trị</h2>
    <p>Bưu điện Cà Mau — Cổng quản lý bán hàng &amp; truyền thông</p>

    <?php flash_render(); ?>
    <?php if ($error): ?><div class="alert alert-error" style="margin:0 0 12px;"><?= e($error) ?></div><?php endif; ?>

    <?php if ($needsSetup): ?>
      <div class="alert alert-ok" style="margin:0 0 12px;">Chưa có tài khoản quản trị nào. Vui lòng thiết lập tài khoản đầu tiên.</div>
      <a class="btn btn-navy" style="width:100%;justify-content:center;" href="setup.php">Thiết lập tài khoản quản trị</a>
    <?php else: ?>
      <form method="post" action="login.php">
        <?= csrf_field() ?>
        <div class="field"><label>Tài khoản</label><input name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>"></div>
        <div class="field"><label>Mật khẩu</label><input type="password" name="password" required></div>
        <button type="submit" class="btn btn-navy" style="width:100%;justify-content:center;">Đăng nhập</button>
      </form>
    <?php endif; ?>
    <a class="gate-link" href="../index.php">← Về trang bán hàng</a>
  </div>
</div>
</body>
</html>
