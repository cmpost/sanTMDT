<?php
/**
 * Header dùng chung cho các trang công khai (bán hàng).
 * Trang gọi include file này cần đã require includes/init.php trước đó,
 * và có thể set biến $pageTitle trước khi include để đổi tiêu đề tab.
 */
$__settings = $pdo->query('SELECT * FROM settings WHERE id = 1')->fetch() ?: [];
$__cats = $pdo->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();
$__cartCount = cart_count();
$__title = isset($pageTitle) && $pageTitle ? $pageTitle . ' - ' . ($__settings['site_name'] ?? 'Bưu điện Cà Mau') : ($__settings['site_name'] ?? 'Bưu điện tỉnh Cà Mau');
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($__title) ?></title>
<meta name="description" content="Website bán hàng và truyền thông của Bưu điện tỉnh Cà Mau — đặc sản OCOP, bưu phẩm, tin tức địa phương.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@500;600;700;800&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="utilitybar">
  <div class="wrap">
    <div><?= e($__settings['banner_text'] ?? '') ?></div>
    <div><a href="admin/login.php">Trang quản trị</a></div>
  </div>
</div>

<?php flash_render(); ?>

<header class="site">
  <div class="wrap headrow">
    <a class="brand" href="index.php">
      <div class="mark">
        <svg viewBox="0 0 24 24" fill="none"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7z" stroke="#0A1E42" stroke-width="1.6" stroke-linejoin="round"/><path d="M3 7l9 4 9-4M12 11v10" stroke="#0A1E42" stroke-width="1.6" stroke-linejoin="round"/></svg>
      </div>
      <div class="txt">
        <b>BƯU ĐIỆN CÀ MAU</b>
        <span>Mua sắm trực tuyến &amp; dịch vụ bưu chính</span>
      </div>
    </a>
    <form class="searchbox" action="search.php" method="get">
      <input type="text" name="q" placeholder="Tìm tôm khô, mật ong, cua Năm Căn…" value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit" aria-label="Tìm kiếm">
        <svg viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#0A1E42" stroke-width="2"/><path d="M20 20l-3.5-3.5" stroke="#0A1E42" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
    </form>
    <div class="headicons">
      <div class="hicon">
        <svg viewBox="0 0 24 24" fill="none"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.4 2.1L8 9.9a16 16 0 0 0 6 6l1.4-1.4a2 2 0 0 1 2.1-.4c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.8 2.2Z" stroke="#fff" stroke-width="1.7"/></svg>
        <div><b>Hotline</b><small><?= e($__settings['hotline'] ?? '') ?></small></div>
      </div>
      <a class="hicon" href="cart.php" aria-label="Giỏ hàng">
        <svg viewBox="0 0 24 24" fill="none"><path d="M3 4h2l2.4 12.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 8H6" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="21" r="1.4" fill="#fff"/><circle cx="18" cy="21" r="1.4" fill="#fff"/></svg>
        <span class="num tnum"><?= (int)$__cartCount ?></span>
      </a>
    </div>
  </div>
  <nav class="catnav">
    <div class="wrap">
      <a class="catchip <?= empty($_GET['cat']) && basename($_SERVER['SCRIPT_NAME'])==='index.php' ? 'active':'' ?>" href="index.php">Tất cả</a>
      <?php foreach ($__cats as $c): ?>
        <a class="catchip <?= (($_GET['slug'] ?? '')===$c['slug']) ? 'active':'' ?>" href="category.php?slug=<?= urlencode($c['slug']) ?>"><?= e($c['icon']) ?> <?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </nav>
</header>
