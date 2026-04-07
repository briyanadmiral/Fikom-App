<?php
session_start();

// 1. Cek Login Utama dari Main App
if(!isset($_SESSION['logged_in'])){
    header('Location: login.php'); exit;
}

$email       = $_SESSION['user_email'];
$role_global = $_SESSION['role']; 
$nama_user   = $_SESSION['user_name']; 

// 2. Koneksi ke Database Utama & Database Surat
$conn_utama = mysqli_connect('localhost', 'root', '', 'fikomapp');
$conn_surat = mysqli_connect('localhost', 'root', '', 'surat_fikom'); 

if (!$conn_surat || !$conn_utama) {
    die("Koneksi database gagal. Pastikan database fikomapp dan surat_fikom aktif.");
}

// Rahasia Jembatan Laravel
$sharedSecret = '7bf5429f72beebd2f98b046e4527d46e83ba56f161e0508fb97fa33615b413f1';

// Buat password acak (dummy) untuk menipu kolom sandi_hash Laravel
$dummy_hash = password_hash('bypass123', PASSWORD_BCRYPT);

/* =================================================================================
   BAGIAN 1: LOGIKA KHUSUS SUPERADMIN (CRUD & PINTU MASUK)
   ================================================================================= */
if ($role_global === 'superadmin') {
    
    // A. Hapus Admin/User Surat (Soft Delete ala Laravel)
    if (isset($_GET['hapus_id'])) {
        $id_hapus = (int)$_GET['hapus_id'];
        mysqli_query($conn_surat, "UPDATE pengguna SET deleted_at = NOW() WHERE id = $id_hapus");
        header("Location: surat.php");
        exit;
    }

    // B. Tambah Akses Baru ke Sistem Surat (Dilengkapi Auto-Restore)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_surat_akses'])) {
        $n_email   = mysqli_real_escape_string($conn_surat, $_POST['email']);
        $n_nama    = mysqli_real_escape_string($conn_surat, $_POST['nama_lengkap']);
        $n_jabatan = mysqli_real_escape_string($conn_surat, $_POST['jabatan']);
        $n_peran   = (int)$_POST['peran_id'];
        
        // 1. Cek apakah email sudah ada di database (Bahkan yang sudah di-soft-delete)
        $cek_email = mysqli_query($conn_surat, "SELECT id FROM pengguna WHERE email = '$n_email'");

        if (mysqli_num_rows($cek_email) > 0) {
            // 2a. Jika SUDAH ADA: Lakukan RESTORE (Aktifkan kembali) dan Update datanya
            $sql_action = "UPDATE pengguna SET 
                            nama_lengkap = '$n_nama', 
                            jabatan = '$n_jabatan', 
                            peran_id = $n_peran, 
                            deleted_at = NULL, 
                            status = 'aktif', 
                            updated_at = NOW() 
                           WHERE email = '$n_email'";
        } else {
            // 2b. Jika BENAR-BENAR BARU: Lakukan Insert
            $sql_action = "INSERT INTO pengguna (email, sandi_hash, nama_lengkap, jabatan, peran_id, status, created_at, updated_at) 
                           VALUES ('$n_email', '$dummy_hash', '$n_nama', '$n_jabatan', $n_peran, 'aktif', NOW(), NOW())";
        }

        mysqli_query($conn_surat, $sql_action);
        header("Location: surat.php");
        exit;
    }

    // C. Pintu Masuk Paksa untuk Superadmin (Bypass)
    if (isset($_GET['masuk_surat'])) {
        $q_sa = mysqli_query($conn_surat, "SELECT id FROM pengguna WHERE email = '$email' AND deleted_at IS NULL LIMIT 1");
        $d_sa = mysqli_fetch_assoc($q_sa);
        
        if (!$d_sa) {
            mysqli_query($conn_surat, "INSERT INTO pengguna (email, sandi_hash, nama_lengkap, jabatan, peran_id, status, created_at, updated_at) 
                                       VALUES ('$email', '$dummy_hash', 'Super Admin (Bypass)', 'Superadmin FIKOM', 1, 'aktif', NOW(), NOW())");
            $sa_id = mysqli_insert_id($conn_surat);
        } else {
            $sa_id = $d_sa['id'];
        }

        $token = hash_hmac('sha256', $sa_id . date('Y-m-d'), $sharedSecret);
        header("Location: http://localhost/fikomapp/surat_siega/public/entry?user_id=" . $sa_id . "&token=" . $token);
        exit;
    }

    // D. Ambil Data Pengguna Surat (Di-JOIN dengan tabel Peran untuk mendapatkan nama peran)
    $query_surat_users = mysqli_query($conn_surat, "
        SELECT p.*, r.nama AS nama_peran 
        FROM pengguna p 
        LEFT JOIN peran r ON p.peran_id = r.id 
        WHERE p.deleted_at IS NULL 
        ORDER BY p.id DESC
    ");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel Surat - FIKOM APP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* === TEMA GLASSMORPHISM (GREY UI/UX) === */
        :root {
            --primary: #8a9ccc; --primary-soft: rgba(255, 255, 255, 0.5); --dark: #3a4252;
            --text-main: #333333; --text-muted: #5e6677; --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.4); --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --danger: #ef4444; --danger-soft: #fee2e2; --success: #10b981;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            color: var(--text-main); 
            margin: 0; padding: 30px;
            background: var(--bg-body);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.8) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(200, 205, 215, 0.5) 0%, transparent 60%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .container { max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 30px; }
        
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .header-top h1 { font-size: 24px; color: var(--dark); margin: 0; }
        .btn-back { color: var(--text-muted); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-back:hover { color: var(--primary); }

        .card { 
            background: var(--bg-card); 
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 16px; 
            padding: 25px; 
            box-shadow: var(--shadow); 
            border: 1px solid var(--border); 
        }
        .card-title { font-size: 18px; font-weight: 600; color: var(--dark); margin-top: 0; margin-bottom: 20px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 500; color: var(--text-muted); }
        .form-control { padding: 10px 15px; background: rgba(255,255,255,0.5); border: 1px solid var(--border); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 15px; outline: none; transition: 0.2s; backdrop-filter: blur(5px); }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
        
        .btn { padding: 10px 20px; border: 1px solid rgba(255,255,255,0.7); backdrop-filter: blur(5px); border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-family: 'Inter', sans-serif; transition: 0.2s; }
        .btn-primary { background: rgba(255,255,255,0.5); color: var(--dark); }
        .btn-primary:hover { background: rgba(255,255,255,0.8); transform: translateY(-2px); border-color: var(--primary); }
        .btn-success { background: rgba(255,255,255,0.5); color: var(--dark); }
        .btn-success:hover { background: rgba(255,255,255,0.8); transform: translateY(-2px); border-color: var(--primary); }

        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        th { font-weight: 600; font-size: 13px; color: var(--text-muted); text-transform: uppercase; background: transparent; }
        th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        tbody tr:hover { background-color: rgba(255,255,255,0.3); }
        
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-admin { background: #fee2e2; color: #dc2626; }
        .badge-user { background: #dcfce7; color: #16a34a; }

        .actions { display: flex; gap: 8px; }
        .btn-action { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 8px; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .btn-delete { background: var(--danger-soft); color: var(--danger); }
        .btn-delete:hover { background: var(--danger); color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-top">
            <h1>Control Panel Surat</h1>
            <a href="superadmin/superadmin_home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <div class="card" style="display: flex; justify-content: space-between; align-items: center; background: var(--primary-soft); border-color: #bfdbfe;">
            <div>
                <h2 style="margin: 0 0 5px 0; color: var(--dark); font-size: 18px;">Akses Sistem Surat (Bypass)</h2>
                <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Masuk ke aplikasi Laravel dengan kekuasaan penuh.</p>
            </div>
            <a href="surat.php?masuk_surat=true" class="btn btn-primary">
                <i class="fas fa-envelope-open-text"></i> Masuk ke Sistem Surat
            </a>
        </div>

        <div class="card">
            <h2 class="card-title">Tambah Pengelola / Pengguna Surat</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="form-group">
                        <label>Email Kampus / Pribadi</label>
                        <input type="email" name="email" class="form-control" placeholder="dosen@unika.ac.id" required>
                    </div>
                    <div class="form-group">
                        <label>Peran (Sesuai Database)</label>
                        <select name="peran_id" class="form-control" required>
                            <option value="1">Admin TU (Administrator)</option>
                            <option value="2">Dekan</option>
                            <option value="3">Wakil Dekan</option>
                            <option value="4">Kaprodi</option>
                            <option value="5">Dosen</option>
                            <option value="6">Tendik</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jabatan Spesifik</label>
                        <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Ka. TU / Dosen Sistem Informasi" required>
                    </div>
                </div>
                <button type="submit" name="tambah_surat_akses" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Pengguna</button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Daftar Pengguna Sistem Surat</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama & Email</th>
                        <th>Jabatan</th>
                        <th>Hak Akses (Peran)</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query_surat_users)) : ?>
                    <tr>
                        <td>
                            <strong style="display: block; color: var(--dark);"><?= htmlspecialchars($row['nama_lengkap'] ?? 'Tanpa Nama') ?></strong>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($row['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= ($row['peran_id'] == 1) ? 'badge-admin' : 'badge-user' ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $row['nama_peran'] ?? 'Mahasiswa')) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="surat.php?hapus_id=<?= $row['id'] ?>" class="btn-action btn-delete" title="Hapus Akses" onclick="return confirm('Yakin ingin menghapus akses surat untuk <?= $row['email'] ?>?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
    exit; 
}

/* =================================================================================
   BAGIAN 2: LOGIKA PENGECEKAN BERUNTUN (WATERFALL AUTO-REGISTER & AUTO-RESTORE LARAVEL)
   ================================================================================= */

$userId_laravel = null;

// LAPIS 1: Cek langsung di tabel pengguna Surat (Admin / User yang sudah ada)
// Perhatikan: Kita hapus "AND deleted_at IS NULL" agar bisa mendeteksi akun yang terhapus (Soft Deleted)
$query_surat = mysqli_query($conn_surat, "SELECT id, deleted_at FROM pengguna WHERE email = '$email' LIMIT 1");
$data_surat = mysqli_fetch_assoc($query_surat);

if ($data_surat) {
    $userId_laravel = $data_surat['id'];

    // Jika akunnya ternyata sedang dalam status "Dihapus" (Soft Deleted), kita aktifkan kembali (Restore)!
    if ($data_surat['deleted_at'] !== null) {
        mysqli_query($conn_surat, "UPDATE pengguna SET deleted_at = NULL, status = 'aktif', updated_at = NOW() WHERE id = $userId_laravel");
    }
} 
else {
    // LAPIS 2: Tidak ada di Surat, Cek di DB Utama (Dosen)
    $query_dosen = mysqli_query($conn_utama, "SELECT * FROM dosen WHERE email = '$email' LIMIT 1");
    if (mysqli_num_rows($query_dosen) > 0) {
        // Karena dia Dosen Resmi, kita BUATKAN akun dengan peran_id = 5 (Dosen)
        $insert = mysqli_query($conn_surat, "INSERT INTO pengguna (email, sandi_hash, nama_lengkap, jabatan, peran_id, status, created_at, updated_at) 
                                             VALUES ('$email', '$dummy_hash', '$nama_user', 'Dosen', 5, 'aktif', NOW(), NOW())");
        if ($insert) {
            $userId_laravel = mysqli_insert_id($conn_surat);
        }
    } 
    else {
        // LAPIS 3: Bukan Dosen, Cek apakah Mahasiswa
        if ($role_global === 'mahasiswa' || strpos($email, 'student.unika.ac.id') !== false) {
            // Karena belum ada Peran Mahasiswa di tabel peran, kita gunakan angka 7 sebagai penanda sementara
            $insert = mysqli_query($conn_surat, "INSERT INTO pengguna (email, sandi_hash, nama_lengkap, jabatan, peran_id, status, created_at, updated_at) 
                                                 VALUES ('$email', '$dummy_hash', '$nama_user', 'Mahasiswa', 7, 'aktif', NOW(), NOW())");
            if ($insert) {
                $userId_laravel = mysqli_insert_id($conn_surat);
            }
        }
    }
}

// VALIDASI AKHIR & REDIRECT KE LARAVEL
if ($userId_laravel !== null) {
    // Generate Token
    $token = hash_hmac('sha256', $userId_laravel . date('Y-m-d'), $sharedSecret);
    $url_tujuan = "http://localhost/fikomapp/surat_siega/public/entry?user_id=" . $userId_laravel . "&token=" . $token;
    
    header("Location: " . $url_tujuan);
    exit;
} else {
    echo "<script>alert('Akses Ditolak. Anda tidak terdaftar sebagai pengguna sistem ini.'); window.location='index.php';</script>";
}
?>