<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['logged_in'] = true;
$_SESSION['role'] = 'superadmin';
$_SESSION['user_name'] = 'Test';
$_SESSION['user_picture'] = 'test.jpg';

chdir(__DIR__ . '/superadmin');
include 'superadmin_home.php';
?>
