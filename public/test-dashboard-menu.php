<?php
/**
 * SIMULASI DASHBOARD MENU (untuk testing)
 * File ini mensimulasikan flow dari dashboard menu teman Anda
 */

session_start();

// Simulasi data user yang sudah login di dashboard
$_SESSION['logged_in'] = true;
$_SESSION['user_email'] = 'agustina.anggitasari@unika.ac.id'; // Email user yang ada di database Laravel
$_SESSION['role'] = 'dosen';

// Simulasi hak akses dari tabel t_surat (ini yang dilakukan teman Anda)
// Dalam kasus real, ini diambil dari database mereka
$email = $_SESSION['user_email'];

// ✅ Simulasi 2 kondisi:
// 1. User punya role 'admin' di sistem surat
// 2. User punya role 'dosen' di sistem surat

// Pilih salah satu untuk testing:
$role_spesifik = 'admin'; // ✅ GANTI JADI 'dosen' untuk test role dosen
// $role_spesifik = 'dosen'; // Uncomment untuk test role dosen

// Set session sesuai role
if($role_spesifik == 'admin'){
    $_SESSION['admin'] = true;
    unset($_SESSION['dosen']);
} elseif($role_spesifik == 'dosen'){
    $_SESSION['dosen'] = true;
    unset($_SESSION['admin']);
}

$_SESSION['jurusan'] = 'Ilmu Komputer';

// Cari user_id dari database Laravel berdasarkan email
// (Ini perlu koneksi ke database Laravel)
try {
    // Koneksi ke database Laravel (sesuaikan dengan .env Anda)
    $host = '127.0.0.1';
    $dbname = 'surat_siega';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cari user berdasarkan email
    $stmt = $pdo->prepare("SELECT id, nama_lengkap FROM pengguna WHERE email = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$user) {
        die("Error: User dengan email '{$email}' tidak ditemukan di database Laravel. Pastikan email sudah terdaftar di tabel 'pengguna'.");
    }
    
    $user_id = $user['id'];
    $user_name = $user['nama_lengkap'];
    
} catch(PDOException $e) {
    die("Error koneksi database: " . $e->getMessage());
}

// ✅ Jika ada parameter ?redirect=true, langsung redirect
if(isset($_GET['redirect']) && $_GET['redirect'] == 'true') {
    $laravel_url = "http://127.0.0.1:8000/entry?user_id={$user_id}&role={$role_spesifik}";
    header("Location: {$laravel_url}");
    exit;
}

// URL untuk redirect
$laravel_url = "http://127.0.0.1:8000/entry?user_id={$user_id}&role={$role_spesifik}";

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Dashboard Menu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-top: 0; }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-left: 4px solid #2196F3;
            margin: 20px 0;
        }
        .success {
            background: #e8f5e9;
            padding: 15px;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
        }
        .btn {
            background: #2196F3;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #1976D2;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table td:first-child {
            font-weight: bold;
            width: 150px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🎯 Simulasi Dashboard Menu</h1>
        <p>File ini mensimulasikan flow dari dashboard menu teman Anda sebelum redirect ke Laravel.</p>
        
        <div class="info">
            <strong>ℹ️ Informasi Session</strong>
            <table>
                <tr>
                    <td>Email</td>
                    <td><code><?= htmlspecialchars($email) ?></code></td>
                </tr>
                <tr>
                    <td>User ID Laravel</td>
                    <td><code><?= $user_id ?></code></td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td><code><?= htmlspecialchars($user_name) ?></code></td>
                </tr>
                <tr>
                    <td>Role Spesifik</td>
                    <td><code><?= $role_spesifik ?></code></td>
                </tr>
                <tr>
                    <td>Session Admin</td>
                    <td><code><?= isset($_SESSION['admin']) && $_SESSION['admin'] ? 'TRUE' : 'FALSE' ?></code></td>
                </tr>
                <tr>
                    <td>Session Dosen</td>
                    <td><code><?= isset($_SESSION['dosen']) && $_SESSION['dosen'] ? 'TRUE' : 'FALSE' ?></code></td>
                </tr>
                <tr>
                    <td>Jurusan</td>
                    <td><code><?= htmlspecialchars($_SESSION['jurusan']) ?></code></td>
                </tr>
            </table>
        </div>
        
        <div class="success">
            <strong>✅ Session berhasil di-set!</strong><br>
            Siap redirect ke Laravel dengan URL:<br>
            <code><?= htmlspecialchars($laravel_url) ?></code>
        </div>
        
        <!-- ✅ PERBAIKAN: Gunakan link langsung, bukan form -->
        <a href="?redirect=true" class="btn">
            🚀 Masuk ke Sistem Surat (Laravel)
        </a>
        
        <hr style="margin: 30px 0;">
        
        <h3>📝 Cara Testing:</h3>
        <ol>
            <li>Edit file ini, ganti <code>$role_spesifik</code> untuk test role berbeda</li>
            <li>Klik tombol "Masuk ke Sistem Surat"</li>
            <li>Anda akan otomatis login ke Laravel tanpa memasukkan password</li>
            <li>Cek apakah tombol "Kembali ke Dashboard Menu" muncul</li>
        </ol>
        
        <h3>🔧 Debug URL:</h3>
        <p>Jika ingin langsung redirect tanpa klik tombol, akses URL ini:</p>
        <code style="display: block; padding: 10px; background: #f5f5f5;">
            <?= htmlspecialchars($laravel_url) ?>
        </code>
    </div>
</body>
</html>
