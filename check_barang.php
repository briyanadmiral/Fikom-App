<?php
header('Content-Type: text/plain');

try {
    require_once __DIR__ . '/inventory/inventaris-lab/config/config.php';
    require_once __DIR__ . '/inventory/inventaris-lab/app/core/Database.php';

    $db = new Database();

    echo "All items in 'barang' table:\n";
    $db->query("SELECT * FROM barang");
    $barang = $db->resultSet();
    print_r($barang);

    echo "\nAll items in 'jenis_barang' table:\n";
    $db->query("SELECT * FROM jenis_barang");
    $jenis = $db->resultSet();
    print_r($jenis);
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}
