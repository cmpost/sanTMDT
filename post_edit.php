<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$post = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) redirect('posts.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $title = trim($_POST['title'] ?? '');
    $tag = trim($_POST['tag'] ?? '') ?: 'Thông báo';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

    if ($title === '') $errors[] = 'Vui lòng nhập tiêu đề bài viết.';

    $uploadResult = upload_image('image', 'posts');
    if ($uploadResult === false) $errors[] = 'Lỗi tải ảnh lên (xem chi tiết phía trên).';

    if (!$errors) {
        $slugBase = slugify($title);
        $imagePath = $post['image'] ?? null;
        if ($uploadResult) {
            if ($post && $post['image']) delete_image($post['image']);
            $imagePath = $uploadResult;
        }
        $publishedAt = $post['published_at'] ?? null;
        if ($status === 'published' && !$publishedAt) $publishedAt = date('Y-m-d H:i:s');

        if ($post) {
            $slug = unique_slug($pdo, 'posts', $slugBase, $post['id']);
            $stmt = $pdo->prepare('UPDATE posts SET title=?, slug=?, tag=?, excerpt=?, content=?, image=?, status=?, published_at=? WHERE id=?');
            $stmt->execute([$title, $slug, $tag, $excerpt, $content, $imagePath, $status, $publishedAt, $post['id']]);
            flash_set('ok', 'Đã cập nhật bài viết.');
        } else {
            $slug = unique_slug($pdo, 'posts', $slugBase);
            $stmt = $pdo->prepare('INSERT INTO posts (title, slug, tag, excerpt, content, image, icon, status, published_at) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$title, $slug, $tag, $excerpt, $content, $imagePath, '📰', $status, $publishedAt]);
            flash_set('ok', 'Đã đăng bài viết mới.');
        }
        redirect('posts.php');
    }
}

$pageTitle = $post ? 'Sửa bài viết' : 'Đăng bài mới';
$activeNav = 'posts';
require __DIR__ . '/includes/admin_header.php';

$v = fn($k, $def = '') => e($_POST[$k] ?? ($post[$k] ?? $def));
$curImg = admin_image_url($post['image'] ?? null);
?>
<div class="apage-head"><div><h1><?= $post ? 'Sửa bài viết' : 'Đăng bài viết mới' ?></h1></div>
  <a class="btn btn-line btn-sm" href="posts.php">← Quay lại danh sách</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error" style="margin:0 0 16px;"><?php foreach ($errors as $er) echo '<div>'.e($er).'</div>'; ?></div>
<?php endif; ?>

<div class="panel" style="max-width:680px;">
  <form method="post" action="post_edit.php<?= $post ? '?id='.(int)$post['id'] : '' ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="imgpick">
      <div class="prev"><?= $curImg ? '<img src="'.e($curImg).'">' : e($post['icon'] ?? '📰') ?></div>
      <div>
        <input type="file" name="image" accept="image/*">
        <div style="font-size:11px;color:var(--ink-dim);margin-top:4px;">Ảnh đại diện bài viết (không bắt buộc, tối đa 3MB).</div>
      </div>
    </div>
    <div class="field"><label>Tiêu đề</label><input name="title" required value="<?= $v('title') ?>"></div>
    <div class="formgrid">
      <div class="field"><label>Chuyên mục</label><input name="tag" required value="<?= $v('tag', 'Thông báo') ?>"></div>
      <div class="field"><label>Trạng thái</label>
        <select name="status">
          <option value="published" <?= (($post['status'] ?? '')==='published')?'selected':'' ?>>Đăng ngay</option>
          <option value="draft" <?= (($post['status'] ?? 'draft')==='draft')?'selected':'' ?>>Lưu nháp</option>
        </select>
      </div>
    </div>
    <div class="field"><label>Tóm tắt ngắn</label><textarea name="excerpt" style="min-height:56px;"><?= $v('excerpt') ?></textarea></div>
    <div class="field"><label>Nội dung bài viết</label><textarea name="content" style="min-height:180px;"><?= $v('content') ?></textarea></div>
    <button type="submit" class="btn btn-navy">Lưu bài viết</button>
  </form>
</div>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
