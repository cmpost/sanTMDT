<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$status = $_GET['status'] ?? 'all';
$validStatuses = ['pending', 'shipping', 'completed', 'cancelled'];

if (in_array($status, $validStatuses, true)) {
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC');
    $stmt->execute([$status]);
} else {
    $status = 'all';
    $stmt = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC');
}
$orders = $stmt->fetchAll();

$options = ['all' => 'Tất cả', 'pending' => 'Chờ xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã huỷ'];

$pageTitle = 'Đơn hàng';
$activeNav = 'orders';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="apage-head">
  <div><h1>Đơn hàng</h1><p><?= count($orders) ?> đơn hàng<?= $status!=='all' ? ' (đã lọc)' : '' ?></p></div>
  <form method="get" action="orders.php">
    <select class="statusSel" name="status" onchange="this.form.submit()">
      <?php foreach ($options as $val => $label): ?>
        <option value="<?= $val ?>" <?= $status===$val?'selected':'' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="panel">
  <div class="tbl-wrap">
    <table>
      <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>SĐT</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
        <?php if (!$orders): ?><tr><td colspan="7" class="emptystate">Không có đơn hàng nào</td></tr><?php endif; ?>
        <?php foreach ($orders as $o): [$label,$cls] = order_status_label($o['status']); ?>
          <tr>
            <td><?= e(order_code($o['id'])) ?></td>
            <td><?= e($o['customer_name']) ?></td>
            <td><?= e($o['customer_phone']) ?></td>
            <td><?= fmt_datetime($o['created_at']) ?></td>
            <td class="tnum"><?= money($o['total']) ?></td>
            <td><span class="pill <?= $cls ?>"><?= $label ?></span></td>
            <td><a class="btn btn-line btn-sm" href="order_view.php?id=<?= (int)$o['id'] ?>">Xem</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
