<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('posts.php');
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT image FROM posts WHERE id = ?');
$stmt->execute([$id]);
$img = $stmt->fetchColumn();

$pdo->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);

if ($img) delete_image($img);

flash_set('ok', 'Đã xoá bài viết.');
redirect('posts.php');
