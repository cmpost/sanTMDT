<?php
require_once __DIR__ . '/includes/init.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status='active'");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    $pageTitle = 'Không tìm thấy sản phẩm';
    require __DIR__ . '/includes/header.php';
    echo '<div class="wrap section"><div class="emptystate">Sản phẩm không tồn tại hoặc đã ngừng kinh doanh. <a href="index.php">Về trang chủ</a></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$cat = null;
if ($p['category_id']) {
    $cs = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $cs->execute([$p['category_id']]);
    $cat = $cs->fetch();
}

$related = [];
if ($p['category_id']) {
    $rs = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status='active' ORDER BY RAND() LIMIT 5");
    $rs->execute([$p['category_id'], $p['id']]);
    $related = $rs->fetchAll();
}

$pageTitle = $p['name'];
require __DIR__ . '/includes/header.php';

$oos = ((int)$p['stock']) <= 0;
$hasSale = (float)$p['sale_price'] > 0;
$img = image_url($p['image']);
?>
<div class="wrap">
  <div class="pd-wrap">
    <div class="pd-thumb"><?= $img ? '<img src="'.e($img).'" alt="'.e($p['name']).'">' : e($p['icon']) ?></div>
    <div class="pd-info">
      <?php if ($cat): ?><div class="tag" style="color:var(--mangrove);font-weight:700;font-size:12.5px;margin-bottom:8px;"><?= e($cat['icon']) ?> <?= e($cat['name']) ?></div><?php endif; ?>
      <h1><?= e($p['name']) ?></h1>
      <div class="pd-prices">
        <?php if ($hasSale): ?>
          <span class="price"><?= money($p['sale_price']) ?></span><span class="old"><?= money($p['price']) ?></span>
        <?php else: ?>
          <span class="price" style="color:var(--navy);"><?= money($p['price']) ?></span>
        <?php endif; ?>
      </div>
      <div class="pd-stock">
        <?php if ($oos): ?><span class="pill bad">Hết hàng</span>
        <?php else: ?><span class="pill ok">Còn <?= (int)$p['stock'] ?> sản phẩm</span><?php endif; ?>
      </div>
      <p class="pd-desc"><?= nl2br(e($p['description'])) ?></p>
      <?php if (!$oos): ?>
        <form class="qtyform" method="post" action="cart.php">
          <?= csrf_field() ?>
          <input type="hidden" name="cart_action" value="add">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <input type="number" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>">
          <button type="submit" class="btn btn-gold">Thêm vào giỏ hàng</button>
        </form>
      <?php else: ?>
        <button class="btn btn-line" disabled>Hết hàng</button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($related): ?>
    <section class="section">
      <div class="sec-head"><h2><span class="dot"></span>Sản phẩm liên quan</h2></div>
      <div class="grid">
        <?php foreach ($related as $r) echo render_product_card($r); ?>
      </div>
    </section>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
