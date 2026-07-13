<?php

// Jika $_ENV belum dimuat (misalnya saat file ini dipanggil langsung tanpa config.php)
if (!isset($_ENV['DB_HOST']) && file_exists(__DIR__ . '/.env')) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        if (class_exists('Dotenv\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->safeLoad();
        }
    }
}

$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_user = $_ENV['DB_USERNAME'] ?? 'root';
$db_pass = $_ENV['DB_PASSWORD'] ?? '';
$db_name = $_ENV['DB_DATABASE_APP'] ?? 'fike8938_fikom_app';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die('koneksi gagal: ' . mysqli_connect_error());

?>