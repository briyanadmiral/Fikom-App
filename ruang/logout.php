<?php
// logout.php - Keluar kembali ke portal utama
require_once 'config/database.php';

startSession();

// Log activity sebelum keluar
$user_info = getUserInfo();
if ($user_info['user_id']) {
    logActivity($user_info['user_id'], 'Exit Module', 'User kembali ke dashboard utama', null);
}

// Hanya hapus session spesifik modul ruangan (tanpa men-destroy session utama FikomApp)
unset($_SESSION['admin']);
unset($_SESSION['users']);

// Redirect ke main page FIKOMAPP
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$pos = strpos($script, '/ruang/logout.php');
$basePath = ($pos !== false) ? substr($script, 0, $pos) : '';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
    header("Location: " . $protocol . $host . $basePath . "/superadmin/superadmin_home.php");
} else {
    header("Location: " . $protocol . $host . $basePath . "/index.php");
}
exit;
?>