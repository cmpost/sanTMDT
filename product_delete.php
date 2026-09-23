<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
$stmt->execute([$id]);
$img = $stmt->fetchColumn();

$del = $pdo->prepare('DELETE FROM products WHERE id = ?');
$del->execute([$id]);

if ($img) delete_image($img);

flash_set('ok', 'Đã xoá sản phẩm.');
redirect('products.php');
