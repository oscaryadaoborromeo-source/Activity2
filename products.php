
<?php
require_once 'config.php';
require_once 'includes/functions.php';

require_login();

$search = trim($_GET['q'] ?? '');

$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$productsPerPage = 10;
$offset = ($currentPage - 1) * $productsPerPage;

$where = '';
$params = [];

if ($search !== '') {

    $where = "WHERE p.name LIKE ? OR p.description LIKE ?";
    $searchKeyword = "%{$search}%";
    $params = [$searchKeyword, $searchKeyword];
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM products p
    $where
");

$countStmt->execute($params);
$totalProducts = (int) $countStmt->fetchColumn();

$totalPages = max(
    1,
    (int) ceil($totalProducts / $productsPerPage)
);

$productStmt = $pdo->prepare("
    SELECT p.*, u.username
    FROM products p
    LEFT JOIN users u ON u.id = p.created_by
    $where
    ORDER BY p.id DESC
    LIMIT $productsPerPage OFFSET $offset
");

$productStmt->execute($params);
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Products';

require 'includes/header.php';

?>

<div>

    <h1>Products</h1>

    <a href="product_form.php">Add New Product</a>

</div>

<form method="GET">

    <input
        type="text"
        name="q"
        value="<?= e($search) ?>"
        placeholder="Search by product name or description"
    >

    <button type="submit">Search</button>
    <?php if ($search !== ''): ?>
        <a href="products.php">Clear Search</a>
    <?php endif; ?>
</form>

<?php if (empty($products)): ?>

    <p>
        <?= $search !== ''
            ? 'No products match your search.'
            : 'No products have been added yet. Add your first product to get started.'
        ?>
    </p>

<?php else: ?>

    <table border="1" cellpadding="6" cellspacing="0">

        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Added By</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($products as $product): ?>

            <tr>

                <td>
                    <?= (int) $product['id'] ?>
                </td>

                <td>
                    <?= e($product['name']) ?>
                </td>

                <td>
                    <?= e($product['description']) ?>
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

                <td>

                    <?php if (can_modify_product($product)): ?>

                        <a href="product_form.php?id=<?= (int) $product['id'] ?>">
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="product_delete.php"
                            onsubmit="return confirm('Are you sure you want to delete this product?');"
                        >

                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $product['id'] ?>"
                            >

                            <button type="submit">
                                Delete
                            </button>

                        </form>

                    <?php else: ?>

                        <span>View Only</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($totalPages > 1): ?>

        <div>
            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                <a href="?page=<?= $pageNumber ?>&q=<?= urlencode($search) ?>">
                    <?= $pageNumber ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>
<?php require 'includes/footer.php'; ?>
