<?php
require_once __DIR__ . '/includes/init.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND status='published'");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    $pageTitle = 'Không tìm thấy bài viết';
    require __DIR__ . '/includes/header.php';
    echo '<div class="wrap section"><div class="emptystate">Bài viết không tồn tại. <a href="news.php">Về trang tin tức</a></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $p['title'];
require __DIR__ . '/includes/header.php';
$img = image_url($p['image']);
?>
<div class="wrap">
  <article class="news-detail">
    <div class="cov" style="<?= $img ? '' : 'background:linear-gradient(135deg,var(--navy),var(--mangrove));display:flex;align-items:center;justify-content:center;color:#fff;font-size:48px;' ?>">
      <?= $img ? '<img src="'.e($img).'" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:14px;">' : e($p['icon']) ?>
    </div>
    <div class="tag" style="color:var(--mangrove);font-weight:700;font-size:12.5px;text-transform:uppercase;"><?= e($p['tag']) ?></div>
    <h1 style="font-size:24px;margin:8px 0 6px;"><?= e($p['title']) ?></h1>
    <div style="font-size:12.5px;color:var(--ink-dim);margin-bottom:18px;">Đăng ngày <?= fmt_date($p['published_at']) ?></div>
    <div class="content"><?= nl2br(e($p['content'])) ?></div>
  </article>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
