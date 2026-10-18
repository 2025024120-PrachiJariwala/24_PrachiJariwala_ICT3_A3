
<?php
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];

    $stmt = $conn->prepare(
        "SELECT id FROM products WHERE id = ?"
    );
    $stmt->execute([$id]);

    if ($stmt->fetch()) {
        $_SESSION['cart'][$id] =
            ($_SESSION['cart'][$id] ?? 0) + 1;
    }

    header("Location: cart.php");
    exit;
}

if (isset($_POST['remove'])) {
    unset($_SESSION['cart'][(int)$_POST['remove']]);

    header("Location: cart.php");
    exit;
}

$items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_map(
        'intval',
        array_keys($_SESSION['cart'])
    );

    $placeholders = implode(
        ',',
        array_fill(0, count($ids), '?')
    );

    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE id IN ($placeholders)"
    );
    $stmt->execute($ids);

    foreach ($stmt->fetchAll() as $product) {
        $product['quantity'] =
            $_SESSION['cart'][$product['id']];

        $product['subtotal'] =
            $product['price'] * $product['quantity'];

        $total += $product['subtotal'];
        $items[] = $product;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2>Shopping Cart</h2>
    <a href="index.php">Continue Shopping</a>
</header>

<main>
    <div class="panel">
        <h1>Your Cart</h1>

        <?php if (empty($items)) { ?>
            <p>Your cart is empty.</p>
        <?php } else { ?>

        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>

            <?php foreach ($items as $p) { ?>
                <tr>
                    <td><?= e($p['pname']) ?></td>

                    <td>
                        ₹<?= number_format($p['price'], 2) ?>
                    </td>

                    <td><?= (int)$p['quantity'] ?></td>

                    <td>
                        ₹<?= number_format($p['subtotal'], 2) ?>
                    </td>

                    <td>
                        <form method="post">
                            <button class="danger"
                                    name="remove"
                                    value="<?= (int)$p['id'] ?>">
                                Remove
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <h2>Total: ₹<?= number_format($total, 2) ?></h2>

        <?php } ?>
    </div>
</main>
</body>
</html>
