
<?php
require_once 'config.php';
require_once 'includes/functions.php';

require_login();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$product = [
    'name' => '',
    'description' => '',
    'quantity' => 0,
    'price' => '0.00',
    'created_by' => $_SESSION['user_id']
];

if ($id > 0) {

    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE id = ?"
    );

    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {

        flash('Product not found.', 'error');

        header('Location: products.php');
        exit;
    }

    if (!can_modify_product($product)) {

        http_response_code(403);

        flash('You can only edit products you added.', 'error');

        header('Location: products.php');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = $_POST['quantity'] ?? '';
    $price = $_POST['price'] ?? '';

    if ($name === '' || strlen($name) > 150) {

        $error = 'Please enter a product name (maximum of 150 characters).';

    }

    elseif (
        filter_var($quantity, FILTER_VALIDATE_INT) === false ||
        (int) $quantity < 0
    ) {

        $error = 'Quantity must be a whole number and cannot be negative.';

    }

    elseif (!is_numeric($price) || (float) $price < 0) {

        $error = 'Please enter a valid price.';

    }

    else {

        if ($id > 0) {

            $stmt = $pdo->prepare("
                UPDATE products
                SET name = ?, description = ?, quantity = ?, price = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $name,
                $description,
                (int) $quantity,
                (float) $price,
                $id
            ]);

            flash('Product successfully updated.');

        }

        else {

            $stmt = $pdo->prepare("
                INSERT INTO products
                (name, description, quantity, price, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $name,
                $description,
                (int) $quantity,
                (float) $price,
                $_SESSION['user_id']
            ]);

            flash('New product successfully added.');

        }

        header('Location: products.php');
        exit;
    }

    $product = array_merge($product, [
        'name' => $name,
        'description' => $description,
        'quantity' => $quantity,
        'price' => $price
    ]);
}

$pageTitle = $id > 0 ? 'Edit Product' : 'Add Product';

require 'includes/header.php';

?>

<h1>
    <?= $id > 0 ? 'Edit Product' : 'Add Product' ?>
</h1>

<?php if ($error): ?>

    <p>
        <b>Error:</b>
        <?= e($error) ?>
    </p>

<?php endif; ?>

<form method="POST">

    <?= csrf_field() ?>

    <input type="hidden" name="id" value="<?= $id ?>">

    <table cellpadding="6" cellspacing="0">

        <tr>
            <td>
                <label for="name">Product Name:</label>
            </td>

            <td>
                <input
                    type="text"
                    id="name"
                    name="name"
                    size="40"
                    maxlength="150"
                    required
                    value="<?= e($product['name']) ?>"
                >
            </td>
        </tr>

        <tr>
            <td valign="top">
                <label for="description">Description:</label>
            </td>

            <td>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    cols="42"
                ><?= e($product['description']) ?></textarea>
            </td>
        </tr>

        <tr>
            <td>
                <label for="quantity">Quantity:</label>
            </td>

            <td>
                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    min="0"
                    step="1"
                    required
                    value="<?= e($product['quantity']) ?>"
                >
            </td>
        </tr>

        <tr>
            <td>
                <label for="price">Price (₱):</label>
            </td>

            <td>
                <input
                    type="number"
                    id="price"
                    name="price"
                    min="0"
                    step="0.01"
                    required
                    value="<?= e($product['price']) ?>"
                >
            </td>
        </tr>

        <tr>
            <td></td>
            <td>
                <button type="submit">
                    <?= $id > 0 ? 'Save Changes' : 'Add Product' ?>
                </button>
                <a href="products.php">Cancel</a>
            </td>
        </tr>

    </table>

</form>
<?php require 'includes/footer.php'; ?>
