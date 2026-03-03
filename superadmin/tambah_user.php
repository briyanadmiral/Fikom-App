<?php
// 1. WAJIB: Mulai session dan lakukan pengecekan keamanan
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../login.php'); 
    exit;
}

// Koneksi ke database
$koneksi = mysqli_connect('localhost', 'root', '', 'fikomapp') or die ('Koneksi ke database gagal');

// 2. PROSES FORM DENGAN PREPARED STATEMENTS (LEBIH AMAN)
if(isset($_POST['submit'])){
    // Ambil data dari form dan bersihkan
    $nip     = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $email   = mysqli_real_escape_string($koneksi, $_POST['email']);
    
    // Siapkan query template
    $query_insert = "INSERT INTO dosen (nip, nama, jurusan, email) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query_insert);
    
    // Bind parameter ke template
    mysqli_stmt_bind_param($stmt, "ssss", $nip, $nama, $jurusan, $email);
    
    // Eksekusi statement
    if(mysqli_stmt_execute($stmt)){
        echo "<script>
                alert('Data dosen berhasil disimpan!');
                document.location.href='superadmin_home.php';
              </script>";
    } else {
        echo "<script>
                alert('Data gagal disimpan. Terjadi kesalahan.');
                document.location.href='tambah_user.php';
              </script>";
    }
    // Tutup statement
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User Dosen - Superadmin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --body-bg: #f4f7fc;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-color: #495057;
            --border-color: #dee2e6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--body-bg); color: var(--text-color); }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background-color: var(--sidebar-bg); padding: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
        .main-content { margin-left: 260px; padding: 20px; }
        .sidebar .logo { text-align: center; margin-bottom: 40px; }
        .sidebar .logo img { max-width: 150px; }
        .sidebar .nav-links a { display: flex; align-items: center; padding: 12px 15px; margin-bottom: 10px; border-radius: 8px; text-decoration: none; color: var(--secondary-color); transition: background-color 0.2s, color 0.2s; }
        .sidebar .nav-links a i { margin-right: 15px; width: 20px; text-align: center; }
        .sidebar .nav-links a:hover, .sidebar .nav-links a.active { background-color: var(--primary-color); color: white; }
        .sidebar .logout-link { margin-top: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { font-size: 24px; font-weight: 600; }
        .card { background-color: var(--card-bg); border-radius: 12px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        .card-header { margin-bottom: 20px; }
        .card-header h2 { font-size: 18px; font-weight: 600; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        
        /* CSS Khusus untuk Form */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 500; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.85; }
        .btn-success { background-color: var(--success-color); color: white; }
        .btn-secondary { background-color: var(--secondary-color); color: white; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo">
            <img src="../assets/img/fikom.png" alt="Logo Fikom"> 
        </div>
        <nav class="nav-links">
            <a href="superadmin_home.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="superadmin_home.php" class="active"><i class="fas fa-users"></i> Manajemen Dosen</a>
            <a href="#"><i class="fas fa-user-graduate"></i> Manajemen Mahasiswa</a>
            <a href="#"><i class="fas fa-cog"></i> Pengaturan</a>
        </nav>
        <div class="logout-link">
             <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Manajemen Dosen</h1>
        </header>

        <div class="card">
            <div class="card-header">
                <h2>Formulir Tambah Dosen Baru</h2>
            </div>
            
            <form action="tambah_user.php" method="post">
                <div class="form-group">
                    <label for="nip">NIP (Nomor Induk Pegawai)</label>
                    <input type="text" name="nip" id="nip" placeholder="Masukkan NIP dosen" required>
                </div>
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" placeholder="Masukkan nama lengkap dengan gelar" required>
                </div>
                <div class="form-group">
                    <label for="jurusan">Jurusan</label>
                    <input type="text" name="jurusan" id="jurusan" placeholder="Contoh: Sistem Informasi">
                </div>
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" placeholder="Gunakan email institusi (@unika.ac.id)" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                    <a href="superadmin_home.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>