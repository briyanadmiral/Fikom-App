<?php
/**
 * koneksi.php — Wrapper kompatibilitas mundur
 * Semua file yang pakai include 'koneksi.php' akan otomatis
 * menggunakan db.php terpusat.
 */

// Tentukan root jika belum didefinisikan
if (!defined('FIKOM_ROOT')) {
    define('FIKOM_ROOT', __DIR__);
}

require_once __DIR__ . '/db.php';

// $conn sudah di-set oleh db.php (koneksi ke 'app')
// $db_host, $db_user, $db_pass juga sudah tersedia
?>