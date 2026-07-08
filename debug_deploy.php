<?php
header('Content-Type: text/html; charset=utf-8');
// Load config.php to trigger Dotenv loading
if (file_exists("config.php")) {
    include "config.php";
}

echo "<div style='font-family: sans-serif; padding: 20px; max-width: 600px; margin: 50px auto; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>";
echo "<h2 style='color: #2c3e50;'>🚀 Deploy & Database Environment Diagnostic</h2>";

// Cek filemtime login.php
if (file_exists("login.php")) {
    echo "<p><strong>login.php Last Modified:</strong> " . date("Y-m-d H:i:s", filemtime("login.php")) . "</p>";
}

// Cek .env
if (file_exists(".env")) {
    echo "<p style='color: green; font-weight: bold;'>✓ .env file exists in root directory</p>";
    $env_content = file_get_contents(".env");
    echo "<p><strong>.env size:</strong> " . strlen($env_content) . " bytes</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ .env file DOES NOT exist in root directory!</p>";
}

// Debug environment variables
echo "<h3>Database Settings loaded in PHP:</h3>";

$db_host = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : (isset($_SERVER['DB_HOST']) ? $_SERVER['DB_HOST'] : 'NOT SET');
$db_user = isset($_ENV['DB_USERNAME']) ? $_ENV['DB_USERNAME'] : (isset($_SERVER['DB_USERNAME']) ? $_SERVER['DB_USERNAME'] : 'NOT SET');
$db_pass_status = isset($_ENV['DB_PASSWORD']) ? 'SET (length: ' . strlen($_ENV['DB_PASSWORD']) . ')' : (isset($_SERVER['DB_PASSWORD']) ? 'SET IN SERVER' : 'NOT SET');
$db_name = isset($_ENV['DB_DATABASE']) ? $_ENV['DB_DATABASE'] : (isset($_SERVER['DB_DATABASE']) ? $_SERVER['DB_DATABASE'] : 'NOT SET');

echo "<ul>";
echo "<li><strong>DB_HOST:</strong> " . htmlspecialchars($db_host) . "</li>";
echo "<li><strong>DB_USERNAME:</strong> " . htmlspecialchars($db_user) . "</li>";
echo "<li><strong>DB_PASSWORD:</strong> " . htmlspecialchars($db_pass_status) . "</li>";
echo "<li><strong>DB_DATABASE:</strong> " . htmlspecialchars($db_name) . "</li>";
echo "</ul>";

// Cek koneksi manual dengan parameter ini
echo "<h3>Test Connection using detected settings:</h3>";
if ($db_host !== 'NOT SET') {
    try {
        $conn_test = @mysqli_connect($db_host, $db_user, isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : '', $db_name);
        if ($conn_test) {
            echo "<p style='color: green; font-weight: bold;'>✓ Connection Succeeded!</p>";
            mysqli_close($conn_test);
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ Connection Failed: " . mysqli_connect_error() . "</p>";
        }
    } catch (Throwable $e) {
        echo "<p style='color: red; font-weight: bold;'>✗ Connection Threw Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: orange;'>Cannot test connection: DB settings are not loaded.</p>";
}

echo "</div>";
