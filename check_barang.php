<?php
header('Content-Type: text/plain');

require_once __DIR__ . '/inventory/inventaris-lab/config/config.php';
require_once __DIR__ . '/inventory/inventaris-lab/app/core/Database.php';

$db = new Database();

echo "All items in 'barang' table:\n";
$db->query("SELECT * FROM barang");
$barang = $db->resultSet();
print_r($barang);

echo "\nLatest 10 logs in 'log_stok' table:\n";
$db->query("SELECT * FROM log_stok ORDER BY id_log DESC LIMIT 10");
$log_stok = $db->resultSet();
print_r($log_stok);

echo "\nLatest 10 logs in 'log_aktivitas' table:\n";
$db->query("SELECT * FROM log_aktivitas ORDER BY id_log DESC LIMIT 10");
$log_aktivitas = $db->resultSet();
print_r($log_aktivitas);
