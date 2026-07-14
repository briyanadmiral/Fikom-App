<?php
session_start();
$_SESSION['logged_in'] = true;
$_SESSION['role'] = 'dosen';
$_SESSION['user_email'] = 'testdosen@unika.ac.id';
$_SESSION['user_name'] = 'Dosen Test';
$_SESSION['user_picture'] = 'assets/img/default-avatar.png';
header('Location: index.php');
exit;
