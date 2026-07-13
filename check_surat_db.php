<?php
header('Content-Type: text/plain');

if (!isset($_ENV['DB_HOST']) && file_exists(__DIR__ . '/.env')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}

$db_host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$db_user = $_ENV['DB_USERNAME'] ?? 'root';
$db_pass = $_ENV['DB_PASSWORD'] ?? '';
$db_name_surat = $_ENV['DB_DATABASE_SURAT'] ?? 'fike8938_fikom_surat';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name_surat);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Database connected successfully to $db_name_surat.\n\n";

// Check table columns
$res = mysqli_query($conn, "DESCRIBE pengguna");
if (!$res) {
    echo "Failed to describe table 'pengguna': " . mysqli_error($conn) . "\n";
} else {
    echo "Columns of table 'pengguna':\n";
    while ($row = mysqli_fetch_assoc($res)) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    echo "\n";
}

// Check if any user with gmail or unika exists
echo "Users in database:\n";
$res_users = mysqli_query($conn, "SELECT id, email, nama_lengkap, deleted_at, status FROM pengguna LIMIT 20");
if (!$res_users) {
    echo "Failed to select users: " . mysqli_error($conn) . "\n";
} else {
    while ($row = mysqli_fetch_assoc($res_users)) {
        echo "ID: " . $row['id'] . " | Email: " . $row['email'] . " | Name: " . $row['nama_lengkap'] . " | Deleted: " . ($row['deleted_at'] ?? 'NULL') . " | Status: " . $row['status'] . "\n";
    }
}
