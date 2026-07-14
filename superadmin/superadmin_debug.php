<?php
/**
 * DEBUG PAGE - superadmin_debug.php
 * Halaman ini untuk mendiagnosis error 500 di superadmin_home.php
 * HAPUS file ini setelah selesai debug!
 */

// Tampilkan semua error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

echo "<style>
body { font-family: monospace; background: #1a1a2e; color: #e0e0e0; padding: 20px; }
h2 { color: #00d4ff; border-bottom: 1px solid #333; padding-bottom: 8px; }
.ok { color: #00ff88; }
.fail { color: #ff4444; font-weight: bold; }
.warn { color: #ffaa00; }
.box { background: #16213e; border: 1px solid #333; padding: 15px; margin: 10px 0; border-radius: 8px; }
pre { background: #0f3460; padding: 10px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #444; padding: 8px 12px; text-align: left; }
th { background: #0f3460; color: #00d4ff; }
tr:nth-child(even) { background: #1a1a2e; }
</style>";

echo "<h1 style='color:#00d4ff'>🔍 Superadmin Debug Page</h1>";
echo "<p style='color:#888'>Generated: " . date('Y-m-d H:i:s') . " (server time)</p>";

// ============================================================
// 1. CECK SESSION
// ============================================================
echo "<h2>1. Session Data</h2><div class='box'>";
echo "<table><tr><th>Key</th><th>Value</th></tr>";
if (empty($_SESSION)) {
    echo "<tr><td colspan='2' class='fail'>⚠ Session KOSONG - belum login!</td></tr>";
} else {
    foreach ($_SESSION as $k => $v) {
        $display = is_array($v) ? json_encode($v) : htmlspecialchars((string)$v);
        echo "<tr><td>$k</td><td>$display</td></tr>";
    }
}
echo "</table>";

// Cek field penting
$required_session = ['logged_in', 'role', 'user_name', 'user_picture'];
foreach ($required_session as $key) {
    if (!isset($_SESSION[$key])) {
        echo "<p class='warn'>⚠ SESSION['$key'] TIDAK ADA</p>";
    }
}

$is_superadmin = isset($_SESSION['logged_in']) && $_SESSION['role'] === 'superadmin';
echo "<p>Role check (superadmin): " . ($is_superadmin ? "<span class='ok'>✔ PASS</span>" : "<span class='fail'>✘ FAIL - role = " . htmlspecialchars($_SESSION['role'] ?? 'NULL') . "</span>") . "</p>";
echo "</div>";

// ============================================================
// 2. CEK PHP VERSION & EXTENSIONS
// ============================================================
echo "<h2>2. PHP Environment</h2><div class='box'>";
echo "<p>PHP Version: <strong>" . phpversion() . "</strong></p>";
echo "<p>mysqli: " . (extension_loaded('mysqli') ? "<span class='ok'>✔ Loaded</span>" : "<span class='fail'>✘ NOT loaded</span>") . "</p>";
echo "<p>pdo_mysql: " . (extension_loaded('pdo_mysql') ? "<span class='ok'>✔ Loaded</span>" : "<span class='warn'>⚠ Not loaded</span>") . "</p>";
echo "<p>openssl: " . (extension_loaded('openssl') ? "<span class='ok'>✔ Loaded</span>" : "<span class='warn'>⚠ Not loaded</span>") . "</p>";
echo "<p>SERVER: <code>" . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "</code></p>";
echo "</div>";

// ============================================================
// 3. CEK .ENV FILE DAN VARIABEL
// ============================================================
echo "<h2>3. Environment Variables (.env)</h2><div class='box'>";

$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    echo "<p class='ok'>✔ File .env ditemukan di: " . realpath($env_file) . "</p>";
} else {
    echo "<p class='fail'>✘ File .env TIDAK DITEMUKAN di: $env_file</p>";
}

// Coba load dotenv
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
    echo "<p class='ok'>✔ vendor/autoload.php berhasil di-load</p>";

    if (class_exists('Dotenv\\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->safeLoad();
            echo "<p class='ok'>✔ Dotenv safeLoad() berhasil</p>";
        } catch (Exception $e) {
            echo "<p class='fail'>✘ Dotenv error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p class='warn'>⚠ Class Dotenv\\Dotenv tidak tersedia</p>";
    }
} else {
    echo "<p class='fail'>✘ vendor/autoload.php TIDAK ADA di: $autoload</p>";
}

$env_vars = ['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE_APP', 'DB_DATABASE'];
echo "<table><tr><th>Variable</th><th>\$_ENV</th><th>getenv()</th></tr>";
foreach ($env_vars as $var) {
    $env_val = $_ENV[$var] ?? null;
    $getenv_val = getenv($var) ?: null;

    // Sembunyikan password
    $display_env = $var === 'DB_PASSWORD'
        ? ($env_val ? str_repeat('*', strlen($env_val)) : '<em class="fail">NULL</em>')
        : ($env_val ? htmlspecialchars($env_val) : '<em class="fail">NULL</em>');

    $display_get = $var === 'DB_PASSWORD'
        ? ($getenv_val ? str_repeat('*', strlen($getenv_val)) : '<em class="fail">NULL</em>')
        : ($getenv_val ? htmlspecialchars($getenv_val) : '<em class="fail">NULL</em>');

    echo "<tr><td>$var</td><td>$display_env</td><td>$display_get</td></tr>";
}
echo "</table></div>";

// ============================================================
// 4. CEK KONEKSI DATABASE (via db.php terpusat)
// ============================================================
echo "<h2>4. Database Connection Test (via db.php)</h2><div class='box'>";

// Load db.php
if (!defined('FIKOM_ROOT')) define('FIKOM_ROOT', __DIR__ . '/..');
require_once __DIR__ . '/../db.php';

$db_map_display = [
    'app'       => $_ENV['DB_DATABASE_APP']       ?? 'fike8938_fikom_app',
    'inventory' => $_ENV['DB_DATABASE_INVENTORY'] ?? 'fike8938_fikom_inventory',
    'ruang'     => $_ENV['DB_DATABASE_RUANG']     ?? 'fike8938_fikom_ruang',
    'surat'     => $_ENV['DB_DATABASE_SURAT']     ?? 'fike8938_fikom_surat',
    'mou'       => $_ENV['DB_DATABASE_MOU']       ?? 'fike8938_fikom_mou',
];

echo "<table><tr><th>Database Key</th><th>Nama DB</th><th>Status</th></tr>";
foreach ($db_map_display as $key => $db_name_display) {
    $c = fikom_db($key);
    $status = $c
        ? "<span class='ok'>✔ OK</span>"
        : "<span class='fail'>✘ GAGAL — Access Denied atau DB tidak ada</span>";
    echo "<tr><td><code>$key</code></td><td><code>" . htmlspecialchars($db_name_display) . "</code></td><td>$status</td></tr>";
}
echo "</table>";

// Detail cek tabel dosen di DB 'app'
$conn_app = fikom_db('app');
if ($conn_app) {
    echo "<h3 style='margin-top:15px'>Detail DB 'app' — Cek Tabel 'dosen':</h3>";
    $q = mysqli_query($conn_app, "SHOW TABLES LIKE 'dosen'");
    if ($q && mysqli_num_rows($q) > 0) {
        echo "<p class='ok'>✔ Tabel 'dosen' ADA</p>";
        $count_q = mysqli_query($conn_app, "SELECT COUNT(*) as total FROM dosen");
        if ($count_q) {
            $row = mysqli_fetch_assoc($count_q);
            echo "<p>Jumlah record dosen: <strong>" . $row['total'] . "</strong></p>";
        }
        $col_q = mysqli_query($conn_app, "DESCRIBE dosen");
        if ($col_q) {
            echo "<p>Kolom tabel dosen:</p><table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            while ($col = mysqli_fetch_assoc($col_q)) {
                echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p class='fail'>✘ Tabel 'dosen' TIDAK ADA di database 'app'</p>";
        $tables_q = mysqli_query($conn_app, "SHOW TABLES");
        if ($tables_q) {
            echo "<p>Tabel yang tersedia:</p><ul>";
            while ($t = mysqli_fetch_row($tables_q)) echo "<li>{$t[0]}</li>";
            echo "</ul>";
        }
    }
} else {
    echo "<div style='background:#2a1a1a;border:1px solid #ff4444;padding:12px;border-radius:6px;margin-top:10px'>";
    echo "<strong style='color:#ff4444'>🔑 Solusi — Lakukan di cPanel Hosting:</strong><br>";
    echo "<ol style='color:#ffaa00;margin-top:8px'>";
    echo "<li>Login ke <strong>cPanel → MySQL Databases</strong></li>";
    echo "<li>Di bagian <strong>'Add User To Database'</strong>:</li>";
    echo "<li>User: <code>" . htmlspecialchars($db_user ?? '?') . "</code> → Database: <code>" . htmlspecialchars($db_map_display['app']) . "</code></li>";
    echo "<li>Klik <strong>Add</strong> → centang <strong>ALL PRIVILEGES</strong> → Save</li>";
    echo "<li>Ulangi untuk semua database (inventory, ruang, surat, mou)</li>";
    echo "</ol></div>";
}
echo "</div>";




// ============================================================
// 5. CEK ERROR LOG TERAKHIR
// ============================================================
echo "<h2>5. PHP Error Log (10 baris terakhir)</h2><div class='box'>";
$log_paths = [
    ini_get('error_log'),
    __DIR__ . '/../storage/logs/laravel.log',
    '/var/log/apache2/error.log',
    '/var/log/nginx/error.log',
    __DIR__ . '/../php_error.log',
];

$found_log = false;
foreach ($log_paths as $log_path) {
    if ($log_path && file_exists($log_path) && is_readable($log_path)) {
        $found_log = true;
        echo "<p class='ok'>✔ Log ditemukan: <code>" . htmlspecialchars($log_path) . "</code></p>";
        $lines = [];
        $file = new SplFileObject($log_path);
        $file->seek(PHP_INT_MAX);
        $total = $file->key();
        $start = max(0, $total - 20);
        $file->seek($start);
        while (!$file->eof()) {
            $lines[] = htmlspecialchars($file->fgets());
        }
        echo "<pre>" . implode('', array_slice($lines, -20)) . "</pre>";
        break;
    }
}

if (!$found_log) {
    $log_ini = ini_get('error_log');
    echo "<p class='warn'>⚠ Tidak bisa membaca error log</p>";
    echo "<p>error_log setting: <code>" . htmlspecialchars($log_ini ?: '(tidak diset)') . "</code></p>";
}
echo "</div>";

// ============================================================
// 6. RINGKASAN
// ============================================================
echo "<h2>6. Ringkasan / Kemungkinan Penyebab Error 500</h2><div class='box'>";
$issues = [];

if (!isset($_SESSION['logged_in'])) $issues[] = "Session tidak ada - user belum login atau session expired";
if (!$is_superadmin) $issues[] = "Role bukan superadmin (role = " . htmlspecialchars($_SESSION['role'] ?? 'NULL') . ")";
if (!$conn && isset($conn)) $issues[] = "Koneksi database gagal: " . mysqli_connect_error();
if (!extension_loaded('mysqli')) $issues[] = "Extension mysqli tidak ada di server";

if (empty($issues)) {
    echo "<p class='ok'>✔ Tidak ada masalah yang terdeteksi secara otomatis. Cek error log di atas.</p>";
} else {
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li class='fail'>✘ $issue</li>";
    }
    echo "</ul>";
}

echo "<br><p style='color:#888'>⚠ <strong>PENTING:</strong> Hapus file <code>superadmin_debug.php</code> ini setelah selesai debug karena menampilkan info sensitif!</p>";
echo "</div>";
?>
