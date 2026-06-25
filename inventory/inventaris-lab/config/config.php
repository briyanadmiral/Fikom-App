<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$pos = strpos($script, '/inventory/inventaris-lab/public');
$basePath = ($pos !== false) ? substr($script, 0, $pos) : '';
define('BASE_URL', $protocol . $host . $basePath . '/inventory/inventaris-lab/public');


// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', '');     // Ganti dengan password database Anda
define('DB_NAME', 'fike8938_fikom_inventory'); // Ganti dengan nama database Anda