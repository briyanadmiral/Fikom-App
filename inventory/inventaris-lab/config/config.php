<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$pos = strpos($script, '/inventory/inventaris-lab/public');
$basePath = ($pos !== false) ? substr($script, 0, $pos) : '';
define('BASE_URL', $protocol . $host . $basePath . '/inventory/inventaris-lab/public');


// Konfigurasi Database
if (!isset($_ENV['DB_HOST']) && file_exists(__DIR__ . '/../../../.env')) {
    if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../../vendor/autoload.php';
        if (class_exists('Dotenv\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
            $dotenv->safeLoad();
        }
    }
}

define('DB_HOST', 'localhost');
define('DB_USER', 'fike8938_fikom_app');
define('DB_PASS', 'fikom#12345');
define('DB_NAME', 'fike8938_fikom_inventory');