<?php
require_once __DIR__ . '/includes/init.php';

$perPage = 9;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$total = (int)$pdo->query("SELECT COUNT(*) FROM posts WHERE status='published'")->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->query("SELECT * FROM posts WHERE status='published' ORDER BY published_at DESC LIMIT $perPage OFFSET $offset");
$posts = $stmt->fetchAll();

$pageTitle = 'Tin tức & Thông báo';
require __DIR__ . '/includes/header.php';
?>
<div class="wrap section">
  <div class="sec-head"><h1><span class="dot"></span>Tin tức &amp; Thông báo</h1></div>
  <?php if (!$posts): ?>
    <div class="emptystate">Chưa có tin tức nào.</div>
  <?php else: ?>
    <div class="news-grid">
      <?php foreach ($posts as $p): $img = image_url($p['image']); ?>
        <a class="news-card" href="news_detail.php?id=<?= (int)$p['id'] ?>">
          <div class="cov"><?= $img ? '<img src="'.e($img).'" alt="">' : e($p['icon']) ?></div>
          <div class="nb">
            <div class="tag"><?= e($p['tag']) ?></div>
            <h3><?= e($p['title']) ?></h3>
            <p><?= e($p['excerpt']) ?></p>
            <div class="meta"><?= fmt_date($p['published_at']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <div style="display:flex;gap:8px;justify-content:center;margin-top:24px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a class="btn <?= $i===$page ? 'btn-navy' : 'btn-line' ?> btn-sm" href="news.php?page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
