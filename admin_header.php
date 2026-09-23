<?php
/**
 * Khung giao diện dùng chung cho các trang quản trị (đã đăng nhập).
 * Trang gọi cần: require_admin() đã qua, và set $activeNav trước khi include.
 * $pageTitle (tuỳ chọn) đổi tiêu đề tab trình duyệt.
 */
$__navItems = [
    ['dashboard',  'Tổng quan',   '<path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6V11h-6v9Zm0-16v5h6V4h-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>', 'index.php'],
    ['products',   'Sản phẩm',    '<path d="M3 7l9-4 9 4-9 4-9-4Zm0 0v10l9 4m0-14v14m9-14v10l-9 4" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>', 'products.php'],
    ['categories', 'Danh mục',    '<rect x="4" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="4" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="4" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="13" y="13" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.6"/>', 'categories.php'],
    ['posts',      'Tin tức',     '<path d="M4 5h13a2 2 0 0 1 2 2v11a1 1 0 0 1-1.6.8L15 17H6a2 2 0 0 1-2-2V5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M7 9h9M7 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>', 'posts.php'],
    ['orders',     'Đơn hàng',    '<path d="M3 4h2l2.4 12.1a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 8H6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="21" r="1.4" fill="currentColor"/><circle cx="18" cy="21" r="1.4" fill="currentColor"/>', 'orders.php'],
    ['settings',   'Cài đặt',     '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.5-2-3.4-2.3.9a7 7 0 0 0-2-1.2L14 3h-4l-.5 2.6a7 7 0 0 0-2 1.2l-2.3-.9-2 3.4 2 1.5A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.5 2 3.4 2.3-.9c.6.5 1.3.9 2 1.2L10 21h4l.5-2.6c.7-.3 1.4-.7 2-1.2l2.3.9 2-3.4-2-1.5c.1-.4.2-.8.2-1.2Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>', 'settings.php'],
];
$__title = (isset($pageTitle) && $pageTitle ? $pageTitle . ' - ' : '') . 'Quản trị Bưu điện Cà Mau';
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($__title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@500;600;700;800&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="abar">
  <div class="abrand"><div class="mk"><svg viewBox="0 0 24 24" fill="none" style="width:16px;height:16px;"><path d="M3 7l9-4 9 4v10l-9 4-9-4V7z" stroke="#0A1E42" stroke-width="1.6" stroke-linejoin="round"/></svg></div>QUẢN TRỊ BƯU ĐIỆN CÀ MAU</div>
  <div style="margin-left:auto; display:flex; gap:10px; align-items:center;">
    <span style="font-size:12.5px;opacity:.85;">Xin chào, <?= e($_SESSION['admin_name'] ?? 'Quản trị viên') ?></span>
    <a class="btn btn-ghost btn-sm" href="../index.php">Xem trang bán hàng</a>
    <a class="btn btn-ghost btn-sm" href="logout.php">Đăng xuất</a>
  </div>
</div>
<div class="ashell">
  <nav class="asidenav">
    <?php foreach ($__navItems as [$key, $label, $icon, $href]): ?>
      <a class="anavitem <?= ($activeNav ?? '')===$key ? 'active':'' ?>" href="<?= e($href) ?>">
        <svg viewBox="0 0 24 24" fill="none"><?= $icon ?></svg><span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <main class="amain">
    <?php flash_render(); ?>
