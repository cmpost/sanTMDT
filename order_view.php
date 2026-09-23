<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) redirect('orders.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['pending', 'shipping', 'completed', 'cancelled'], true)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$newStatus, $id]);
        flash_set('ok', 'Đã cập nhật trạng thái đơn hàng.');
    }
    redirect('order_view.php?id=' . $id);
}

$itemsStmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

$pageTitle = 'Đơn hàng ' . order_code($order['id']);
$activeNav = 'orders';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="apage-head">
  <div><h1>Đơn hàng #<?= e(order_code($order['id'])) ?></h1><p>Đặt lúc <?= fmt_datetime($order['created_at']) ?></p></div>
  <a class="btn btn-line btn-sm" href="orders.php">← Quay lại danh sách</a>
</div>

<div class="cols2">
  <div class="panel">
    <div class="panel-head"><h3>Sản phẩm đã đặt</h3></div>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= e($it['product_name']) ?></td>
              <td class="tnum"><?= money($it['price']) ?></td>
              <td><?= (int)$it['qty'] ?></td>
              <td class="tnum"><?= money($it['price'] * $it['qty']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="rowbetween" style="margin-top:14px;font-size:15px;"><span>Tổng cộng</span><b class="tnum"><?= money($order['total']) ?></b></div>
    <?php if ($order['note']): ?>
      <div style="margin-top:10px;font-size:13px;color:var(--ink-dim);">Ghi chú: <?= e($order['note']) ?></div>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head"><h3>Thông tin khách hàng</h3></div>
    <div class="field"><label>Họ tên</label><div><?= e($order['customer_name']) ?></div></div>
    <div class="field"><label>Số điện thoại</label><div><?= e($order['customer_phone']) ?></div></div>
    <div class="field"><label>Địa chỉ giao hàng</label><div><?= e($order['customer_address']) ?></div></div>

    <form method="post" action="order_view.php?id=<?= (int)$order['id'] ?>">
      <?= csrf_field() ?>
      <div class="field">
        <label>Trạng thái đơn hàng</label>
        <select name="status">
          <option value="pending" <?= $order['status']==='pending'?'selected':'' ?>>Chờ xác nhận</option>
          <option value="shipping" <?= $order['status']==='shipping'?'selected':'' ?>>Đang giao</option>
          <option value="completed" <?= $order['status']==='completed'?'selected':'' ?>>Hoàn thành</option>
          <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>Đã huỷ</option>
        </select>
      </div>
      <button type="submit" class="btn btn-navy">Cập nhật trạng thái</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
