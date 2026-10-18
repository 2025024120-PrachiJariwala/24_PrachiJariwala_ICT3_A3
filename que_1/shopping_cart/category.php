
<?php
require 'db.php';
require 'functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    "SELECT * FROM categories WHERE id = ?"
);
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    exit("Category not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= e($category['category_name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Shopping Store</h2>
    <nav>
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>
    </nav>
</header>

<main>
    <h1><?= e($category['category_name']) ?></h1>

    <div id="products" class="grid">
        Loading products...
    </div>

    <div id="pagination"></div>
</main>

<script>
const categoryId = <?= $id ?>;

function escapeHTML(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

async function loadProducts(page = 1) {
    const productsDiv = document.getElementById('products');
    productsDiv.textContent = 'Loading products...';

    try {
        const response = await fetch(
            'products_ajax.php?category_id='
            + categoryId + '&page=' + page
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Unable to load products.');
        }

        productsDiv.innerHTML = '';

        if (data.products.length === 0) {
            productsDiv.textContent = 'No products available.';
        }

        data.products.forEach(product => {
            const card = document.createElement('div');
            card.className = 'product';

            const image = document.createElement('img');
            image.alt = product.pname;

            // if (product.picture) {
            //     image.src = product.picture;
            // }

            if (product.picture) {
                const filename = product.picture.trim().split(/[\\/]/).pop();
                image.src = 'uploads/' + encodeURIComponent(filename);
            } else {
                image.alt = 'No product image available';
            }

            const name = document.createElement('h3');
            name.textContent = product.pname;

            const price = document.createElement('p');
            price.textContent =
                '₹' + Number(product.price).toFixed(2);

            const link = document.createElement('a');
            link.className = 'btn';
            link.href = 'cart.php?add=' + Number(product.id);
            link.textContent = 'Add to Cart';

            card.append(image, name, price, link);
            productsDiv.appendChild(card);
        });

        const pagination =
            document.getElementById('pagination');

        pagination.innerHTML = '';

        for (let i = 1; i <= data.pages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.disabled = i === data.page;
            button.onclick = () => loadProducts(i);
            pagination.appendChild(button);
        }

    } catch (error) {
        productsDiv.textContent = error.message;
    }
}

loadProducts();
</script>

</body>
</html>
