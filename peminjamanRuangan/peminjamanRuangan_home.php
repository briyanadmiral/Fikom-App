<?php
session_start();

// --- 1. KEAMANAN: CEK VALIDASI JALUR (BRIDGE CHECK) ---
// Kita cek:
// a. Apakah user sudah login?
// b. Apakah user punya 'tiket' validasi (pr_validated) dari file bridge?
if(!isset($_SESSION['logged_in']) || !isset($_SESSION['pr_validated']) || $_SESSION['pr_validated'] !== true){
    // Jika user mencoba bypass (langsung ketik URL tanpa lewat bridge),
    // Tendang balik ke file Bridge (peminjamanRuangan.php) di folder luar
    header('Location: ../peminjamanRuangan.php'); 
    exit;
}

// --- 2. AMBIL DATA DARI SESSION SPESIFIK ---
// Kita tidak lagi pakai $_SESSION['admin'] yang umum.
// Kita pakai variable lokal yang diambil dari session ber-prefix 'pr_'
$name    = $_SESSION['user_name'];
$role    = $_SESSION['pr_role'] ?? 'guest';  // Nilainya: 'mahasiswa', 'dosen', atau 'admin'
$jurusan = $_SESSION['pr_jurusan'] ?? '-';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Peminjaman Ruangan - Home</title>
</head>
<body>
    <h2>Selamat datang di sistem Peminjaman Ruangan, <?php echo htmlspecialchars($name); ?></h2>
    
    <p>Status Akses: <strong><?php echo ucfirst($role); ?></strong> | Jurusan: <?php echo htmlspecialchars($jurusan); ?></p>
    <hr>

    <?php 
    /* -----------------------------------------------------------
       LOGIKA TAMPILAN MENU BERDASARKAN ROLE (pr_role)
       ----------------------------------------------------------- */
    ?>

    <?php if($role == 'mahasiswa'): ?>
       <div style="background-color: #e9ecef; padding: 10px; border-radius: 5px;">
           <p>Ini adalah menu untuk <b>Mahasiswa</b>.</p>
           <ul>
               <li><a href="form_pinjam.php">Buat Pengajuan Peminjaman</a></li>
               <li><a href="status_peminjaman.php">Lihat Status Peminjaman Saya</a></li>
           </ul>
       </div>
    <?php endif; ?>

    <?php if($role == 'dosen'): ?>
       <div style="background-color: #fff3cd; padding: 10px; border-radius: 5px;">
           <p>Ini adalah menu untuk <b>Dosen</b>.</p>
           <ul>
               <li><a href="form_pinjam.php">Buat Pengajuan Peminjaman</a></li>
               <li><a href="status_peminjaman.php">Lihat Status Peminjaman Saya</a></li>
           </ul>
       </div>
    <?php endif; ?>

    <?php if($role == 'admin'): ?>
       <div style="background-color: #d1e7dd; padding: 10px; border-radius: 5px;">
           <p>Ini adalah menu untuk <b>Admin Jurusan</b>.</p>
           <ul>
               <li><a href="kelola_pengajuan.php">Kelola Semua Pengajuan</a></li>
               <li><a href="jadwal_ruangan.php">Lihat Jadwal Ruangan</a></li>
               <li><a href="laporan.php">Laporan Peminjaman</a></li>
           </ul>
       </div>
    <?php endif; ?>

</body>
</html>