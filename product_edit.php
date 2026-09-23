<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$product = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) redirect('products.php');
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY sort_order, name')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
    $price = (float)($_POST['price'] ?? 0);
    $salePrice = (float)($_POST['sale_price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status = ($_POST['status'] ?? 'active') === 'hidden' ? 'hidden' : 'active';

    if ($name === '') $errors[] = 'Vui lòng nhập tên sản phẩm.';
    if ($price < 0) $errors[] = 'Giá sản phẩm không hợp lệ.';
    if ($stock < 0) $errors[] = 'Tồn kho không hợp lệ.';

    $uploadResult = upload_image('image', 'products');
    if ($uploadResult === false) {
        $errors[] = 'Lỗi tải ảnh lên (xem chi tiết phía trên).';
    }

    if (!$errors) {
        $slugBase = slugify($name);
        $imagePath = $product['image'] ?? null;
        if ($uploadResult) {
            if ($product && $product['image']) delete_image($product['image']);
            $imagePath = $uploadResult;
        }

        if ($product) {
            $slug = unique_slug($pdo, 'products', $slugBase, $product['id']);
            $stmt = $pdo->prepare('UPDATE products SET category_id=?, name=?, slug=?, price=?, sale_price=?, stock=?, image=?, description=?, status=? WHERE id=?');
            $stmt->execute([$categoryId, $name, $slug, $price, $salePrice, $stock, $imagePath, $description, $status, $product['id']]);
            flash_set('ok', 'Đã cập nhật sản phẩm.');
        } else {
            $slug = unique_slug($pdo, 'products', $slugBase);
            $stmt = $pdo->prepare('INSERT INTO products (category_id, name, slug, price, sale_price, stock, image, icon, description, status) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$categoryId, $name, $slug, $price, $salePrice, $stock, $imagePath, '📦', $description, $status]);
            flash_set('ok', 'Đã thêm sản phẩm mới.');
        }
        redirect('products.php');
    }
}

$pageTitle = $product ? 'Sửa sản phẩm' : 'Thêm sản phẩm';
$activeNav = 'products';
require __DIR__ . '/includes/admin_header.php';

$v = fn($k, $def = '') => e($_POST[$k] ?? ($product[$k] ?? $def));
$curImg = admin_image_url($product['image'] ?? null);
$saleVal = $_POST['sale_price'] ?? (((float)($product['sale_price'] ?? 0)) > 0 ? $product['sale_price'] : '');
?>
<div class="apage-head"><div><h1><?= $product ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' ?></h1></div>
  <a class="btn btn-line btn-sm" href="products.php">← Quay lại danh sách</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error" style="margin:0 0 16px;"><?php foreach ($errors as $er) echo '<div>'.e($er).'</div>'; ?></div>
<?php endif; ?>

<div class="panel" style="max-width:640px;">
  <form method="post" action="product_edit.php<?= $product ? '?id='.(int)$product['id'] : '' ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="imgpick">
      <div class="prev"><?= $curImg ? '<img src="'.e($curImg).'">' : e($product['icon'] ?? '📦') ?></div>
      <div>
        <input type="file" name="image" accept="image/*">
        <div style="font-size:11px;color:var(--ink-dim);margin-top:4px;">Không chọn ảnh sẽ giữ ảnh/biểu tượng hiện tại (tối đa 3MB).</div>
      </div>
    </div>
    <div class="field"><label>Tên sản phẩm</label><input name="name" required value="<?= $v('name') ?>"></div>
    <div class="formgrid">
      <div class="field"><label>Danh mục</label>
        <select name="category_id">
          <option value="">— Không thuộc danh mục —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (($product['category_id'] ?? null)==$c['id'])?'selected':'' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Trạng thái hiển thị</label>
        <select name="status">
          <option value="active" <?= (($product['status'] ?? 'active')==='active')?'selected':'' ?>>Đang bán</option>
          <option value="hidden" <?= (($product['status'] ?? '')==='hidden')?'selected':'' ?>>Ẩn khỏi cửa hàng</option>
        </select>
      </div>
      <div class="field"><label>Tồn kho</label><input type="number" name="stock" min="0" required value="<?= $v('stock', 10) ?>"></div>
      <div class="field"><label>Giá gốc (đ)</label><input type="number" name="price" min="0" required value="<?= $v('price', 0) ?>"></div>
      <div class="field"><label>Giá khuyến mãi (đ, để trống nếu không có)</label><input type="number" name="sale_price" min="0" value="<?= e($saleVal) ?>"></div>
    </div>
    <div class="field"><label>Mô tả</label><textarea name="description"><?= $v('description') ?></textarea></div>
    <button type="submit" class="btn btn-navy">Lưu sản phẩm</button>
  </form>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
