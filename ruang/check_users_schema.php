<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
try {
    $stmt = $db->query("DESCRIBE users");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
