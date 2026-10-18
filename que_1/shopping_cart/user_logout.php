<?php
require_once 'functions.php';

unset(
    $_SESSION['user_id'],
    $_SESSION['user_name']
);

header("Location: index.php");
exit;
?>