
<?php
require_once 'config.php';
require_once 'includes/functions.php';

require_login();

$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(quantity), 0) AS qty,
        COALESCE(SUM(quantity * price), 0) AS value,
        COALESCE(SUM(quantity <= 5), 0) AS low
    FROM products
")->fetch(PDO::FETCH_ASSOC);

$pending = 0;

if (is_admin()) {
    $pending = (int) $pdo->query("
        SELECT COUNT(*)
        FROM users
        WHERE status = 'pending'
    ")->fetchColumn();
}

$recent = $pdo->query("
    SELECT p.*, u.username
    FROM products p
    LEFT JOIN users u ON u.id = p.created_by
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard';

require 'includes/header.php';

?>

<h1>Hello, <?= e($_SESSION['username']) ?>!</h1>
<p>Here is an overview of your current products.</p>

<?php if ($pending > 0): ?>

    <div>
        There are <?= $pending ?> account(s) waiting for approval.
        <a href="users.php">Review accounts</a>
    </div>

<?php endif; ?>

<ul>
    <li>
        Total Products:
        <b><?= (int) $stats['total'] ?></b>
    </li>

    <li>
        Total Units in Stock:
        <b><?= (int) $stats['qty'] ?></b>
    </li>

    <li>
        Total Inventory Value:
        <b>₱<?= number_format((float) $stats['value'], 2) ?></b>
    </li>

    <li>
        Low Stock Products:
        <b><?= (int) $stats['low'] ?></b>
    </li>

</ul>

<div>

    <h2>Recently Added Products</h2>

    <a href="product_form.php">Add New Product</a>

</div>

<?php if (empty($recent)): ?>

    <p>
        No products have been added yet.
        Start by adding your first product!
    </p>

<?php else: ?>

    <table border="1" cellpadding="6" cellspacing="0">

        <tr>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Added By</th>
        </tr>

        <?php foreach ($recent as $product): ?>

            <tr>
                <td>
                    <?= e($product['name']) ?>
                </td>
                <td>
                    <?= (int) $product['quantity'] ?>
                </td>
                <td>
                    ₱<?= number_format((float) $product['price'], 2) ?>
                </td>
                <td>
                    <?= e($product['username'] ?? 'Deleted User') ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php endif; ?>
<?php require 'includes/footer.php'; ?>
