<?php
require_once __DIR__ . '/includes/init.php';

$q = trim($_GET['q'] ?? '');
$items = [];
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status='active' AND name LIKE ? ORDER BY created_at DESC LIMIT 60");
    $stmt->execute(['%' . $q . '%']);
    $items = $stmt->fetchAll();
}

$pageTitle = 'Kết quả tìm kiếm';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap section">
  <div class="sec-head">
    <h1><span class="dot"></span>Kết quả tìm kiếm cho "<?= e($q) ?>"</h1>
  </div>
  <?php if ($q === ''): ?>
    <div class="emptystate">Vui lòng nhập từ khoá cần tìm.</div>
  <?php elseif (!$items): ?>
    <div class="emptystate">Không tìm thấy sản phẩm phù hợp với "<?= e($q) ?>".</div>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($items as $p) echo render_product_card($p); ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
