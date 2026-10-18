<!-- This file contains session checks, HTML escaping, and the image-upload/resizing functions. -->


<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function require_admin() {
    if (empty($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}

function upload_product_picture($file) {
    if (empty($file['name'])) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Image upload failed.");
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Image must be under 5 MB.");
    }

    $info = getimagesize($file['tmp_name']);

    if (!$info) {
        throw new Exception("Invalid image file.");
    }

    $mime = $info['mime'];

    $types = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif'
    ];

    if (!isset($types[$mime])) {
        throw new Exception("Only JPG, PNG and GIF are allowed.");
    }

    if (!function_exists('imagecreatetruecolor')) {
        throw new Exception("Enable the PHP GD extension.");
    }

    $source = null;

    if ($mime === 'image/jpeg') {
        $source = imagecreatefromjpeg($file['tmp_name']);
    } elseif ($mime === 'image/png') {
        $source = imagecreatefrompng($file['tmp_name']);
    } elseif ($mime === 'image/gif') {
        $source = imagecreatefromgif($file['tmp_name']);
    }

    if (!$source) {
        throw new Exception("Cannot read image.");
    }

    $width = imagesx($source);
    $height = imagesy($source);

    // Resize to maximum 600 x 600 without enlarging.
    $scale = min(600 / $width, 600 / $height, 1);
    $newWidth = max(1, (int)($width * $scale));
    $newHeight = max(1, (int)($height * $scale));

    $destination = imagecreatetruecolor(
        $newWidth,
        $newHeight
    );

    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);

        $transparent = imagecolorallocatealpha(
            $destination, 0, 0, 0, 127
        );

        imagefilledrectangle(
            $destination, 0, 0,
            $newWidth, $newHeight, $transparent
        );
    }

    imagecopyresampled(
        $destination, $source,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $width, $height
    );

    $folder = __DIR__ . "/uploads/";

    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }

    $filename = bin2hex(random_bytes(12))
              . "." . $types[$mime];

    $path = $folder . $filename;

    if ($mime === 'image/jpeg') {
        imagejpeg($destination, $path, 85);
    } elseif ($mime === 'image/png') {
        imagepng($destination, $path, 6);
    } else {
        imagegif($destination, $path);
    }

    imagedestroy($source);
    imagedestroy($destination);

    return "uploads/" . $filename;
}
?>
