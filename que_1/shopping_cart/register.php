
<?php
require 'db.php';
require 'functions.php';

if (!isset($_SESSION['captcha_a'])) {
    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $answer = (int)($_POST['captcha'] ?? -1);

    if (
        $answer !==
        ($_SESSION['captcha_a'] + $_SESSION['captcha_b'])
    ) {
        $error = 'Incorrect CAPTCHA.';
    } elseif (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $error = 'Enter a valid name, email and password.';
    } else {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO users(name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            $success = 'Registration successful.';
            unset($_SESSION['captcha_a']);
            unset($_SESSION['captcha_b']);

        } catch (PDOException $e) {
            $error = 'Email already registered.';
        }
    }

    if ($error !== '') {
        $_SESSION['captcha_a'] = random_int(1, 9);
        $_SESSION['captcha_b'] = random_int(1, 9);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <div class="panel">
        <h1>Register</h1>

        <?php if ($error) { ?>
            <p class="msg"><?= e($error) ?></p>
        <?php } ?>

        <?php if ($success) { ?>
            <p class="msg"><?= e($success) ?></p>
            <a href="user_login.php">Login Now</a>
        <?php } else { ?>

        <form method="post">
            <label>Name</label><br>
            <input type="text" name="name" required>
            <br>

            <label>Email</label><br>
            <input type="email" name="email" required>
            <br>

            <label>Password (minimum 8 characters)</label><br>
            <input type="password" name="password"
                   minlength="8" required>
            <br>

            <label>
                CAPTCHA:
                <?= (int)$_SESSION['captcha_a'] ?>
                +
                <?= (int)$_SESSION['captcha_b'] ?>
                = ?
            </label><br>

            <input type="number" name="captcha" required>
            <br>

            <button type="submit">Register</button>
        </form>

        <?php } ?>

        <a href="index.php">Home</a>
    </div>
</main>
</body>
</html>
