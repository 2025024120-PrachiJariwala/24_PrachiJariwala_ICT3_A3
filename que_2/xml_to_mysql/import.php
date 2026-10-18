
<?php
require 'db.php';

$file = __DIR__ . '/students.xml';

if (!file_exists($file)) {
    die("students.xml file not found.");
}

// $xml = simplexml_load_file($file);

// if ($xml === false) {
//     die("Invalid XML file.");
// }

libxml_use_internal_errors(true);

$xml = simplexml_load_file($file);

if ($xml === false) {
    echo "Invalid XML file:<br>";

    foreach (libxml_get_errors() as $error) {
        echo htmlspecialchars(
            trim($error->message),
            ENT_QUOTES,
            'UTF-8'
        ) . "<br>";
    }

    libxml_clear_errors();
    exit;
}

$sql = "INSERT INTO students (roll_no, name, city)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        city = VALUES(city)";

$stmt = $pdo->prepare($sql);
$count = 0;

foreach ($xml->student as $student) {
    $roll = trim((string)$student->roll_no);
    $name = trim((string)$student->name);
    $city = trim((string)$student->city);

    if ($roll !== '' && $name !== '' && $city !== '') {
        $stmt->execute([$roll, $name, $city]);
        $count++;
    }
}

echo "<h2>XML to MySQL Import</h2>";
echo "<p>$count student records imported or updated.</p>";
?>
