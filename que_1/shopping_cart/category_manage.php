
<?php
require 'db.php';
require 'functions.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['category_name'] ?? '');

            if ($name === '') {
                throw new Exception("Category name is required.");
            }

            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE categories
                     SET category_name = ? WHERE id = ?"
                );
                $stmt->execute([$name, $id]);
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO categories(category_name)
                     VALUES (?)"
                );
                $stmt->execute([$name]);
            }

            header("Location: category_manage.php");
            exit;
        }

        if ($action === 'delete') {
            $stmt = $conn->prepare(
                "DELETE FROM categories WHERE id = ?"
            );
            $stmt->execute([(int)$_POST['id']]);

            header("Location: category_manage.php");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Category already exists or database error occurred.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare(
        "SELECT * FROM categories WHERE id = ?"
    );
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$categories = $conn->query(
    "SELECT * FROM categories ORDER BY id DESC"
)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <div class="panel">
        <h1><?= $edit ? 'Edit' : 'Add' ?> Category</h1>

        <?php if ($error) { ?>
            <p class="msg"><?= e($error) ?></p>
        <?php } ?>

        <form method="post">
            <input type="hidden" name="action" value="save">

            <input type="hidden" name="id"
                   value="<?= (int)($edit['id'] ?? 0) ?>">

            <label>Category Name</label>
            <input type="text" name="category_name"
                   maxlength="100" required
                   value="<?= e($edit['category_name'] ?? '') ?>">

            <button type="submit">Save Category</button>
        </form>

        <a href="admin.php">Back to Dashboard</a>
    </div>

    <div class="panel">
        <h2>Category List</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($categories as $c) { ?>
                <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><?= e($c['category_name']) ?></td>
                    <td>
                        <a href="?edit=<?= (int)$c['id'] ?>">Edit</a>

                        <form method="post" style="display:inline"
                              onsubmit="return confirm('Delete category?')">
                            <input type="hidden" name="action"
                                   value="delete">
                            <input type="hidden" name="id"
                                   value="<?= (int)$c['id'] ?>">
                            <button class="danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</main>
</body>
</html>
