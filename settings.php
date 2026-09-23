<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $stmt = $pdo->prepare('UPDATE settings SET site_name=?, hotline=?, address=?, email=?, fanpage=?, banner_text=? WHERE id = 1');
    $stmt->execute([
        trim($_POST['site_name'] ?? ''),
        trim($_POST['hotline'] ?? ''),
        trim($_POST['address'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['fanpage'] ?? ''),
        trim($_POST['banner_text'] ?? ''),
    ]);
    flash_set('ok', 'Đã lưu cài đặt cửa hàng.');
    redirect('settings.php');
}

$s = $pdo->query('SELECT * FROM settings WHERE id = 1')->fetch() ?: [];

$pageTitle = 'Cài đặt';
$activeNav = 'settings';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="apage-head"><div><h1>Cài đặt cửa hàng</h1><p>Thông tin hiển thị trên trang bán hàng</p></div></div>

<div class="panel" style="max-width:560px;">
  <form method="post" action="settings.php">
    <?= csrf_field() ?>
    <div class="field"><label>Tên website</label><input name="site_name" value="<?= e($s['site_name'] ?? '') ?>"></div>
    <div class="field"><label>Hotline</label><input name="hotline" value="<?= e($s['hotline'] ?? '') ?>"></div>
    <div class="field"><label>Địa chỉ</label><input name="address" value="<?= e($s['address'] ?? '') ?>"></div>
    <div class="field"><label>Email</label><input name="email" value="<?= e($s['email'] ?? '') ?>"></div>
    <div class="field"><label>Fanpage</label><input name="fanpage" value="<?= e($s['fanpage'] ?? '') ?>"></div>
    <div class="field"><label>Thông báo đầu trang</label><input name="banner_text" value="<?= e($s['banner_text'] ?? '') ?>"></div>
    <button type="submit" class="btn btn-navy">Lưu cài đặt</button>
  </form>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
