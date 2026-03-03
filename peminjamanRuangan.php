<?php
session_start();
if(!isset($_SESSION['logged_in'])){
    header('Location: login.php'); exit;
}

include 'koneksi.php';

$email   = $_SESSION['user_email'];
$role    = $_SESSION['role']; 

// 1. BERSIHKAN DULU Session lama (Supaya tidak tabrakan)
// Hapus semua session yang berawalan 'pr_' (Peminjaman Ruangan)
unset($_SESSION['pr_role']);
unset($_SESSION['pr_jurusan']);
unset($_SESSION['pr_validated']);

/* -----------------------------------------
   LOGIKA PENENTUAN HAK AKSES
   ----------------------------------------- */

// Default: Tidak punya akses
$akses_diterima = false;
$role_di_fitur_ini = 'guest';
$jurusan_di_fitur_ini = '';

// Cek Logika Mahasiswa
if($role == 'mahasiswa') {
    $akses_diterima = true;
    $role_di_fitur_ini = 'mahasiswa';
    $jurusan_di_fitur_ini = $_SESSION['program'];
}
// Cek Logika Dosen / Tendik (Cek ke Database Spesifik)
elseif($role == 'dosen') {
    // Gunakan Prepared Statement (Sudah Bagus!)
    $stmt = mysqli_prepare($conn, "SELECT role, jurusan FROM t_peminjamanruangan WHERE email = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if($data){
        $akses_diterima = true;
        // Ambil role dari tabel: 'admin' atau 'dosen_biasa'
        $role_di_fitur_ini = $data['role']; 
        $jurusan_di_fitur_ini = $data['jurusan'];
    }
}

/* -----------------------------------------
   FINALISASI SESSION (PEMATANGAN)
   ----------------------------------------- */

if ($akses_diterima) {
    // 2. Set Session dengan NAMA UNIK (Namespace)
    $_SESSION['pr_role']      = $role_di_fitur_ini; // isinya: 'mahasiswa', 'admin', atau 'dosen'
    $_SESSION['pr_jurusan']   = $jurusan_di_fitur_ini;
    
    // 3. Beri "STEMPEL" Validasi
    $_SESSION['pr_validated'] = true;

    // Lempar ke sistem teman
    header("Location: peminjamanRuangan/peminjamanRuangan_home.php");
    exit;
} else {
    // Jika ditolak
    echo "<script>alert('Akses Ditolak: Anda tidak terdaftar di sistem Peminjaman Ruangan.'); window.location='index.php';</script>";
    exit;
}
?>