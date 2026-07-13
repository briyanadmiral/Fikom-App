<?php
header('Content-Type: text/plain');

echo "=== Laravel Environment Diagnostic ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current File: " . __FILE__ . "\n";
echo "Current Directory: " . __DIR__ . "\n\n";

// 1. Check Directory Permissions
$dirs_to_check = [
    __DIR__ . '/../storage',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../storage/framework',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../storage/framework/cache',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../bootstrap/cache'
];

echo "--- Checking Directory Permissions ---\n";
foreach ($dirs_to_check as $dir) {
    if (!file_exists($dir)) {
        echo "[❌] Directory does not exist: $dir\n";
    } else {
        $writable = is_writable($dir);
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo ($writable ? "[✅]" : "[❌]") . " " . basename($dir) . " ($perms) - " . ($writable ? "Writable" : "NOT Writable") . "\n";
    }
}
echo "\n";

// 2. Check .env file
$env_file = __DIR__ . '/../.env';
echo "--- Checking .env file ---\n";
if (file_exists($env_file)) {
    echo "[✅] .env file exists.\n";
    // Load .env
    $lines = file($env_file);
    $db_vars = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (strpos($key, 'DB_') === 0 || $key === 'APP_ENV' || $key === 'APP_DEBUG' || $key === 'APP_URL') {
                $db_vars[$key] = $val;
            }
        }
    }
    
    foreach ($db_vars as $k => $v) {
        if ($k === 'DB_PASSWORD') {
            echo "$k = [HIDDEN (" . strlen($v) . " chars)]\n";
        } else {
            echo "$k = $v\n";
        }
    }
    
    // 3. Test database connection
    echo "\n--- Testing Database Connection using .env credentials ---\n";
    $db_host = $db_vars['DB_HOST'] ?? 'localhost';
    $db_port = $db_vars['DB_PORT'] ?? '3306';
    $db_name = $db_vars['DB_DATABASE'] ?? '';
    $db_user = $db_vars['DB_USERNAME'] ?? '';
    $db_pass = $db_vars['DB_PASSWORD'] ?? '';
    
    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        echo "[✅] PDO Connection Successful!\n";
        
        // Query users table count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM pengguna");
        $row = $stmt->fetch();
        echo "[✅] Query t_pengguna/pengguna successful. Total users: " . $row['count'] . "\n";
    } catch (PDOException $e) {
        echo "[❌] PDO Connection Failed: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "[❌] .env file does not exist at: $env_file\n";
}
echo "\n";

// 4. Check PHP Extensions
echo "--- Checking Required PHP Extensions ---\n";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'xml', 'ctype', 'bcmath', 'fileinfo'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo ($loaded ? "[✅]" : "[❌]") . " Extension '$ext' " . ($loaded ? "is loaded" : "is NOT loaded") . "\n";
}
