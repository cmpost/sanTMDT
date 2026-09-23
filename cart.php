<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['cart_action'] ?? '';

    if ($action === 'add') {
        $id = (int)($_POST['id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ? AND status='active'");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();
        if ($prod) {
            $current = $_SESSION['cart'][$id] ?? 0;
            $newQty = min((int)$prod['stock'], $current + $qty);
            if ($newQty > 0) {
                $_SESSION['cart'][$id] = $newQty;
                flash_set('ok', 'Đã thêm sản phẩm vào giỏ hàng.');
            } else {
                flash_set('error', 'Sản phẩm này hiện đã hết hàng.');
            }
        }
        redirect('cart.php');
    }

    if ($action === 'update_all') {
        $qtys = $_POST['qty'] ?? [];
        foreach ($qtys as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;
            if (!isset($_SESSION['cart'][$id])) continue;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
                continue;
            }
            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $stock = (int)$stmt->fetchColumn();
            $_SESSION['cart'][$id] = min($qty, max(0, $stock));
        }
        flash_set('ok', 'Đã cập nhật giỏ hàng.');
        redirect('cart.php');
    }

    if ($action === 'remove') {
        $id = (int)($_POST['id'] ?? 0);
        unset($_SESSION['cart'][$id]);
        flash_set('ok', 'Đã xoá sản phẩm khỏi giỏ hàng.');
        redirect('cart.php');
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        redirect('cart.php');
    }

    redirect('cart.php');
}

$pageTitle = 'Giỏ hàng';
require __DIR__ . '/includes/header.php';

$lines = [];
$total = 0;
if ($_SESSION['cart']) {
    $ids = array_keys($_SESSION['cart']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
    $byId = [];
    foreach ($products as $pr) $byId[$pr['id']] = $pr;

    foreach ($_SESSION['cart'] as $id => $qty) {
        if (!isset($byId[$id])) continue;
        $pr = $byId[$id];
        $unit = (float)$pr['sale_price'] > 0 ? (float)$pr['sale_price'] : (float)$pr['price'];
        $lineTotal = $unit * $qty;
        $total += $lineTotal;
        $lines[] = ['product' => $pr, 'qty' => $qty, 'unit' => $unit, 'lineTotal' => $lineTotal];
    }
}
?>
<div class="wrap">
  <div class="sec-head" style="padding-top:20px;"><h1><span class="dot"></span>Giỏ hàng của bạn</h1></div>

  <?php if (!$lines): ?>
    <div class="empty-note">Giỏ hàng của bạn đang trống.<br><a class="btn btn-navy" style="margin-top:14px;" href="index.php">Tiếp tục mua sắm</a></div>
  <?php else: ?>
    <form method="post" action="cart.php">
      <?= csrf_field() ?>
      <input type="hidden" name="cart_action" value="update_all">
      <div class="cart-wrap">
        <div class="tbl-wrap">
          <table class="cart-table">
            <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($lines as $l): $pr = $l['product']; $img = image_url($pr['image']); ?>
                <tr>
                  <td>
                    <div class="cart-prod">
                      <div class="th"><?= $img ? '<img src="'.e($img).'">' : e($pr['icon']) ?></div>
                      <a href="product.php?id=<?= (int)$pr['id'] ?>"><?= e($pr['name']) ?></a>
                    </div>
                  </td>
                  <td class="tnum"><?= money($l['unit']) ?></td>
                  <td>
                    <div class="qtybox">
                      <input type="number" name="qty[<?= (int)$pr['id'] ?>]" value="<?= (int)$l['qty'] ?>" min="0" max="<?= (int)$pr['stock'] ?>">
                    </div>
                  </td>
                  <td class="tnum"><?= money($l['lineTotal']) ?></td>
                  <td>
                    <button type="submit" form="removeForm-<?= (int)$pr['id'] ?>" class="btn btn-danger btn-sm">Xoá</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="summary-box">
          <h3>Tóm tắt đơn hàng</h3>
          <div class="rowbetween"><span>Tạm tính</span><b class="tnum"><?= money($total) ?></b></div>
          <button type="submit" class="btn btn-line" style="width:100%;justify-content:center;margin-bottom:10px;">Cập nhật giỏ hàng</button>
          <a class="btn btn-gold" style="width:100%;justify-content:center;" href="checkout.php">Tiến hành đặt hàng</a>
        </div>
      </div>
    </form>
    <?php foreach ($lines as $l): $pr = $l['product']; ?>
      <form id="removeForm-<?= (int)$pr['id'] ?>" method="post" action="cart.php" style="display:none;">
        <?= csrf_field() ?>
        <input type="hidden" name="cart_action" value="remove">
        <input type="hidden" name="id" value="<?= (int)$pr['id'] ?>">
      </form>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
