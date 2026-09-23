<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'toggle') {
        $stmt = $pdo->prepare('SELECT status, published_at FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if ($post) {
            $newStatus = $post['status'] === 'published' ? 'draft' : 'published';
            $publishedAt = $post['published_at'];
            if ($newStatus === 'published' && !$publishedAt) $publishedAt = date('Y-m-d H:i:s');
            $upd = $pdo->prepare('UPDATE posts SET status = ?, published_at = ? WHERE id = ?');
            $upd->execute([$newStatus, $publishedAt, $id]);
            flash_set('ok', $newStatus === 'published' ? 'Đã đăng bài viết.' : 'Đã gỡ bài khỏi trang chủ.');
        }
    }
    redirect('posts.php');
}

$posts = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC')->fetchAll();

$pageTitle = 'Tin tức';
$activeNav = 'posts';
require __DIR__ . '/includes/admin_header.php';
?>
<div class="apage-head">
  <div><h1>Tin tức &amp; Bài đăng</h1><p><?= count($posts) ?> bài viết</p></div>
  <a class="btn btn-navy" href="post_edit.php">+ Đăng bài mới</a>
</div>

<div class="panel">
  <div class="tbl-wrap">
    <table>
      <thead><tr><th></th><th>Tiêu đề</th><th>Chuyên mục</th><th>Ngày đăng</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
        <?php if (!$posts): ?><tr><td colspan="6" class="emptystate">Chưa có bài viết nào</td></tr><?php endif; ?>
        <?php foreach ($posts as $p): $img = admin_image_url($p['image']); ?>
          <tr>
            <td><div class="rowimg"><?= $img ? '<img src="'.e($img).'">' : e($p['icon']) ?></div></td>
            <td style="max-width:300px;"><?= e($p['title']) ?></td>
            <td><?= e($p['tag']) ?></td>
            <td><?= $p['published_at'] ? fmt_date($p['published_at']) : '—' ?></td>
            <td><span class="pill <?= $p['status']==='published' ? 'ok' : 'muted' ?>"><?= $p['status']==='published' ? 'Đã đăng' : 'Bản nháp' ?></span></td>
            <td>
              <div class="rowacts">
                <form method="post" action="posts.php" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button type="submit" class="btn btn-line btn-sm"><?= $p['status']==='published' ? 'Gỡ bài' : 'Đăng bài' ?></button>
                </form>
                <a class="btn btn-line btn-sm" href="post_edit.php?id=<?= (int)$p['id'] ?>">Sửa</a>
                <form method="post" action="post_delete.php" onsubmit="return confirm('Xoá bài viết này?');" style="display:inline;">
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
