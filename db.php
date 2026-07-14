<?php
/**
 * ============================================================
 *  db.php — Manajer Koneksi Database Terpusat FIKOM APP
 * ============================================================
 *  File ini adalah SATU-SATUNYA sumber koneksi database.
 *  Semua file PHP lain harus menggunakan require_once 'db.php'
 *  (atau require_once '../db.php' jika di subfolder).
 *
 *  Variabel yang tersedia setelah include:
 *    $db_host    — host database
 *    $db_user    — username database
 *    $db_pass    — password database
 *
 *  Fungsi yang tersedia:
 *    fikom_db($db_key)  — membuat/mengembalikan koneksi cached
 *
 *  Contoh pemakaian:
 *    $conn       = fikom_db('app');        // DB utama fike8938_fikom_app
 *    $conn_inv   = fikom_db('inventory');  // fike8938_fikom_inventory
 *    $conn_ruang = fikom_db('ruang');      // fike8938_fikom_ruang
 *    $conn_surat = fikom_db('surat');      // fike8938_fikom_surat
 *    $conn_mou   = fikom_db('mou');        // fike8938_fikom_mou (via mou/koneksi.php)
 * ============================================================
 */

// ── 1. LOAD .ENV ─────────────────────────────────────────────
// Cari root directory (tempat .env berada).
// Dari subfolder (misal /superadmin/), naik satu level ke atas.
$_fikom_root = (defined('FIKOM_ROOT')) ? FIKOM_ROOT : __DIR__;

if (!isset($_ENV['DB_HOST'])) {
    $env_path = $_fikom_root . '/.env';
    $autoload = $_fikom_root . '/vendor/autoload.php';

    if (file_exists($env_path) && file_exists($autoload)) {
        require_once $autoload;
        if (class_exists('Dotenv\\Dotenv')) {
            try {
                $dotenv = Dotenv\Dotenv::createImmutable($_fikom_root);
                $dotenv->safeLoad();
            } catch (Throwable $e) {
                error_log('[db.php] Dotenv error: ' . $e->getMessage());
            }
        }
    }
}

// ── 2. KREDENSIAL GLOBAL ──────────────────────────────────────
$db_host = 'localhost';
$db_user = 'fike8938_fikom_app';
$db_pass = 'fikom#12345';

// Peta nama kunci → nama database
$_fikom_db_map = [
    'app' => 'fike8938_fikom_app',
    'inventory' => 'fike8938_fikom_inventory',
    'ruang' => 'fike8938_fikom_ruang',
    'surat' => 'fike8938_fikom_surat',
    'mou' => 'fike8938_fikom_mou',
];

// Cache koneksi (singleton per database)
$_fikom_db_cache = [];

// ── 3. FUNGSI UTAMA ───────────────────────────────────────────
/**
 * Mengembalikan koneksi mysqli ke database yang diminta.
 * Koneksi di-cache sehingga satu request hanya membuka
 * satu koneksi per database (bukan membuka ulang setiap kali).
 *
 * @param  string  $db_key  Kunci database: 'app', 'inventory', 'ruang', 'surat', 'mou'
 * @return mysqli|null      Objek koneksi, atau null jika gagal
 */
function fikom_db(string $db_key): ?mysqli
{
    global $db_host, $db_user, $db_pass, $_fikom_db_map, $_fikom_db_cache;

    // Kembalikan dari cache jika sudah ada
    if (isset($_fikom_db_cache[$db_key])) {
        return $_fikom_db_cache[$db_key];
    }

    if (!isset($_fikom_db_map[$db_key])) {
        error_log("[db.php] Kunci database tidak dikenal: '$db_key'");
        return null;
    }

    $db_name = $_fikom_db_map[$db_key];

    // Nonaktifkan exception mysqli agar tidak crash
    mysqli_report(MYSQLI_REPORT_OFF);

    $conn = null;
    try {
        $conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        if (!$conn) {
            error_log("[db.php] Koneksi GAGAL ke '$db_key' ($db_name): " . mysqli_connect_error());
            $conn = null;
        } else {
            // Set charset UTF-8 agar tidak ada masalah encoding
            mysqli_set_charset($conn, 'utf8mb4');
        }
    } catch (mysqli_sql_exception $e) {
        error_log("[db.php] mysqli_sql_exception '$db_key': " . $e->getMessage());
        $conn = null;
    } catch (Throwable $e) {
        error_log("[db.php] Exception '$db_key': " . $e->getMessage());
        $conn = null;
    }

    $_fikom_db_cache[$db_key] = $conn;
    return $conn;
}

// ── 4. KONEKSI DEFAULT ($conn) ────────────────────────────────
// Untuk kompatibilitas mundur dengan file lama yang masih pakai $conn
// (misalnya include 'koneksi.php' lalu pakai $conn).
// Gunakan fikom_db() untuk koneksi yang lebih eksplisit.
$conn = fikom_db('app');
?>