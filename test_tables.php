<?php
header('Content-Type: text/plain');

require_once __DIR__ . '/inventory/inventaris-lab/config/config.php';
require_once __DIR__ . '/inventory/inventaris-lab/app/core/Database.php';

try {
    $db = new Database();
    echo "Tables in database:\n";
    $db->query("SHOW TABLES");
    $tables = $db->resultSet();
    foreach ($tables as $table) {
        print_r($table);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
