<?php
header("Content-Type: application/json");

require_once "db.php";

try {
    $sql = "SELECT id, name, city FROM students";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $students
    ]);
} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to fetch students"
    ]);
}
?>