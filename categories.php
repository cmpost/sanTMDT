<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '') ?: 'Danh mục mới';
        $icon = trim($_POST['icon'] ?? '') ?: '🏷️';
        $slug = unique_slug($pdo, 'categories', slugify($name));
        $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM categories')->fetchColumn();
        $stmt = $pdo->prepare('INSERT INTO categories (name, slug, icon, sort_order) VALUES (?,?,?,?)');
        $stmt->execute([$name, $slug, $icon, $maxOrder + 1]);
        flash_set('ok', 'Đã thêm danh mục mới.');
    } elseif ($action === 'rename') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        if ($name !== '') {
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, icon = ? WHERE id = ?');
            $stmt->execute([$name, $icon ?: '🏷️', $id]);
            flash_set('ok', 'Đã lưu thay đổi danh mục.');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $countStmt->execute([$id]);
        if ((int)$countStmt->fetchColumn() > 0) {
            flash_set('error', 'Không thể xoá: danh mục vẫn còn sản phẩm bên trong.');
        } else {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            flash_set('ok', 'Đã xoá danh mục.');
        }
    }
    redirect('categories.php');
}

$cats = $pdo->query('
    SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
    FROM categories c ORDER BY c.sort_order, c.name
')->fetchAll();

$pageTitle = 'Danh mục';
$activeNav = 'categories';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="apage-head">
  <div><h1>Danh mục sản phẩm</h1><p><?= count($cats) ?> danh mục</p></div>
</div>

<div class="panel" style="max-width:420px;">
  <div class="panel-head"><h3>Thêm danh mục mới</h3></div>
  <form method="post" action="categories.php" style="display:flex;gap:10px;align-items:end;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="field" style="width:70px;margin-bottom:0;"><label>Icon</label><input name="icon" maxlength="4" placeholder="🏷️"></div>
    <div class="field" style="flex:1;margin-bottom:0;"><label>Tên danh mục</label><input name="name" required placeholder="VD: Đặc sản mùa hè"></div>
    <button type="submit" class="btn btn-navy">Thêm</button>
  </form>
</div>

<div class="panel">
  <div class="tbl-wrap">
    <table>
      <thead><tr><th style="width:60px;">Icon</th><th>Tên danh mục</th><th>Số sản phẩm</th><th></th></tr></thead>
      <tbody>
        <?php if (!$cats): ?><tr><td colspan="4" class="emptystate">Chưa có danh mục</td></tr><?php endif; ?>
        <?php foreach ($cats as $c): ?>
          <tr>
            <td><input name="icon" form="renameForm-<?= (int)$c['id'] ?>" value="<?= e($c['icon']) ?>" maxlength="4" style="width:50px;border:1px solid var(--line);border-radius:8px;padding:6px;text-align:center;"></td>
            <td><input name="name" form="renameForm-<?= (int)$c['id'] ?>" value="<?= e($c['name']) ?>" style="border:1px solid var(--line);border-radius:8px;padding:6px 9px;font-size:13px;width:100%;max-width:280px;"></td>
            <td><?= (int)$c['product_count'] ?></td>
            <td>
              <div class="rowacts">
                <button type="submit" form="renameForm-<?= (int)$c['id'] ?>" class="btn btn-line btn-sm">Lưu</button>
                <button type="submit" form="deleteForm-<?= (int)$c['id'] ?>" class="btn btn-danger btn-sm">Xoá</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php foreach ($cats as $c): ?>
  <form id="renameForm-<?= (int)$c['id'] ?>" method="post" action="categories.php" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="rename">
    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
  </form>
  <form id="deleteForm-<?= (int)$c['id'] ?>" method="post" action="categories.php" onsubmit="return confirm('Xoá danh mục này?');" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
  </form>
<?php endforeach; ?>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
