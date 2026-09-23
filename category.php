<?php
require_once __DIR__ . '/includes/init.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
$stmt->execute([$slug]);
$cat = $stmt->fetch();

if (!$cat) {
    $pageTitle = 'Không tìm thấy danh mục';
    require __DIR__ . '/includes/header.php';
    echo '<div class="wrap section"><div class="emptystate">Danh mục không tồn tại. <a href="index.php">Về trang chủ</a></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$perPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status='active'");
$countStmt->execute([$cat['id']]);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status='active' ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute([$cat['id']]);
$items = $stmt->fetchAll();

$pageTitle = $cat['name'];
require __DIR__ . '/includes/header.php';
?>
<div class="wrap section">
  <div class="sec-head">
    <h1><span class="dot"></span><?= e($cat['icon']) ?> <?= e($cat['name']) ?></h1>
    <span style="color:var(--ink-dim);font-size:13px;"><?= $total ?> sản phẩm</span>
  </div>
  <?php if (!$items): ?>
    <div class="emptystate">Danh mục này chưa có sản phẩm nào.</div>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($items as $p) echo render_product_card($p); ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <div style="display:flex;gap:8px;justify-content:center;margin-top:24px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a class="btn <?= $i===$page ? 'btn-navy' : 'btn-line' ?> btn-sm" href="category.php?slug=<?= urlencode($slug) ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
