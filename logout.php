<?php 

session_start();

session_unset();
session_destroy();

$error = isset($_GET['error']) ? '?error=' . urlencode($_GET['error']) : '';
header('Location: login.php' . $error);
exit();
