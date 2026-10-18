<?php
require_once "functions.php";
require_admin();

require_once "db.php";

$message = "";
$editProduct = null;

// Fetch categories
$stmt = $conn->query(
    "SELECT id, category_name FROM categories ORDER BY category_name"
);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle image upload
function saveProductImage($file)
{
    if (
        !isset($file) ||
        $file["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return ["picture" => "", "error" => ""];
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["picture" => "", "error" => "Image upload failed."];
    }

    if ($file["size"] > 2 * 1024 * 1024) {
        return [
            "picture" => "",
            "error" => "Image must be under 2 MB."
        ];
    }

    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($file["tmp_name"]);

    if (!isset($allowedTypes[$mimeType])) {
        return [
            "picture" => "",
            "error" => "Upload a JPG, PNG, or WEBP image."
        ];
    }

    $uploadDir = __DIR__ . "/uploads/";

    if (
        !is_dir($uploadDir) &&
        !mkdir($uploadDir, 0755, true) &&
        !is_dir($uploadDir)
    ) {
        return [
            "picture" => "",
            "error" => "Unable to create upload directory."
        ];
    }

    $filename = bin2hex(random_bytes(16))
        . "." . $allowedTypes[$mimeType];

    if (!move_uploaded_file(
        $file["tmp_name"],
        $uploadDir . $filename
    )) {
        return [
            "picture" => "",
            "error" => "Unable to save the image."
        ];
    }

    return ["picture" => $filename, "error" => ""];
}

// Delete product
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete"
) {
    $id = filter_var(
        $_POST["id"] ?? "",
        FILTER_VALIDATE_INT
    );

    if (!$id || $id <= 0) {
        $message = "Invalid product ID.";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT picture FROM products WHERE id = ?"
            );
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $message = "Product not found.";
            } else {
                $stmt = $conn->prepare(
                    "DELETE FROM products WHERE id = ?"
                );
                $stmt->execute([$id]);

                $oldPicture = basename(
                    trim($product["picture"] ?? "")
                );

                if (
                    $oldPicture !== "" &&
                    is_file(__DIR__ . "/uploads/" . $oldPicture)
                ) {
                    unlink(__DIR__ . "/uploads/" . $oldPicture);
                }

                $message = "Product deleted successfully!";
            }
        } catch (PDOException $e) {
            $message = "Unable to delete product.";
        }
    }
}

// Add or update product
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    in_array(
        $_POST["action"] ?? "",
        ["add", "update"],
        true
    )
) {
    $action = $_POST["action"];
    $pname = trim($_POST["pname"] ?? "");
    $price = $_POST["price"] ?? "";
    $categoryId = $_POST["category_id"] ?? "";

    $id = filter_var(
        $_POST["id"] ?? "",
        FILTER_VALIDATE_INT
    );

    if (
        $pname === "" ||
        !is_numeric($price) ||
        (float)$price < 0 ||
        !filter_var($categoryId, FILTER_VALIDATE_INT) ||
        (int)$categoryId <= 0
    ) {
        $message = "Please enter valid product details.";
    } elseif (
        $action === "update" &&
        (!$id || $id <= 0)
    ) {
        $message = "Invalid product ID.";
    } else {
        // Verify that the category exists
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM categories WHERE id = ?"
        );
        $stmt->execute([(int)$categoryId]);

        if (!$stmt->fetchColumn()) {
            $message = "Please select a valid category.";
        } else {
            $oldPicture = "";

            if ($action === "update") {
                $stmt = $conn->prepare(
                    "SELECT picture FROM products WHERE id = ?"
                );
                $stmt->execute([$id]);
                $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingProduct) {
                    $message = "Product not found.";
                } else {
                    $oldPicture = $existingProduct["picture"] ?? "";
                }
            }

            if ($message === "") {
                $upload = saveProductImage(
                    $_FILES["picture"] ?? null
                );

                $picture = $upload["picture"];
                $message = $upload["error"];

                // An image is required when adding a product.
                if (
                    $action === "add" &&
                    $picture === "" &&
                    $message === ""
                ) {
                    $message = "Please select a product image.";
                }

                if ($message === "") {
                    try {
                        if ($action === "add") {
                            $stmt = $conn->prepare(
                                "INSERT INTO products
                                (category_id, pname, price, picture)
                                VALUES (?, ?, ?, ?)"
                            );

                            $stmt->execute([
                                (int)$categoryId,
                                $pname,
                                (float)$price,
                                $picture
                            ]);

                            $message = "Product added successfully!";
                        } else {
                            // Keep the existing image if no new image
                            // was uploaded.
                            $finalPicture = $picture !== ""
                                ? $picture
                                : $oldPicture;

                            $stmt = $conn->prepare(
                                "UPDATE products
                                SET category_id = ?,
                                    pname = ?,
                                    price = ?,
                                    picture = ?
                                WHERE id = ?"
                            );

                            $stmt->execute([
                                (int)$categoryId,
                                $pname,
                                (float)$price,
                                $finalPicture,
                                $id
                            ]);

                            // Remove old image after successful update
                            if (
                                $picture !== "" &&
                                $oldPicture !== ""
                            ) {
                                $oldFilename = basename(
                                    trim($oldPicture)
                                );

                                if (
                                    is_file(
                                        __DIR__ . "/uploads/" .
                                        $oldFilename
                                    )
                                ) {
                                    unlink(
                                        __DIR__ . "/uploads/" .
                                        $oldFilename
                                    );
                                }
                            }

                            $message = "Product updated successfully!";
                        }
                    } catch (PDOException $e) {
                        // Remove newly uploaded image if saving fails
                        if ($picture !== "") {
                            $newImagePath =
                                __DIR__ . "/uploads/" . $picture;

                            if (is_file($newImagePath)) {
                                unlink($newImagePath);
                            }
                        }

                        $message = "Unable to save product. Please check your database.";
                    }
                }
            }
        }
    }
}

// Load product for editing
$editId = filter_var(
    $_GET["edit"] ?? "",
    FILTER_VALIDATE_INT
);

if ($editId && $editId > 0) {
    $stmt = $conn->prepare(
        "SELECT * FROM products WHERE id = ?"
    );
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$editProduct) {
        $message = "Product not found.";
    }
}

// Fetch all products
$stmt = $conn->query(
    "SELECT p.*, c.category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     ORDER BY p.id DESC"
);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Shopping Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>My Shopping Store</h2>
    <nav>
        <a href="index.php">Home</a>
        <a href="admin.php">Admin Dashboard</a>
        <a href="product_manage.php">Manage Products</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>

    <!-- Add / Edit Form -->
    <div class="panel">
        <h1>
            <?= $editProduct ? "Edit Product" : "Add New Product" ?>
        </h1>

        <p>
            <?= $editProduct
                ? "Update the product details below."
                : "Enter the details to add a new product." ?>
        </p>

        <?php if ($message !== "") { ?>
            <p class="msg">
                <?= e($message) ?>
            </p>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">

            <input
                type="hidden"
                name="action"
                value="<?= $editProduct ? "update" : "add" ?>"
            >

            <?php if ($editProduct) { ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$editProduct["id"] ?>"
                >
            <?php } ?>

            <label for="pname">Product Name</label>
            <input
                type="text"
                id="pname"
                name="pname"
                placeholder="Enter product name"
                value="<?= e($editProduct["pname"] ?? "") ?>"
                required
            >

            <label for="price">Price (₹)</label>
            <input
                type="number"
                id="price"
                name="price"
                min="0"
                step="0.01"
                placeholder="Enter product price"
                value="<?= e($editProduct["price"] ?? "") ?>"
                required
            >

            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>

                <?php foreach ($categories as $category) { ?>
                    <option
                        value="<?= (int)$category["id"] ?>"
                        <?= (
                            (string)($editProduct["category_id"] ?? "") ===
                            (string)$category["id"]
                        ) ? "selected" : "" ?>
                    >
                        <?= e($category["category_name"]) ?>
                    </option>
                <?php } ?>
            </select>

            <?php if ($editProduct && !empty($editProduct["picture"])) { ?>
                <p>Current Product Image:</p>
                <img
                    src="uploads/<?= rawurlencode(
                        basename(trim($editProduct["picture"]))
                    ) ?>"
                    alt="Current product image"
                    style="width:120px;height:100px;object-fit:contain;"
                >
                <p><small>Select a new image only if you want to replace it.</small></p>
            <?php } ?>

            <label for="picture">
                Product Image
                <?= $editProduct ? "(Optional)" : "" ?>
            </label>

            <input
                type="file"
                id="picture"
                name="picture"
                accept=".jpg,.jpeg,.png,.webp"
                <?= $editProduct ? "" : "required" ?>
            >

            <button type="submit">
                <?= $editProduct ? "Update Product" : "Add Product" ?>
            </button>

            <?php if ($editProduct) { ?>
                <a href="product_manage.php" class="btn">
                    Cancel Edit
                </a>
            <?php } ?>

            <a href="admin.php" class="btn">Back to Dashboard</a>
        </form>
    </div>

    <!-- Product List -->
    <h1>Manage Products</h1>

    <?php if (count($products) > 0) { ?>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($products as $product) { ?>
                        <tr>
                            <td><?= (int)$product["id"] ?></td>

                            <td>
                                <?php if (!empty(trim($product["picture"] ?? ""))) { ?>
                                    <img
                                        src="uploads/<?= rawurlencode(
                                            basename(trim($product["picture"]))
                                        ) ?>"
                                        alt="<?= e($product["pname"]) ?>"
                                        style="width:65px;height:55px;object-fit:contain;"
                                    >
                                <?php } else { ?>
                                    No image
                                <?php } ?>
                            </td>

                            <td><?= e($product["pname"]) ?></td>

                            <td>
                                <?= e($product["category_name"] ?? "Uncategorized") ?>
                            </td>

                            <td>
                                ₹<?= number_format((float)$product["price"], 2) ?>
                            </td>

                            <td>
                                <a
                                    href="product_manage.php?edit=<?= (int)$product["id"] ?>"
                                    class="btn"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this product?');"
                                    style="display:inline;"
                                >
                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$product["id"] ?>"
                                    >

                                    <button type="submit" class="danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    <?php } else { ?>

        <div class="panel">
            <p>No products available. Add your first product above.</p>
        </div>

    <?php } ?>

</main>

</body>
</html>
