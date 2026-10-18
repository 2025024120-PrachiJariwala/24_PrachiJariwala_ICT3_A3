
<?php
$directory = __DIR__ . '/files';

if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
}

$files = array_diff(
    scandir($directory),
    ['.', '..']
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Directory File Listing</title>
</head>
<body>

<h1>Files in Directory</h1>

<p>
    Directory:
    <?= htmlspecialchars($directory, ENT_QUOTES, 'UTF-8') ?>
</p>

<ul>
    <?php if (empty($files)) { ?>
        <li>No files found.</li>
    <?php } ?>

    <?php foreach ($files as $file) { ?>
        <li>
            <?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>

            <?php
            $path = $directory . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                echo " (Directory)";
            } else {
                echo " (" . filesize($path) . " bytes)";
            }
            ?>
        </li>
    <?php } ?>
</ul>

</body>
</html>
