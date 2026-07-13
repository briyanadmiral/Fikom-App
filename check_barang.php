<?php
header('Content-Type: text/plain');

// Panggil file konfigurasi
require_once __DIR__ . '/inventory/inventaris-lab/config/config.php';
require_once __DIR__ . '/inventory/inventaris-lab/app/core/Database.php';

if (!session_id()) {
    session_start();
}

$db = new Database();

echo "Logged in user session:\n";
print_r($_SESSION['app_user'] ?? 'No app_user in session');

echo "\nAll items in 'barang' table:\n";
$db->query("SELECT * FROM barang");
$barang = $db->resultSet();
print_r($barang);

echo "\nAll items in 'jenis_barang' table:\n";
$db->query("SELECT * FROM jenis_barang");
$jenis = $db->resultSet();
print_r($jenis);
