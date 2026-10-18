
<?php
require 'db.php';
require 'functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE email = ?"
    );
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if (
        $user &&
        password_verify($password, $user['password'])
    ) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        header("Location: index.php");
        exit;
    }

    $error = 'Invalid email or password.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <div class="panel">
        <h1>User Login</h1>

        <?php if ($error) { ?>
            <p class="msg"><?= e($error) ?></p>
        <?php } ?>

        <form method="post">
            <label>Email</label><br>
            <input type="email" name="email" required>
            <br>

            <label>Password</label><br>
            <input type="password" name="password" required>
            <br>

            <button type="submit">Login</button>
        </form>

        <a href="register.php">Register</a> |
        <a href="index.php">Home</a>
    </div>
</main>
</body>
</html>
