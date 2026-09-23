<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.name LIKE ? ORDER BY p.created_at DESC");
    $stmt->execute(['%' . $q . '%']);
} else {
    $stmt = $pdo->query('SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC');
}
$products = $stmt->fetchAll();
$totalAll = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

$pageTitle = 'Sản phẩm';
$activeNav = 'products';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="apage-head">
  <div><h1>Sản phẩm</h1><p><?= $totalAll ?> sản phẩm trong cửa hàng</p></div>
  <div style="display:flex;gap:10px;">
    <form class="searchmini" method="get" action="products.php">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/></svg>
      <input type="text" name="q" placeholder="Tìm sản phẩm..." value="<?= e($q) ?>">
    </form>
    <a class="btn btn-navy" href="product_edit.php">+ Thêm sản phẩm</a>
  </div>
</div>

<div class="panel">
  <div class="tbl-wrap">
    <table>
      <thead><tr><th></th><th>Tên sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th></th></tr></thead>
      <tbody>
        <?php if (!$products): ?><tr><td colspan="6" class="emptystate">Chưa có sản phẩm nào</td></tr><?php endif; ?>
        <?php foreach ($products as $p): $img = admin_image_url($p['image']); ?>
          <tr>
            <td><div class="rowimg"><?= $img ? '<img src="'.e($img).'">' : e($p['icon']) ?></div></td>
            <td style="max-width:280px;"><?= e($p['name']) ?></td>
            <td><?= e($p['cat_name'] ?? '—') ?></td>
            <td class="tnum">
              <?php if ($p['sale_price'] > 0): ?>
                <?= money($p['sale_price']) ?> <span style="color:var(--ink-dim);text-decoration:line-through;font-size:11px;"><?= money($p['price']) ?></span>
              <?php else: ?>
                <?= money($p['price']) ?>
              <?php endif; ?>
            </td>
            <td><?= $p['stock'] <= 0 ? '<span class="pill bad">Hết hàng</span>' : '<span class="tnum">'.(int)$p['stock'].'</span>' ?></td>
            <td>
              <div class="rowacts">
                <a class="btn btn-line btn-sm" href="product_edit.php?id=<?= (int)$p['id'] ?>">Sửa</a>
                <form method="post" action="product_delete.php" onsubmit="return confirm('Xoá sản phẩm này? Hành động không thể hoàn tác.');" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Xoá</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
