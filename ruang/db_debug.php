<?php
/**
 * db_debug.php — Halaman Diagnosa Koneksi Database Ruang
 * Akses: https://apps.fikom.id/ruang/db_debug.php
 * PENTING: Hapus file ini setelah selesai debug!
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === KEAMANAN MINIMAL: Hanya izinkan akses dari IP tertentu (opsional) ===
// $allowed_ips = ['YOUR_IP_HERE'];
// if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowed_ips)) { die('Access denied'); }

$sections = [];

// ─────────────────────────────────────────────────────────────
// 1. PHP INFO DASAR
// ─────────────────────────────────────────────────────────────
$sections['PHP Info'] = [
    'PHP Version'           => PHP_VERSION,
    'PDO Loaded'            => extension_loaded('pdo') ? '✅ YES' : '❌ NO',
    'PDO MySQL Loaded'      => extension_loaded('pdo_mysql') ? '✅ YES' : '❌ NO',
    'MySQLi Loaded'         => extension_loaded('mysqli') ? '✅ YES' : '❌ NO',
    'variables_order (ini)' => ini_get('variables_order'),
    'SAPI'                  => PHP_SAPI,
    '__DIR__'               => __DIR__,
];

// ─────────────────────────────────────────────────────────────
// 2. CEK FILE .ENV & VENDOR
// ─────────────────────────────────────────────────────────────
$root_path         = __DIR__ . '/..';          // /ruang/../ = root fikomapp
$env_file          = $root_path . '/.env';
$autoload_root     = $root_path . '/vendor/autoload.php';
$autoload_ruang    = __DIR__ . '/vendor/autoload.php';

$sections['File Check'] = [
    '.env path (dicari)'      => realpath($env_file) ?: ($env_file . ' [NOT FOUND]'),
    '.env exists'             => file_exists($env_file) ? '✅ YES' : '❌ NO',
    '.env readable'           => is_readable($env_file) ? '✅ YES' : '❌ NO',
    'vendor/autoload (root)'  => file_exists($autoload_root) ? '✅ ' . realpath($autoload_root) : '❌ NOT FOUND',
    'vendor/autoload (ruang)' => file_exists($autoload_ruang) ? '✅ ' . realpath($autoload_ruang) : '❌ NOT FOUND',
    'Dotenv class (setelah autoload)' => 'belum dicek (lihat bawah)',
];

// ─────────────────────────────────────────────────────────────
// 3. CEK $_ENV SEBELUM APAPUN
// ─────────────────────────────────────────────────────────────
$env_keys = ['DB_HOST', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE_RUANG'];
$before_load = [];
foreach ($env_keys as $k) {
    $from_env    = isset($_ENV[$k])    ? '✅ $_ENV'    : '❌';
    $from_server = isset($_SERVER[$k]) ? '✅ $_SERVER' : '❌';
    $from_getenv = (getenv($k) !== false) ? '✅ getenv()' : '❌';
    $before_load[$k] = "$from_env | $from_server | $from_getenv";
}
$sections['Env Variables (SEBELUM load .env)'] = $before_load;

// ─────────────────────────────────────────────────────────────
// 4. ISI .ENV (tampilkan tanpa password lengkap)
// ─────────────────────────────────────────────────────────────
if (file_exists($env_file) && is_readable($env_file)) {
    $env_contents = [];
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value, " \t\n\r\"'");
        // Sembunyikan password
        if (stripos($name, 'PASSWORD') !== false || stripos($name, 'SECRET') !== false || stripos($name, 'KEY') !== false) {
            $value = str_repeat('*', min(strlen($value), 8)) . ' [hidden]';
        }
        $env_contents[$name] = $value;
    }
    $sections['.env Contents (DB keys)'] = array_filter($env_contents, fn($k) => str_starts_with($k, 'DB_'), ARRAY_FILTER_USE_KEY);
} else {
    $sections['.env Contents (DB keys)'] = ['ERROR' => '.env tidak ditemukan atau tidak bisa dibaca'];
}

// ─────────────────────────────────────────────────────────────
// 5. COBA LOAD .ENV DAN CEK LAGI
// ─────────────────────────────────────────────────────────────
$load_method = 'tidak ada yang berhasil';
if (file_exists($autoload_root)) {
    require_once $autoload_root;
    if (class_exists('Dotenv\\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($root_path);
            $dotenv->safeLoad();
            $load_method = '✅ Dotenv dari root vendor berhasil';
        } catch (Throwable $e) {
            $load_method = '❌ Dotenv error: ' . $e->getMessage();
        }
    } else {
        $load_method = '❌ Dotenv class tidak ditemukan di root vendor';
    }
} elseif (file_exists($autoload_ruang)) {
    require_once $autoload_ruang;
    if (class_exists('Dotenv\\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($root_path);
            $dotenv->safeLoad();
            $load_method = '✅ Dotenv dari ruang vendor berhasil';
        } catch (Throwable $e) {
            $load_method = '❌ Dotenv ruang error: ' . $e->getMessage();
        }
    } else {
        $load_method = '❌ Dotenv tidak ada di ruang vendor (hanya phpspreadsheet)';
    }
}

// Fallback manual parse jika Dotenv gagal
if (strpos($load_method, '❌') !== false || $load_method === 'tidak ada yang berhasil') {
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value, " \t\n\r\"'");
            if (!isset($_ENV[$name])) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
        $load_method .= ' | ⚠️ Fallback: parse manual .env dijalankan';
    }
}

$sections['.env Load Method'] = ['method' => $load_method];

// Setelah load
$after_load = [];
foreach ($env_keys as $k) {
    $val = $_ENV[$k] ?? $_SERVER[$k] ?? getenv($k) ?: '❌ TIDAK DITEMUKAN';
    if (stripos($k, 'PASSWORD') !== false) $val = str_repeat('*', 6) . ' [hidden]';
    $after_load[$k] = $val;
}
$sections['Env Variables (SETELAH load .env)'] = $after_load;

// ─────────────────────────────────────────────────────────────
// 6. TES KONEKSI DATABASE LANGSUNG
// ─────────────────────────────────────────────────────────────
$db_host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
$db_user = $_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'fike8938_fikom_app';
$db_pass = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'fikom#12345';
$db_name = $_ENV['DB_DATABASE_RUANG'] ?? $_SERVER['DB_DATABASE_RUANG'] ?? getenv('DB_DATABASE_RUANG') ?: 'fike8938_fikom_ruang';

$conn_result = [];
$conn_result['host dipakai']     = $db_host;
$conn_result['username dipakai'] = $db_user;
$conn_result['password dipakai'] = str_repeat('*', 6) . ' [hidden]';
$conn_result['database dipakai'] = $db_name;

// Test dengan host 'localhost'
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=$db_name;charset=utf8",
        $db_user, $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
    );
    $conn_result['PDO host=localhost'] = '✅ BERHASIL';
    $pdo = null;
} catch (Exception $e) {
    $conn_result['PDO host=localhost'] = '❌ GAGAL: ' . $e->getMessage();
}

// Test dengan host '127.0.0.1'
try {
    $pdo2 = new PDO(
        "mysql:host=127.0.0.1;dbname=$db_name;charset=utf8",
        $db_user, $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
    );
    $conn_result['PDO host=127.0.0.1'] = '✅ BERHASIL';
    $pdo2 = null;
} catch (Exception $e) {
    $conn_result['PDO host=127.0.0.1'] = '❌ GAGAL: ' . $e->getMessage();
}

// Test koneksi tanpa specify database (untuk cek apakah user/pass valid)
try {
    $pdo3 = new PDO(
        "mysql:host=localhost;charset=utf8",
        $db_user, $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
    );
    $conn_result['PDO tanpa DB (cek user/pass)'] = '✅ User/pass VALID';
    // Cek apakah DB ada
    $r = $pdo3->query("SHOW DATABASES LIKE '$db_name'")->fetch();
    $conn_result["Database '$db_name' ada?"] = $r ? '✅ YA' : '❌ TIDAK ADA / tidak punya akses';
    $pdo3 = null;
} catch (Exception $e) {
    $conn_result['PDO tanpa DB (cek user/pass)'] = '❌ GAGAL: ' . $e->getMessage();
}

$sections['Tes Koneksi Database'] = $conn_result;

// ─────────────────────────────────────────────────────────────
// 7. TES VIA DATABASE CLASS
// ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/database.php';
try {
    $dbObj = new Database();
    $dbConn = $dbObj->getConnection();
    $sections['Database Class Test'] = [
        'new Database()->getConnection()' => $dbConn ? '✅ BERHASIL' : '❌ GAGAL (null) — lihat PHP error log',
    ];
} catch (Throwable $e) {
    $sections['Database Class Test'] = ['ERROR' => '❌ ' . $e->getMessage()];
}

// ─────────────────────────────────────────────────────────────
// OUTPUT HTML
// ─────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>DB Debug — Ruang FIKOM</title>
<style>
  body { font-family: monospace; background: #1a1a2e; color: #e0e0e0; padding: 20px; }
  h1 { color: #ff6b6b; }
  h2 { color: #4ecdc4; border-bottom: 1px solid #4ecdc4; padding-bottom: 4px; margin-top: 30px; }
  table { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
  td, th { border: 1px solid #444; padding: 6px 12px; text-align: left; }
  th { background: #16213e; color: #4ecdc4; }
  tr:hover td { background: #1e1e3f; }
  .ok { color: #6bcb77; }
  .fail { color: #ff6b6b; }
  .warn { color: #ffd93d; }
  .note { background: #2a2a4a; padding: 10px; border-left: 4px solid #ffd93d; margin: 10px 0; }
</style>
</head>
<body>
<h1>🔍 Database Debug — Ruang FIKOM</h1>
<div class="note">⚠️ <strong>HAPUS file ini setelah debug selesai!</strong><br>
URL: <code><?= htmlspecialchars('https://apps.fikom.id/ruang/db_debug.php') ?></code></div>

<?php foreach ($sections as $title => $data): ?>
<h2><?= htmlspecialchars($title) ?></h2>
<table>
  <tr><th>Key</th><th>Value</th></tr>
  <?php foreach ($data as $k => $v): 
    $cls = (strpos((string)$v, '✅') !== false) ? 'ok' : ((strpos((string)$v, '❌') !== false) ? 'fail' : ((strpos((string)$v, '⚠️') !== false) ? 'warn' : ''));
  ?>
  <tr><td><?= htmlspecialchars($k) ?></td><td class="<?= $cls ?>"><?= htmlspecialchars((string)$v) ?></td></tr>
  <?php endforeach; ?>
</table>
<?php endforeach; ?>

<div class="note">
<strong>Cara membaca hasil ini:</strong><br>
1. Lihat bagian <em>"Env Variables (SETELAH load .env)"</em> — apakah DB_HOST, DB_PASSWORD dll. sudah terisi?<br>
2. Lihat bagian <em>"Tes Koneksi Database"</em> — pesan error tepat akan ada di sini.<br>
3. Jika "user/pass VALID" tapi "Database tidak ada" → database belum dibuat atau user tidak punya akses ke DB tersebut.
</div>
</body>
</html>
