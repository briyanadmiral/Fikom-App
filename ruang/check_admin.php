<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$email = 'briyanadmiral@gmail.com';
try {
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        echo "User found: " . json_encode($user);
    } else {
        echo "User NOT found for email: $email";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
