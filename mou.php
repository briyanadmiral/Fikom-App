<?php
session_start();

// 1. Cek Login Utama dari Main App
if(!isset($_SESSION['logged_in'])){
    header('Location: index.php'); exit;
}

// Koneksi ke Database Utama (FIKOMAPP)
$conn = mysqli_connect('localhost', 'root', '', 'fikomapp');

$email       = $_SESSION['user_email'];
$role_global = $_SESSION['role']; 
$program     = $_SESSION['program'] ?? '-'; 

/* =================================================================================
   BAGIAN 1: LOGIKA KHUSUS SUPERADMIN (CRUD & PINTU MASUK)
   ================================================================================= */
if ($role_global === 'superadmin') {
    
    // A. Hapus Admin/User MOU (Hard Delete)
    if (isset($_GET['hapus_id'])) {
        $id_hapus = (int)$_GET['hapus_id'];
        mysqli_query($conn, "DELETE FROM t_mou WHERE id = $id_hapus");
        header("Location: mou.php");
        exit;
    }

    // B. Tambah Akses Baru ke Sistem MOU (TANPA NAMA)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_mou_akses'])) {
        $n_email   = mysqli_real_escape_string($conn, $_POST['email']);
        $n_role    = mysqli_real_escape_string($conn, $_POST['role']);
        $n_jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);
        
        // Sesuaikan dengan struktur tabel: email, role, jurusan
        $sql_insert = "INSERT INTO t_mou (email, role, jurusan) VALUES ('$n_email', '$n_role', '$n_jurusan')";
        mysqli_query($conn, $sql_insert);
        header("Location: mou.php");
        exit;
    }

    // C. Pintu Masuk Paksa untuk Superadmin
    if (isset($_GET['masuk_mou'])) {
        $_SESSION['mou_admin']   = true;
        $_SESSION['mou_jurusan'] = 'Semua Jurusan';
        $_SESSION['mou_email']   = $email;
        header("Location: mou/index.php");
        exit;
    }

    // D. Ambil Data Pengguna MOU untuk Ditampilkan di Tabel
    // Menggunakan deleted_at IS NULL sesuai struktur tabelmu
    $query_mou_users = mysqli_query($conn, "SELECT * FROM t_mou WHERE deleted_at IS NULL ORDER BY email ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel MOU - FIKOM APP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* === TEMA GLASSMORPHISM (GREY UI/UX) === */
        :root {
            --primary: #8a9ccc;
            --primary-soft: rgba(255, 255, 255, 0.5);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.4);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
            --danger: #ef4444;
            --success: #10b981;
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
        .btn-back { color: var(--text-muted); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: color 0.2s; }
        .btn-back:hover { color: var(--primary); }

        .card { 
            background: var(--bg-card); 
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 16px; 
            padding: 25px; 
            box-shadow: var(--shadow); 
            border: 1px solid var(--border); 
        }
        .card-title { font-size: 18px; font-weight: 600; color: var(--dark); margin-top: 0; margin-bottom: 20px; }

        /* Form Styling */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 500; color: var(--text-muted); }
        .form-control { padding: 10px 15px; background: rgba(255,255,255,0.5); border: 1px solid var(--border); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 15px; outline: none; transition: 0.2s; backdrop-filter: blur(5px); }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(138, 156, 204, 0.2); }
        
        .btn { padding: 10px 20px; border: 1px solid rgba(255,255,255,0.7); backdrop-filter: blur(5px); border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-family: 'Inter', sans-serif; transition: 0.2s; }
        .btn-primary { background: rgba(255,255,255,0.5); color: var(--dark); }
        .btn-primary:hover { background: rgba(255,255,255,0.8); transform: translateY(-2px); border-color: var(--primary); }
        .btn-success { background: rgba(255,255,255,0.5); color: var(--dark); }
        .btn-success:hover { background: rgba(255,255,255,0.8); transform: translateY(-2px); border-color: var(--primary); }

        /* Table Styling */
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); }
        th { font-weight: 600; font-size: 13px; color: var(--text-muted); text-transform: uppercase; background: transparent; }
        th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        tbody tr:hover { background-color: rgba(255,255,255,0.3); }
        
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
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
            <h1>Control Panel MOU</h1>
            <a href="superadmin/superadmin_home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <div class="card" style="display: flex; justify-content: space-between; align-items: center; background: var(--primary-soft); border-color: #bfdbfe;">
            <div>
                <h2 style="margin: 0 0 5px 0; color: var(--dark); font-size: 18px;">Akses Sistem MOU</h2>
                <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Masuk ke sistem MOU dengan kekuasaan penuh (Bypass).</p>
            </div>
            <a href="mou.php?masuk_mou=true" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Masuk ke Sistem MOU
            </a>
        </div>

        <div class="card">
            <h2 class="card-title">Tambah Akses Pengelola MOU</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Email Kampus / Pribadi</label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: dosen@unika.ac.id" required>
                    </div>
                    <div class="form-group">
                        <label>Hak Akses (Role)</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin MOU</option>
                            <option value="user">User Biasa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jurusan</label>
                        <select name="jurusan" class="form-control" required>
                            <option value="Sistem Informasi">Sistem Informasi</option>
                            <option value="Teknik Informatika">Teknik Informatika</option>
                            <option value="Semua Jurusan">Semua Jurusan</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="tambah_mou_akses" class="btn btn-success"><i class="fas fa-plus"></i> Tambah Pengguna</button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Daftar Pengelola MOU</h2>
            <table>
                <thead>
                    <tr>
                        <th>Email Pengguna</th>
                        <th>Jurusan</th>
                        <th>Hak Akses</th>
                        <th style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query_mou_users)) : ?>
                    <tr>
                        <td>
                            <strong style="display: block; color: var(--dark);"><?= htmlspecialchars($row['email']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($row['jurusan']) ?></td>
                        <td>
                            <span class="badge <?= ($row['role'] == 'admin') ? 'badge-admin' : 'badge-user' ?>">
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="mou.php?hapus_id=<?= $row['id'] ?>" class="btn-action btn-delete" title="Hapus Akses" onclick="return confirm('Yakin ingin menghapus akses untuk <?= $row['email'] ?>?');">
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
   BAGIAN 2: LOGIKA PENGECEKAN BERUNTUN (WATERFALL AUTHORIZATION UNTUK MOU)
   ================================================================================= */

// Reset semua tiket
$_SESSION['mou_admin'] = false;
$_SESSION['mou_user']  = false;

// LAPIS 1: Cek di Database t_mou (Pengelola / Admin Spesifik MOU)
$query_mou = "SELECT role, jurusan FROM t_mou WHERE email = '$email' AND deleted_at IS NULL LIMIT 1";
$result_mou = mysqli_query($conn, $query_mou);
$data_mou = mysqli_fetch_assoc($result_mou);

if ($data_mou) {
    if ($data_mou['role'] == 'admin') {
        $_SESSION['mou_admin']   = true;
    } else {
        $_SESSION['mou_user']    = true;
    }
    $_SESSION['mou_jurusan'] = $data_mou['jurusan'];
    $_SESSION['mou_email']   = $email;
} 
else {
    // LAPIS 2: Tidak ketemu di t_mou, cek di tabel Dosen
    $query_dosen = "SELECT jurusan FROM dosen WHERE email = '$email' LIMIT 1";
    $result_dosen = mysqli_query($conn, $query_dosen);
    $data_dosen = mysqli_fetch_assoc($result_dosen);

    if ($data_dosen) {
        // Dosen biasa (bukan admin spesifik MOU) masuk sebagai user
        $_SESSION['mou_user']    = true;
        $_SESSION['mou_jurusan'] = $data_dosen['jurusan'];
        $_SESSION['mou_email']   = $email;
    } 
    else {
        // LAPIS 3: Bukan Dosen, cek apakah Mahasiswa
        if ($role_global === 'mahasiswa' || strpos($email, 'student.unika.ac.id') !== false) {
            $_SESSION['mou_user']    = true;
            $_SESSION['mou_jurusan'] = $program;
            $_SESSION['mou_email']   = $email;
        }
    }
}

// VALIDASI AKHIR & REDIRECT
// Jika punya tiket admin ATAU tiket user, izinkan masuk ke sistem
if ($_SESSION['mou_admin'] || $_SESSION['mou_user']) {
    header("Location: mou/index.php");
    exit;
} else {
    echo "<script>alert('Akses Ditolak. Anda tidak memiliki izin untuk masuk ke sistem MOU.'); window.location='index.php';</script>";
}
?>