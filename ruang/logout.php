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
if (isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
    header("Location: http://localhost/fikomapp/superadmin/superadmin_home.php");
} else {
    header("Location: http://localhost/fikomapp/index.php");
}
exit;
?>