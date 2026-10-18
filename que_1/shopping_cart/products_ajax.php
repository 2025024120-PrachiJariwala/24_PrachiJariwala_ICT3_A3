
<?php
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

$categoryId = (int)($_GET['category_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 4;

try {
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM products WHERE category_id = ?"
    );
    $stmt->execute([$categoryId]);

    $total = (int)$stmt->fetchColumn();
    $pages = max(1, (int)ceil($total / $limit));

    $page = min($page, $pages);
    $offset = ($page - 1) * $limit;

    $sql = "SELECT id, pname, price, picture
            FROM products
            WHERE category_id = ?
            ORDER BY id DESC
            LIMIT $limit OFFSET $offset";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$categoryId]);

    echo json_encode([
        'products' => $stmt->fetchAll(),
        'page' => $page,
        'pages' => $pages
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'error' => 'Unable to load products.'
    ]);
}
?>
