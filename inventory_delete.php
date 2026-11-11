<?php
require_once 'config.php';
require_once 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}
csrf_check();

$id = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    flash('Product not found.', 'error');
} elseif (!can_modify_product($product)) {
    http_response_code(403);
    flash('You can only delete products you added.', 'error');
} else {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    flash('Product deleted.');
}
header('Location: products.php');
exit;
