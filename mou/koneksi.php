<?php
/**
 * mou/koneksi.php — Koneksi database untuk modul MOU
 * Menggunakan mysqli via fikom_db() dari db.php terpusat.
 */

if (!defined('FIKOM_ROOT')) {
    define('FIKOM_ROOT', __DIR__ . '/..');
}

require_once __DIR__ . '/../db.php';

// Variabel $conn diarahkan ke database MOU
$conn = fikom_db('mou');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['mou_status']) && $_SESSION['mou_status'] === 'pending') {
    $current_file = basename($_SERVER['PHP_SELF']);
    if ($current_file !== 'waiting_room.php') {
        header("Location: waiting_room.php");
        exit;
    }
}

// Alias variabel untuk kompatibilitas file-file lama di folder mou/
$host = 'localhost';
$user = 'fike8938_fikom_app';
$pass = 'fikom#12345';
$db   = 'fike8938_fikom_mou';
?>
