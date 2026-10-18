
<?php
require 'db.php';
require 'functions.php';

require_admin();

$categories = $conn->query(
    "SELECT * FROM categories ORDER BY id DESC"
)->fetchAll();

$products = $conn->query(
    "SELECT p.*, c.category_name
     FROM products p
     JOIN categories c ON p.category_id = c.id
     ORDER BY p.id DESC"
)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Admin Panel</h2>
    <nav>
        <a href="admin.php">Dashboard</a>
        <a href="logout.php">Logout</a>
        <a href="index.php">View Store</a>
    </nav>
</header>

<main>
    <h1>Welcome, Admin</h1>

    <div class="panel">
        <h2>Manage Categories</h2>
        <a class="btn" href="category_manage.php">
            Manage Categories
        </a>
    </div>

    <div class="panel">
        <h2>Manage Products</h2>
        <a class="btn" href="product_manage.php">
            Manage Products
        </a>
    </div>

    <div class="panel">
        <h2>Categories</h2>

        <table>
            <tr><th>ID</th><th>Category Name</th></tr>

            <?php foreach ($categories as $c) { ?>
                <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><?= e($c['category_name']) ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <div class="panel">
        <h2>Products</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
            </tr>

            <?php foreach ($products as $p) { ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= e($p['pname']) ?></td>
                    <td><?= e($p['category_name']) ?></td>
                    <td>₹<?= number_format($p['price'], 2) ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</main>

</body>
</html>
