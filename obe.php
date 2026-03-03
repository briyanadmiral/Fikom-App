<?php
session_start();
if(!isset($_SESSION['logged_in'])){
    header('Location: index.php'); exit;
}

include 'koneksi.php';

$email   = $_SESSION['user_email'];
$role    = $_SESSION['role']; // Ini role umum: 'mahasiswa' atau 'dosen'
$program = $_SESSION['program'] ?? null;

/* -----------------------------------------
   Buat session spesifik sesuai role + jurusan
   ----------------------------------------- */

// Mahasiswa (Bagian ini sudah benar)
if($role == 'mahasiswa') {
    if($program == 'siega')       $_SESSION['mahasiswa_siega'] = true;
    if($program == 'informatika')  $_SESSION['mahasiswa_ti']    = true;
}

// Dosen (Bagian ini diperbaiki total)
if($role == 'dosen') {
    // KITA CEK HAK AKSES SPESIFIK DOSEN DARI TABEL FITUR (t_obe)
    $stmt = mysqli_prepare($conn, "SELECT role, jurusan FROM t_obe WHERE email = ? AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $hak_akses = mysqli_fetch_assoc($result);

    if($hak_akses){
        // Data hak akses ditemukan di t_obe, gunakan ini
        $role_spesifik = $hak_akses['role'];    // akan berisi 'admin' atau 'dosen'
        $jurusan       = $hak_akses['jurusan']; // akan berisi 'siega' atau 'informatika'

        if($role_spesifik == 'admin' && $jurusan == 'siega')       $_SESSION['admin_siega'] = true;
        if($role_spesifik == 'admin' && $jurusan == 'informatika')  $_SESSION['admin_ti']    = true;
        if($role_spesifik == 'dosen' && $jurusan == 'siega')       $_SESSION['dosen_siega'] = true;
        if($role_spesifik == 'dosen' && $jurusan == 'informatika')  $_SESSION['dosen_ti']    = true;
        
    } else {
        // JIKA TIDAK DITEMUKAN: Dosen ini tidak punya hak akses ke fitur OBE.
        // Arahkan kembali atau tampilkan pesan error.
        echo "<script>
                alert('Anda tidak memiliki hak akses untuk fitur OBE.');
                window.history.back();
              </script>";
        exit;
    }
}

/* ---------------------------------------------------
   Lanjut ke halaman utama OBE
   --------------------------------------------------- */
header("Location: obe/obe_home.php");
exit;