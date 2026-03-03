<?php 
session_start();

// Cek Superadmin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../login.php'); exit;
}

// Koneksi
$koneksi = mysqli_connect('localhost', 'root', '', 'fikomapp');

if (isset($_GET['id_dosen'])) {
    $id_dosen = $_GET['id_dosen'];

    // Hapus dari tabel 'dosen' (sesuai DB kamu)
    $stmt = mysqli_prepare($koneksi, "DELETE FROM dosen WHERE id_dosen = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_dosen);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "<script>alert('✅ Data berhasil dihapus'); window.location.href='superadmin_home.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal hapus. ID tidak ditemukan.'); window.location.href='superadmin_home.php';</script>";
    }
} else {
    header('Location: superadmin_home.php');
}
?>