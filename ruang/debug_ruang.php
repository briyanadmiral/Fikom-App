<?php
// Debug script - HAPUS SETELAH SELESAI DEBUG
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "<pre>";
echo "=== DEBUG RUANG MODULE ===\n\n";

// 1. Test DB connection
try {
    $db_obj = new Database();
    $db = $db_obj->getConnection();
    if ($db) {
        echo "✅ DB Connected OK\n\n";
        
        // 2. Check users table structure
        echo "=== USERS TABLE STRUCTURE ===\n";
        $stmt = $db->query('DESCRIBE users');
        $cols = $stmt->fetchAll();
        foreach($cols as $c) {
            echo "  " . $c['Field'] . " | " . $c['Type'] . " | " . $c['Null'] . " | " . $c['Default'] . "\n";
        }
        echo "\n";
        
        // 3. Check if superadmin user exists in ruang users table
        echo "=== CHECKING USERS ===\n";
        $emails = ['briyanadmiral@gmail.com', 'magang.si@unika.ac.id'];
        foreach($emails as $email) {
            $stmt = $db->prepare("SELECT id, email, nama, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                echo "✅ Found: " . json_encode($user) . "\n";
            } else {
                echo "❌ NOT FOUND: $email\n";
            }
        }
        echo "\n";
        
    } else {
        echo "❌ DB Connection FAILED\n";
    }
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

// 4. Check session state
echo "=== SESSION STATE ===\n";
startSession();
echo "Session data: " . print_r($_SESSION, true) . "\n";

// 5. Test getUserInfo
echo "=== getUserInfo() RESULT ===\n";
$info = getUserInfo();
echo print_r($info, true) . "\n";

// 6. Test checkSessionRole
echo "=== checkSessionRole(['admin']) RESULT ===\n";
$role = checkSessionRole(['admin']);
echo "Result: " . var_export($role, true) . "\n";

echo "</pre>";
?>
