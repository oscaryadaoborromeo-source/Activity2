<?php $flash = get_flash(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Home') ?> | Product Management System</title>
</head>
<body>
<?php if (!empty($_SESSION['user_id'])): ?>
<h2>Product Management System</h2>
<p>
    <a href="dashboard.php">Dashboard</a> |
    <a href="products.php">Products</a>
    <?php if (is_admin()): ?> | <a href="users.php">Users</a><?php endif; ?>
    | Logged in as <b><?= e($_SESSION['username']) ?></b> (<?= e($_SESSION['role']) ?>)
    | <a href="logout.php">Log out</a>
</p>
<hr>
<?php endif; ?>
<?php if ($flash): ?>
    <p><b><?= $flash['type'] === 'error' ? 'Error:' : 'Success:' ?></b> <?= e($flash['message']) ?></p>
<?php endif; ?>
