<?php
header('Content-Type: text/plain');

// Print before loading .env
echo "BEFORE loading .env:\n";
echo "DB_PASSWORD length in ENV: " . (isset($_ENV['DB_PASSWORD']) ? strlen($_ENV['DB_PASSWORD']) : 'not set') . "\n";
echo "DB_PASSWORD value (first 2 chars): " . (isset($_ENV['DB_PASSWORD']) ? substr($_ENV['DB_PASSWORD'], 0, 2) : 'not set') . "\n";

// Test Loading .env with createMutable
$env_loaded = false;
$env_path = __DIR__ . '/.env';
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($env_path) && file_exists($autoload)) {
    require_once $autoload;
    if (class_exists('Dotenv\\Dotenv')) {
        try {
            // Use createMutable to force overwrite existing env variables
            $dotenv = Dotenv\Dotenv::createMutable(__DIR__);
            $dotenv->safeLoad();
            $env_loaded = true;
        } catch (Throwable $e) {
            echo "Dotenv error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nAFTER loading .env (createMutable):\n";
echo "Env Loaded: " . ($env_loaded ? "YES" : "NO") . "\n";
echo "DB_HOST from ENV: '" . ($_ENV['DB_HOST'] ?? 'not set') . "'\n";
echo "DB_USERNAME from ENV: '" . ($_ENV['DB_USERNAME'] ?? 'not set') . "'\n";
echo "DB_PASSWORD length from ENV: " . (isset($_ENV['DB_PASSWORD']) ? strlen($_ENV['DB_PASSWORD']) : 'not set') . "\n";
echo "DB_PASSWORD value (first 2 chars): " . (isset($_ENV['DB_PASSWORD']) ? substr($_ENV['DB_PASSWORD'], 0, 2) : 'not set') . "\n";
echo "DB_DATABASE_APP from ENV: '" . ($_ENV['DB_DATABASE_APP'] ?? 'not set') . "'\n\n";

$host = $_ENV['DB_HOST'] ?? 'localhost';
$user = $_ENV['DB_USERNAME'] ?? 'fike8938_fikom_app';
$pass = $_ENV['DB_PASSWORD'] ?? 'fikom#12345';

$databases = [
    'fike8938_fikom_app',
    'fike8938_fikom_ruang',
    'fike8938_fikom_inventory',
    'fike8938_fikom_mou',
    'fike8938_fikom_surat'
];

mysqli_report(MYSQLI_REPORT_OFF);

foreach ($databases as $db) {
    echo "Connecting to database: $db (using user: $user, host: $host)...\n";
    $conn = mysqli_connect($host, $user, $pass, $db);
    if ($conn) {
        echo "✓ Success!\n";
        mysqli_close($conn);
    } else {
        echo "✗ Failed: " . mysqli_connect_error() . "\n";
    }
}
?>
