<?php
require_once 'functions.php';

unset(
    $_SESSION['admin_id'],
    $_SESSION['admin_username']
);

header("Location: index.php");
exit;
?>