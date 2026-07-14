<?php
// ruang/dev_login.php - Dev Bypass Login
require_once 'config/database.php';
startSession();

$role = $_GET['role'] ?? 'mahasiswa';

if ($role === 'admin') {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_email'] = 'admin@fikom.univ.ac.id';
    $_SESSION['user_name'] = 'Administrator FIKOM';
    $_SESSION['role'] = 'admin';
    $_SESSION['admin'] = true;
    unset($_SESSION['users']);
    echo "Logged in as Admin. Redirecting...";
    echo "<script>window.location.href = 'admin/dashboard.php';</script>";
} else {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_email'] = '23g40011@student.unika.ac.id';
    $_SESSION['user_name'] = 'BRIYAN ADMIRAL YOEL KRISNA WID';
    $_SESSION['role'] = 'mahasiswa';
    $_SESSION['users'] = true;
    $_SESSION['nim'] = '23g40011';
    $_SESSION['program'] = 'siega';
    unset($_SESSION['admin']);
    echo "Logged in as Student. Redirecting...";
    echo "<script>window.location.href = 'users/pengajuan.php';</script>";
}
