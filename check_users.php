<?php
header('Content-Type: text/plain');

require_once __DIR__ . '/inventory/inventaris-lab/config/config.php';
require_once __DIR__ . '/inventory/inventaris-lab/app/core/Database.php';

$db = new Database();

echo "All users in inventory database:\n";
$db->query("SELECT * FROM users");
$users = $db->resultSet();
print_r($users);
