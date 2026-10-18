
<?php
require_once 'functions.php';
require_once 'db.php';

$stmt = $conn->query(
    "SELECT * FROM categories ORDER BY category_name"
);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isAdmin = !empty($_SESSION['admin_id']);
$isUser = !empty($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>My Shopping Store</h2>

    <nav>
        <a href="index.php">Home</a>

        <?php if ($isAdmin) { ?>

            <span>
                Welcome, Admin
                <?= e($_SESSION['admin_username']) ?>
            </span>

            <a href="admin.php">Admin Dashboard</a>
            <a href="product_manage.php">Add Product</a>
            <a href="logout.php">Logout</a>

        <?php } elseif ($isUser) { ?>

            <span>
                Welcome, <?= e($_SESSION['user_name']) ?>
            </span>

            <a href="cart.php">Shopping Cart</a>
            <a href="user_logout.php">Logout</a>

        <?php } else { ?>

                <a href="register.php">Register</a>
                <a href="user_login.php">Customer Login</a>
                <a href="login.php">Admin Login</a>

        <?php } ?>
    </nav>
</header>

<main>
    <h1>Shop by Category</h1>

    <div class="grid">
        <?php foreach ($categories as $category) { ?>
            <div class="panel">
                <h2>
                    <?= e($category['category_name']) ?>
                </h2>

                <a class="btn"
                   href="category.php?id=<?= (int)$category['id'] ?>">
                    View Products
                </a>
            </div>
        <?php } ?>
    </div>
</main>

</body>
</html>
