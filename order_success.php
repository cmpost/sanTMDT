<?php
require_once __DIR__ . '/includes/init.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('index.php');
}

$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$pageTitle = 'Đặt hàng thành công';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap section" style="max-width:640px;margin-inline:auto;">
  <div class="summary-box" style="text-align:center;">
    <div style="font-size:48px;">✅</div>
    <h1 style="font-size:22px;margin:10px 0 6px;">Đặt hàng thành công!</h1>
    <p style="color:var(--ink-dim);margin:0 0 16px;">Cảm ơn bạn đã mua hàng tại Bưu điện Cà Mau. Chúng tôi sẽ liên hệ xác nhận trong thời gian sớm nhất.</p>
    <div class="rowbetween"><span>Mã đơn hàng</span><b class="tnum"><?= e(order_code($order['id'])) ?></b></div>
    <div class="rowbetween"><span>Người nhận</span><b><?= e($order['customer_name']) ?></b></div>
    <div class="rowbetween"><span>Địa chỉ</span><b style="text-align:right;max-width:60%;"><?= e($order['customer_address']) ?></b></div>
    <div class="rowbetween"><span>Tổng tiền (COD)</span><b class="tnum" style="color:var(--danger);"><?= money($order['total']) ?></b></div>
  </div>

  <div class="tbl-wrap" style="margin-top:20px;">
    <table class="cart-table">
      <thead><tr><th>Sản phẩm</th><th>SL</th><th>Thành tiền</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= e($it['product_name']) ?></td>
            <td><?= (int)$it['qty'] ?></td>
            <td class="tnum"><?= money($it['price'] * $it['qty']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="text-align:center;margin-top:20px;">
    <a class="btn btn-navy" href="index.php">Tiếp tục mua sắm</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
