<?php
session_start();

// 1. Cek Login Google dari Main App
if(!isset($_SESSION['logged_in'])){
    header('Location: login.php'); exit;
}

$conn = mysqli_connect('localhost', 'root', '', 'db_inventaris_lab');

$email       = $_SESSION['user_email'];
$role_global = $_SESSION['role']; 
$program     = $_SESSION['program'] ?? '-'; 

/* =================================================================================
   BAGIAN 1: LOGIKA KHUSUS SUPERADMIN (CRUD & MODE BUNGLON)
   ================================================================================= */
if ($role_global === 'superadmin') {
    
    // A. Hapus User
    if (isset($_GET['hapus_id'])) {
        $id_hapus = (int)$_GET['hapus_id'];
        mysqli_query($conn, "DELETE FROM users WHERE id_user = $id_hapus");
        header("Location: inventory.php");
        exit;
    }

    // B. Tambah User Baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
        $n_email = mysqli_real_escape_string($conn, $_POST['email']);
        $n_nama  = mysqli_real_escape_string($conn, $_POST['nama']);
        $n_role  = mysqli_real_escape_string($conn, $_POST['role']);
        $n_prodi = (int)$_POST['id_prodi'];
        
        mysqli_query($conn, "INSERT INTO users (email, nama, role, id_prodi) VALUES ('$n_email', '$n_nama', '$n_role', $n_prodi)");
        header("Location: inventory.php");
        exit;
    }

    // C. Mode Bunglon (Pilih Lab)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih_prodi'])) {
        $pilihan = $_POST['pilih_prodi'];
        
        $_SESSION['users_siega'] = false;
        $_SESSION['users_ti']    = false;
        $_SESSION['admin_siega'] = false;
        $_SESSION['admin_ti']    = false;

        if ($pilihan === 'si') {
            $_SESSION['admin_siega'] = true;
            $_SESSION['users_siega'] = true;
        } elseif ($pilihan === 'ti') {
            $_SESSION['admin_ti'] = true;
            $_SESSION['users_ti'] = true;
        }

        $_SESSION['inv_validated'] = true;
        header("Location: inventory/inventaris-lab/public/");
        exit;
    }

    // D. Ambil Data User untuk Ditampilkan di Tabel
    $query_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");

    // Tampilkan Antarmuka Model A1
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel Inventory - FIKOM APP</title>
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
            --bg-body: #e4e7ec; /* Fallback flat color */
            --bg-card: rgba(255, 255, 255, 0.4);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
            --danger: #ef4444;
            --danger-soft: rgba(239, 68, 68, 0.15);
            --info: #0ea5e9;
            --info-soft: rgba(14, 165, 233, 0.15);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.8) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(200, 205, 215, 0.5) 0%, transparent 60%);
            background-attachment: fixed;
            color: var(--text-main);
            margin: 0; padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Header */
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-top h1 { font-size: 24px; color: var(--dark); margin: 0; text-shadow: 0 1px 2px rgba(255,255,255,0.8); }
        .btn-back {
            color: var(--text-muted); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: var(--primary); text-shadow: 0 0 5px rgba(255,255,255,0.8); }

        /* Card Umum - Glassmorphism */
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

        /* Mode Bunglon Buttons */
        .bunglon-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
        }
        .btn-bunglon {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            padding: 20px; background: rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.6);
            backdrop-filter: blur(5px);
            border-radius: 12px; cursor: pointer; color: var(--dark); font-weight: 600; font-size: 16px;
            transition: all 0.3s; font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .btn-bunglon i { font-size: 32px; color: var(--primary); }
        .btn-bunglon:hover {
            border-color: rgba(255,255,255,0.9); background: rgba(255,255,255,0.6); transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(31, 38, 135, 0.1);
        }

        /* Form Tambah */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 500; color: var(--text-muted); }
        .form-control {
            padding: 10px 15px; border: 1px solid rgba(255,255,255,0.6); border-radius: 8px; font-family: 'Inter', sans-serif;
            background: rgba(255,255,255,0.5); backdrop-filter: blur(5px);
            font-size: 15px; color: var(--text-main); outline: none; transition: border-color 0.2s;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,255,255,0.5); background: rgba(255,255,255,0.8); }
        .btn-primary {
            background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px;
            font-weight: 600; cursor: pointer; transition: background 0.2s; font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-primary:hover { background: #647ec2; }

        /* Styling Tabel Glassmorphism */
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.4); }
        th { font-weight: 600; font-size: 13px; color: var(--text-muted); text-transform: uppercase; background: rgba(255,255,255,0.3); backdrop-filter: blur(10px); }
        th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        tbody tr { transition: background-color 0.2s; }
        tbody tr:hover { background-color: rgba(255,255,255,0.5); backdrop-filter: blur(5px); }
        
        /* Badge Prodi & Role */
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-si { background: rgba(79, 70, 229, 0.15); color: #4338ca; }
        .badge-ti { background: rgba(192, 38, 211, 0.15); color: #a21caf; }
        .badge-admin { background: rgba(220, 38, 38, 0.15); color: #b91c1c; }
        .badge-user { background: rgba(22, 163, 74, 0.15); color: #15803d; }

        .actions { display: flex; gap: 8px; }
        .btn-action {
            width: 32px; height: 32px; display: grid; place-items: center; border-radius: 8px; 
            text-decoration: none; font-size: 14px; transition: all 0.2s; backdrop-filter: blur(5px);
        }
        .btn-edit { background: var(--info-soft); color: var(--info); border: 1px solid rgba(14,165,233,0.3); }
        .btn-edit:hover { background: var(--info); color: white; border-color: var(--info); }
        .btn-delete { background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
        .btn-delete:hover { background: var(--danger); color: white; border-color: var(--danger); }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header-top">
            <h1>Control Panel Inventory</h1>
            <a href="superadmin/superadmin_home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <div class="card">
            <h2 class="card-title">Pintu Masuk (Bunglon Mode)</h2>
            <form method="POST" class="bunglon-grid">
                <button type="submit" name="pilih_prodi" value="si" class="btn-bunglon">
                    <i class="fas fa-server"></i>
                    Masuk sebagai Admin Sistem Informasi
                </button>
                <button type="submit" name="pilih_prodi" value="ti" class="btn-bunglon">
                    <i class="fas fa-code"></i>
                    Masuk sebagai Admin Teknik Informatika
                </button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Tambah Akses Pengguna</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                    </div>
                    <div class="form-group">
                        <label>Email Kampus / Pribadi</label>
                        <input type="email" name="email" class="form-control" placeholder="dosen@unika.ac.id" required>
                    </div>
                    <div class="form-group">
                        <label>Hak Akses (Role)</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin Lab (Pengelola)</option>
                            <option value="user">User Biasa (Peminjam)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Program Studi</label>
                        <select name="id_prodi" class="form-control" required>
                            <option value="1">Sistem Informasi</option>
                            <option value="2">Teknik Informatika</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="tambah_user" class="btn-primary"><i class="fas fa-plus"></i> Tambah Pengguna</button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Daftar Pengguna Inventory</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama & Email</th>
                        <th>Program Studi</th>
                        <th>Hak Akses</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query_users)) : ?>
                    <tr>
                        <td>
                            <strong style="display: block; color: var(--dark);"><?= htmlspecialchars($row['nama']) ?></strong>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($row['email']) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= ($row['id_prodi'] == 1) ? 'badge-si' : 'badge-ti' ?>">
                                <?= ($row['id_prodi'] == 1) ? 'Sistem Informasi' : 'Teknik Informatika' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= ($row['role'] == 'admin') ? 'badge-admin' : 'badge-user' ?>">
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="edit_user_inventory.php?id=<?= $row['id_user'] ?>" class="btn-action btn-edit" title="Edit Data">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a href="inventory.php?hapus_id=<?= $row['id_user'] ?>" class="btn-action btn-delete" title="Hapus Data" onclick="return confirm('Yakin ingin menghapus akses untuk <?= $row['nama'] ?>?');">
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
    exit; // Berhenti di sini, kode di bawahnya tidak dieksekusi untuk superadmin
}

/* =================================================================================
   BAGIAN 2: LOGIKA PENGECEKAN BERUNTUN (WATERFALL AUTHORIZATION)
   ================================================================================= */
$_SESSION['users_siega'] = false;
$_SESSION['users_ti']    = false;
$_SESSION['admin_siega'] = false;
$_SESSION['admin_ti']    = false;

// 1. LAPIS PERTAMA: Cek di Database Inventory Lokal (Admin / User Prioritas)
$query_inv  = "SELECT role, id_prodi FROM users WHERE email = '$email' LIMIT 1";
$result_inv = mysqli_query($conn, $query_inv);
$data_inv   = mysqli_fetch_assoc($result_inv);

if ($data_inv) {
    // Jika ketemu di DB Inventory, terapkan hak aksesnya
    if ($data_inv['role'] == 'admin') {
        if ($data_inv['id_prodi'] == 1) { $_SESSION['admin_siega'] = true; $_SESSION['users_siega'] = true; }
        if ($data_inv['id_prodi'] == 2) { $_SESSION['admin_ti'] = true; $_SESSION['users_ti'] = true; }
    } else {
        if ($data_inv['id_prodi'] == 1) $_SESSION['users_siega'] = true;
        if ($data_inv['id_prodi'] == 2) $_SESSION['users_ti'] = true;
    }
} 
else {
    // 2. LAPIS KEDUA: Tidak ketemu di Inventory, Cek di Database Utama (Tabel Dosen)
    $conn_main = mysqli_connect('localhost', 'root', '', 'fikomapp');
    $query_dosen = "SELECT jurusan FROM dosen WHERE email = '$email' LIMIT 1";
    $result_dosen = mysqli_query($conn_main, $query_dosen);
    $data_dosen = mysqli_fetch_assoc($result_dosen);

    if ($data_dosen) {
        // Jika terdaftar sebagai Dosen, beri hak akses user/peminjam sesuai jurusan
        $jurusan = strtolower(trim($data_dosen['jurusan'])); 
        if ($jurusan == 'siega' || $jurusan == 'sistem informasi') {
            $_SESSION['users_siega'] = true;
        } elseif ($jurusan == 'teknik informatika' || $jurusan == 'informatika') {
            $_SESSION['users_ti'] = true;
        }
    } 
    else {
        // 3. LAPIS KETIGA: Bukan Dosen, Cek apakah Mahasiswa (@student.unika.ac.id)
        if ($role_global === 'mahasiswa' || strpos($email, 'student.unika.ac.id') !== false) {
            // Jika mahasiswa, beri akses peminjam sesuai program studi
            if ($program == 'siega') {
                $_SESSION['users_siega'] = true;
            } elseif ($program == 'informatika') {
                $_SESSION['users_ti'] = true;
            }
        }
    }
}

// 4. VALIDASI AKHIR & REDIRECT
// Jika salah satu dari session ini true, berarti dia lolos salah satu lapis di atas
if ($_SESSION['users_siega'] || $_SESSION['users_ti'] || $_SESSION['admin_siega'] || $_SESSION['admin_ti']) {
    $_SESSION['inv_validated'] = true;
    header("Location: inventory/inventaris-lab/public/");
    exit;
} else {
    // Jika false semua, akses ditolak
    echo "<script>alert('Akses Ditolak. Anda tidak memiliki izin untuk masuk ke sistem Inventory.'); window.location='index.php';</script>";
}
?>