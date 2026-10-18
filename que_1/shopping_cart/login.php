
<?php
require 'db.php';
require 'functions.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT * FROM admins WHERE username = ?"
    );
    $stmt->execute([$username]);

    $admin = $stmt->fetch();

    if ($admin) {
        // Supports the SHA-256 hash inserted by database.sql.
        $valid = hash_equals(
            $admin['password'],
            hash('sha256', $password)
        );

        // Also support password_hash() if used later.
        if (!$valid && password_verify(
            $password, $admin['password']
        )) {
            $valid = true;
        }

        if ($valid) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header("Location: admin.php");
            exit;
        }
    }

    $error = "Invalid username or password.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <div class="panel">
        <h1>Admin Login</h1>

        <?php if ($error) { ?>
            <p class="msg"><?= e($error) ?></p>
        <?php } ?>

        <form method="post">
            <label>Username</label><br>
            <input type="text" name="username" required>
            <br>

            <label>Password</label><br>
            <input type="password" name="password" required>
            <br>

            <button type="submit">Login</button>
        </form>

        <a href="index.php">Go to Store</a>
    </div>
</main>
</body>
</html>
