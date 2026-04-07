<?php
session_start();
if(!isset($_SESSION['logged_in'])){
    header('Location: login.php'); exit;
}

$email   = $_SESSION['user_email'];
$role    = $_SESSION['role']; 
$name    = $_SESSION['user_name'];

$koneksi_ruang = mysqli_connect('localhost', 'root', '', 'sentralisasi_ruangan_fikom');

/* =================================================================================
   BAGIAN 1: LOGIKA KHUSUS SUPERADMIN (CRUD & MODE BUNGLON)
   ================================================================================= */
if ($role === 'superadmin') {
    
    // A. Hapus User
    if (isset($_GET['hapus_id'])) {
        $id_hapus = (int)$_GET['hapus_id'];
        mysqli_query($koneksi_ruang, "DELETE FROM users WHERE id = $id_hapus");
        header("Location: peminjamanRuangan.php");
        exit;
    }

    // B. Tambah/Edit User Baru
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
        $n_email   = mysqli_real_escape_string($koneksi_ruang, $_POST['email']);
        $n_nama    = mysqli_real_escape_string($koneksi_ruang, $_POST['nama']);
        $n_role    = mysqli_real_escape_string($koneksi_ruang, $_POST['ruang_role']);
        $n_nimnip  = mysqli_real_escape_string($koneksi_ruang, $_POST['nim_nip']);
        $n_jurusan = mysqli_real_escape_string($koneksi_ruang, $_POST['jurusan']);
        $n_status  = mysqli_real_escape_string($koneksi_ruang, $_POST['status']);
        
        mysqli_query($koneksi_ruang, "INSERT INTO users (email, nama, role, nim_nip, jurusan, status, created_at, updated_at) VALUES ('$n_email', '$n_nama', '$n_role', '$n_nimnip', '$n_jurusan', '$n_status', NOW(), NOW())");
        header("Location: peminjamanRuangan.php");
        exit;
    }

    // C. Mode Bunglon (Pilih Hak Akses)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pilih_role'])) {
        $pilihan = $_POST['pilih_role'];
        
        unset($_SESSION['admin'], $_SESSION['users']);

        $_SESSION['email']   = $email;
        $_SESSION['nama']    = $name;
        $_SESSION['user_id'] = $email;

        if ($pilihan === 'admin') {
            $_SESSION['admin'] = true;
        } elseif ($pilihan === 'users') {
            $_SESSION['users'] = true;
        }

        header("Location: ruang/index.php");
        exit;
    }

    // D. Ambil Data User untuk Ditampilkan di Tabel
    $query_users = mysqli_query($koneksi_ruang, "SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel Peminjaman Ruangan - FIKOM APP</title>
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
            --success: #10b981;
        }
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-body);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.8) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(200, 205, 215, 0.5) 0%, transparent 60%);
            background-attachment: fixed;
            margin: 0; padding: 30px; color: var(--text-main);
        }
        .container { max-width: 1100px; margin: 0 auto; display: flex; flex-direction: column; gap: 30px; }
        .header-top { display: flex; justify-content: space-between; align-items: center; }
        .header-top h1 { font-size: 24px; color: var(--dark); margin: 0; text-shadow: 0 1px 2px rgba(255,255,255,0.8); }
        .btn-back { color: var(--text-muted); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: color 0.2s;}
        .btn-back:hover { color: var(--primary); text-shadow: 0 0 5px rgba(255,255,255,0.8);}
        .card { 
            background: var(--bg-card); 
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 16px; padding: 25px; box-shadow: var(--shadow); border: 1px solid var(--border); 
        }
        .card-title { font-size: 18px; font-weight: 600; margin-top: 0; margin-bottom: 20px; color: var(--dark); }
        
        .bunglon-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-bunglon { 
            display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 20px; 
            background: rgba(255,255,255,0.3); border: 2px solid rgba(255,255,255,0.6); backdrop-filter: blur(5px);
            border-radius: 12px; cursor: pointer; color: var(--dark); font-weight: 600; font-size: 16px; transition: all 0.3s; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .btn-bunglon i { font-size: 32px; color: var(--primary); }
        .btn-bunglon:hover { border-color: rgba(255,255,255,0.9); background: rgba(255,255,255,0.6); transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(31, 38, 135, 0.1); }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 500; color: var(--text-muted); }
        .form-control { 
            padding: 10px 15px; border: 1px solid rgba(255,255,255,0.6); border-radius: 8px; font-family: 'Inter', sans-serif;
            background: rgba(255,255,255,0.5); backdrop-filter: blur(5px);
            outline: none; transition: 0.2s; color: var(--text-main);
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(255,255,255,0.5); background: rgba(255,255,255,0.8);}
        .btn-primary { 
            background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-family: 'Inter', sans-serif;
            font-weight: 600; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-primary:hover { background: #647ec2; }

        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.4); }
        th { font-weight: 600; font-size: 13px; color: var(--text-muted); text-transform: uppercase; background: rgba(255,255,255,0.3); backdrop-filter: blur(10px); }
        th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        tbody tr { transition: background-color 0.2s; }
        tbody tr:hover { background-color: rgba(255,255,255,0.5); backdrop-filter: blur(5px); }
        
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: rgba(79, 70, 229, 0.15); color: #4338ca; }
        .badge-user { background: rgba(22, 163, 74, 0.15); color: #15803d; }
        
        .actions { display: flex; gap: 8px; }
        .btn-action { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 8px; text-decoration: none; font-size: 14px; transition: all 0.2s; backdrop-filter: blur(5px); }
        .btn-delete { background: var(--danger-soft); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
        .btn-delete:hover { background: var(--danger); color: white; border-color: var(--danger); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-top">
            <h1>Control Panel Ruangan</h1>
            <a href="superadmin/superadmin_home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <div class="card">
            <h2 class="card-title">Pintu Masuk (Bunglon Mode)</h2>
            <form method="POST" class="bunglon-grid">
                <button type="submit" name="pilih_role" value="admin" class="btn-bunglon"><i class="fas fa-server"></i> Masuk sebagai Admin</button>
                <button type="submit" name="pilih_role" value="users" class="btn-bunglon"><i class="fas fa-users"></i> Masuk sebagai Pengguna</button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Tambah Relasi Akses</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>NIM / NIP</label><input type="text" name="nim_nip" class="form-control" required></div>
                    <div class="form-group"><label>Jurusan</label><input type="text" name="jurusan" class="form-control" required></div>
                    <div class="form-group">
                        <label>Hak Akses Ruangan (Role)</label>
                        <select name="ruang_role" class="form-control" required>
                            <option value="admin">Administrator Ruangan</option>
                            <option value="users">Pengguna Peminjam (Dosen/Mahasiswa)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="tambah_user" class="btn-primary"><i class="fas fa-plus"></i> Simpan Akses Pengguna</button>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title">Database Pengguna (Sistem Ruangan)</h2>
            <table>
                <thead>
                    <tr>
                        <th>NIM/NIP & Nama</th>
                        <th>Email & Jurusan</th>
                        <th>Status & Akses</th>
                        <th style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query_users)) : ?>
                    <tr>
                        <td><strong style="display:block;color:var(--dark);"><?= htmlspecialchars($row['nama']) ?></strong><small style="color:var(--text-muted);"><?= htmlspecialchars($row['nim_nip']) ?></small></td>
                        <td><strong style="display:block;color:var(--dark);"><?= htmlspecialchars($row['email']) ?></strong><small style="color:var(--text-muted);"><?= htmlspecialchars($row['jurusan']) ?></small></td>
                        <td>
                            <span class="badge <?= ($row['role'] == 'admin') ? 'badge-admin' : 'badge-user' ?>"><?= ucfirst($row['role']) ?></span>
                            <span class="badge" style="background:#e2e8f0; color:#475569; margin-left: 5px;"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td class="actions">
                            <a href="peminjamanRuangan.php?hapus_id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus pengguna ini?');"><i class="fas fa-trash"></i></a>
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
   BAGIAN 2: LOGIKA STANDAR (NON-SUPERADMIN)
   ================================================================================= */

// 1. Bersihkan dahulu session spesifik milik modul ruangan lama / agar tidak bentrok
unset($_SESSION['admin']);
unset($_SESSION['dosen']);
unset($_SESSION['mahasiswa']);
unset($_SESSION['users']);

// 2. Set parameter yang dikenali oleh fitur ruang (berdasarkan config/database.php modul tsb)
$_SESSION['email']   = $email;
$_SESSION['nama']    = $name;
$_SESSION['user_id'] = $email; // Default fallback fallback untuk integrasi ini

// 3. Konversi sistem Role lama -> Role Baru 
if ($role === 'dosen') {
    $_SESSION['users'] = true;
} elseif ($role === 'mahasiswa') {
    $_SESSION['users'] = true;
} else {
    // Jika rolenya tidak terdaftar
    echo "<script>alert('Akses Ditolak: Anda tidak terdaftar sebagai pengguna yang dapat masuk ke Sistem Peminjaman Ruangan ini.'); window.location='index.php';</script>";
    exit;
}

// 4. Integrasi sukses, arahkan ke index ruangan
header("Location: ruang/index.php");
exit;
?>