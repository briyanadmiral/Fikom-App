<?php
session_start();

// Cek Login Utama
if(!isset($_SESSION['logged_in'])){
    header('Location: index.php'); exit;
}

include 'koneksi.php'; // Koneksi ke Database Utama (FIKOMAPP)

$email = $_SESSION['user_email'];
$role  = $_SESSION['role']; 

// Validasi Dosen
if($role == 'dosen') {
    
    // Cek di tabel t_mou (Database Utama)
    $stmt = mysqli_prepare($conn, "SELECT jurusan FROM t_mou WHERE email = ? AND role = 'admin' AND deleted_at IS NULL LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $hak_akses = mysqli_fetch_assoc($result);

    if($hak_akses){
        // === SUKSES ===
        // Kita beri nama session yang SPESIFIK 'mou_' biar gak ketuker
        $_SESSION['mou_admin']   = true;        // Tiket Masuk
        $_SESSION['mou_jurusan'] = $hak_akses['jurusan'];
        $_SESSION['mou_email']   = $email;      // Buat log di sistem teman (kalau perlu)
        
        // Redirect ke sistem teman (Pastikan namanya index.php sesuai list file temanmu)
        header("Location: mou/index.php"); 
        exit;
        
    } else {
        // Dosen tapi bukan Admin MoU
        echo "<script>
                alert('Akses Ditolak: Email Anda tidak terdaftar sebagai Admin MoU.');
                window.location='index.php';
              </script>";
        exit;
    }

} else {
    // Mahasiswa / Role lain
    echo "<script>
            alert('Akses Ditolak: Fitur ini hanya untuk Dosen Admin MoU.');
            window.location='index.php';
          </script>";
    exit;
}
?>