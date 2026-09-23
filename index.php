<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

function rev_sum(PDO $pdo, $where, $params = []) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='completed' AND $where");
    $stmt->execute($params);
    return (float)$stmt->fetchColumn();
}

$revToday = rev_sum($pdo, 'DATE(created_at) = CURDATE()');
$revMonth = rev_sum($pdo, 'YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())');
$revTotal = rev_sum($pdo, '1=1');
$totalOrders = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();

// Doanh thu 7 ngày gần nhất
$stmt = $pdo->query("SELECT DATE(created_at) d, SUM(total) t FROM orders WHERE status='completed' AND created_at >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(created_at)");
$byDay = [];
foreach ($stmt->fetchAll() as $row) $byDay[$row['d']] = (float)$row['t'];
$days = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $days[] = ['date' => $d, 'rev' => $byDay[$d] ?? 0];
}
$maxRev = max(1, ...array_column($days, 'rev'));

// Sản phẩm bán chạy (từ đơn hoàn thành)
$top = $pdo->query("
    SELECT p.id, p.name, p.icon, p.image, SUM(oi.qty) AS qty_sold
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE o.status = 'completed' AND oi.product_id IS NOT NULL
    GROUP BY oi.product_id
    ORDER BY qty_sold DESC
    LIMIT 5
")->fetchAll();

$recent = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC LIMIT 8')->fetchAll();

$pageTitle = 'Tổng quan';
$activeNav = 'dashboard';
require __DIR__ . '/includes/admin_header.php';

// --- vẽ biểu đồ cột doanh thu 7 ngày bằng SVG ---
$W = 640; $H = 220; $padL = 58; $padB = 30; $padT = 16; $padR = 14;
$bw = ($W - $padL - $padR) / count($days);
$grid = ''; $bars = ''; $axis = '';
for ($g = 0; $g <= 4; $g++) {
    $y = $padT + ($H - $padT - $padB) * (1 - $g / 4);
    $grid .= '<line x1="' . $padL . '" y1="' . $y . '" x2="' . ($W - $padR) . '" y2="' . $y . '" class="chart-grid"/>';
    $axis .= '<text x="' . ($padL - 8) . '" y="' . ($y + 3) . '" text-anchor="end" class="chart-axis">' . number_format(round($maxRev * $g / 4 / 1000)) . 'k</text>';
}
foreach ($days as $i => $d) {
    $h = ($d['rev'] / $maxRev) * ($H - $padT - $padB);
    $x = $padL + $i * $bw + $bw * 0.22;
    $y = $H - $padB - $h;
    $bars .= '<rect class="chart-bar" x="' . $x . '" y="' . $y . '" width="' . ($bw * 0.56) . '" height="' . $h . '"><title>' . fmt_date($d['date']) . ': ' . money($d['rev']) . '</title></rect>';
    if ($d['rev'] > 0) {
        $bars .= '<text x="' . ($x + $bw * 0.28) . '" y="' . ($y - 6) . '" text-anchor="middle" class="chart-value">' . round($d['rev'] / 1000) . 'k</text>';
    }
    $axis .= '<text x="' . ($x + $bw * 0.28) . '" y="' . ($H - 8) . '" text-anchor="middle" class="chart-axis">' . date('d/m', strtotime($d['date'])) . '</text>';
}
?>
<div class="apage-head"><div><h1>Tổng quan</h1><p>Số liệu bán hàng &amp; hoạt động cửa hàng</p></div></div>

<div class="kpis">
  <div class="kpi"><div class="lbl">Doanh thu hôm nay</div><div class="val tnum"><?= money($revToday) ?></div></div>
  <div class="kpi"><div class="lbl">Doanh thu tháng này</div><div class="val tnum"><?= money($revMonth) ?></div></div>
  <div class="kpi"><div class="lbl">Tổng doanh thu (đã hoàn thành)</div><div class="val tnum"><?= money($revTotal) ?></div></div>
  <div class="kpi"><div class="lbl">Đơn hàng</div><div class="val tnum"><?= $totalOrders ?></div>
    <span class="delta <?= $pendingCount>0?'down':'up' ?>"><?= $pendingCount ?> chờ xác nhận</span>
  </div>
</div>

<div class="cols2">
  <div class="panel">
    <div class="panel-head"><h3>Doanh thu 7 ngày gần nhất</h3></div>
    <svg class="chart-svg" viewBox="0 0 <?= $W ?> <?= $H ?>" style="width:100%;height:auto;"><?= $grid . $bars . $axis ?></svg>
  </div>
  <div class="panel">
    <div class="panel-head"><h3>Sản phẩm bán chạy</h3></div>
    <?php if (!$top): ?>
      <div class="emptystate">Chưa có dữ liệu bán hàng.</div>
    <?php else: ?>
      <ul class="toplist">
        <?php foreach ($top as $t): $img = admin_image_url($t['image'] ?? null); ?>
          <li>
            <div class="tr-ic"><?= $img ? '<img src="'.e($img).'">' : e($t['icon'] ?? '📦') ?></div>
            <div class="tr-name"><?= e($t['name'] ?? '(sản phẩm đã xoá)') ?></div>
            <div class="tr-qty tnum"><?= (int)$t['qty_sold'] ?> đã bán</div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h3>Đơn hàng gần đây</h3><a class="btn btn-line btn-sm" href="orders.php">Xem tất cả</a></div>
  <div class="tbl-wrap">
    <table>
      <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
      <tbody>
        <?php if (!$recent): ?><tr><td colspan="5" class="emptystate">Chưa có đơn hàng</td></tr><?php endif; ?>
        <?php foreach ($recent as $o): [$label,$cls] = order_status_label($o['status']); ?>
          <tr>
            <td><a href="order_view.php?id=<?= (int)$o['id'] ?>">#<?= e(order_code($o['id'])) ?></a></td>
            <td><?= e($o['customer_name']) ?></td>
            <td><?= fmt_date($o['created_at']) ?></td>
            <td class="tnum"><?= money($o['total']) ?></td>
            <td><span class="pill <?= $cls ?>"><?= $label ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
