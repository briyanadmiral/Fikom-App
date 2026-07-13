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

// Let's attempt the INSERT query that surat.php does:
$dummy_hash = password_hash('bypass123', PASSWORD_BCRYPT);
$email = 'briyanadmiral@gmail.com';

echo "Attempting to insert test user ($email)...\n";
$sql = "INSERT INTO pengguna (email, sandi_hash, nama_lengkap, jabatan, peran_id, status, created_at, updated_at) 
        VALUES ('$email', '$dummy_hash', 'Super Admin (Bypass)', 'Superadmin FIKOM', 1, 'aktif', NOW(), NOW())";

$res = mysqli_query($conn, $sql);

if ($res) {
    $inserted_id = mysqli_insert_id($conn);
    echo "[✅] INSERT Successful! Inserted ID: $inserted_id\n";
    
    // Delete it so we don't pollute the DB
    mysqli_query($conn, "DELETE FROM pengguna WHERE id = $inserted_id");
    echo "Deleted test user.\n";
} else {
    echo "[❌] INSERT Failed!\n";
    echo "Error Code: " . mysqli_errno($conn) . "\n";
    echo "Error Message: " . mysqli_error($conn) . "\n";
}
