
<?php
require 'db.php';

$stmt = $pdo->query(
    "SELECT roll_no, name, city
     FROM students
     ORDER BY roll_no"
);

$students = $stmt->fetchAll();

$xml = new XMLWriter();

$xml->openMemory();
$xml->startDocument('1.0', 'UTF-8');
$xml->setIndent(true);

$xml->startElement('students');

foreach ($students as $student) {
    $xml->startElement('student');

    $xml->writeElement('roll_no', $student['roll_no']);
    $xml->writeElement('name', $student['name']);
    $xml->writeElement('city', $student['city']);

    $xml->endElement();
}

$xml->endElement();
$xml->endDocument();

$content = $xml->outputMemory();

$file = __DIR__ . '/students_export.xml';

if (file_put_contents($file, $content) === false) {
    die("Unable to write XML file.");
}

echo "<h2>MySQL to XML Export</h2>";
echo "<p>" . count($students) . " records exported.</p>";
echo "<p>Saved file: students_export.xml</p>";
echo "<a href='students_export.xml' download>";
echo "Download XML";
echo "</a>";

echo "<h3>XML Preview</h3>";
echo "<pre>";
echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
echo "</pre>";
?>
