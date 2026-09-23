<?php
require_once __DIR__ . '/includes/init.php';

if (!$_SESSION['cart']) {
    flash_set('error', 'Giỏ hàng của bạn đang trống.');
    redirect('cart.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if ($name === '') $errors[] = 'Vui lòng nhập họ và tên.';
    if (!preg_match('/^[0-9]{9,11}$/', $phone)) $errors[] = 'Số điện thoại không hợp lệ (9-11 chữ số).';
    if ($address === '') $errors[] = 'Vui lòng nhập địa chỉ nhận hàng.';

    if (!$errors) {
        // Luôn đọc lại giá & tồn kho mới nhất từ CSDL, không tin dữ liệu từ client.
        $ids = array_keys($_SESSION['cart']);
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in) AND status='active'");
        $stmt->execute($ids);
        $byId = [];
        foreach ($stmt->fetchAll() as $pr) $byId[$pr['id']] = $pr;

        $items = [];
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $qty) {
            if (!isset($byId[$id])) continue;
            $pr = $byId[$id];
            $qty = min((int)$qty, (int)$pr['stock']);
            if ($qty <= 0) continue;
            $unit = (float)$pr['sale_price'] > 0 ? (float)$pr['sale_price'] : (float)$pr['price'];
            $items[] = ['product' => $pr, 'qty' => $qty, 'unit' => $unit];
            $total += $unit * $qty;
        }

        if (!$items) {
            $errors[] = 'Các sản phẩm trong giỏ hàng hiện đã hết hàng, vui lòng quay lại giỏ hàng.';
        } else {
            try {
                $pdo->beginTransaction();

                $fullAddress = $address . ($district ? ', ' . $district : '');
                $ins = $pdo->prepare('INSERT INTO orders (customer_name, customer_phone, customer_address, total, status, note) VALUES (?, ?, ?, ?, ?, ?)');
                $ins->execute([$name, $phone, $fullAddress, $total, 'pending', $note]);
                $orderId = (int)$pdo->lastInsertId();

                $insItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, price, qty) VALUES (?, ?, ?, ?, ?)');
                $updStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

                foreach ($items as $it) {
                    $insItem->execute([$orderId, $it['product']['id'], $it['product']['name'], $it['unit'], $it['qty']]);
                    $updStock->execute([$it['qty'], $it['product']['id'], $it['qty']]);
                }

                $pdo->commit();
                $_SESSION['cart'] = [];
                redirect('order_success.php?id=' . $orderId);
            } catch (Exception $ex) {
                $pdo->rollBack();
                $errors[] = 'Có lỗi xảy ra khi xử lý đơn hàng. Vui lòng thử lại.';
            }
        }
    }
}

$pageTitle = 'Thông tin giao hàng';
require __DIR__ . '/includes/header.php';

$ids = array_keys($_SESSION['cart']);
$in = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($in)");
$stmt->execute($ids);
$byId = [];
foreach ($stmt->fetchAll() as $pr) $byId[$pr['id']] = $pr;
$total = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    if (!isset($byId[$id])) continue;
    $pr = $byId[$id];
    $unit = (float)$pr['sale_price'] > 0 ? (float)$pr['sale_price'] : (float)$pr['price'];
    $total += $unit * $qty;
}
$districts = ['TP. Cà Mau','Năm Căn','Ngọc Hiển','Thới Bình','Trần Văn Thời','Cái Nước','Đầm Dơi','U Minh','Phú Tân','Thanh Bình'];
?>
<div class="wrap section">
  <div class="sec-head"><h1><span class="dot"></span>Thông tin giao hàng</h1></div>

  <?php if ($errors): ?>
    <div class="alert alert-error" style="max-width:560px;margin:0 0 16px;">
      <?php foreach ($errors as $er) echo '<div>' . e($er) . '</div>'; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="checkout.php" class="checkout-box">
    <?= csrf_field() ?>
    <div class="field"><label>Họ và tên</label><input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div>
    <div class="formgrid">
      <div class="field"><label>Số điện thoại</label><input name="phone" required pattern="[0-9]{9,11}" value="<?= e($_POST['phone'] ?? '') ?>"></div>
      <div class="field"><label>Huyện/Thành phố</label>
        <select name="district">
          <?php foreach ($districts as $d): ?><option <?= (($_POST['district'] ?? '')===$d)?'selected':'' ?>><?= e($d) ?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="field"><label>Địa chỉ nhận hàng</label><input name="address" required placeholder="Số nhà, đường, ấp/khóm..." value="<?= e($_POST['address'] ?? '') ?>"></div>
    <div class="field"><label>Ghi chú (không bắt buộc)</label><textarea name="note"><?= e($_POST['note'] ?? '') ?></textarea></div>
    <div class="rowbetween" style="background:var(--sand-2); border-radius:10px; padding:10px 14px;"><span>Thanh toán</span><b>COD - Thanh toán khi nhận hàng</b></div>
    <div class="rowbetween" style="font-size:16px;margin-top:10px;"><span>Tổng cộng</span><b class="tnum" style="color:var(--danger);"><?= money($total) ?></b></div>
    <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;margin-top:8px;">Xác nhận đặt hàng</button>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
