<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') { die("Akses ditolak."); }
if (!isset($_GET['id_dosen'])) { die("ID Dosen tidak valid."); }

$koneksi = mysqli_connect('localhost', 'root', '', 'fikomapp');

$target_dosen_id = (int)$_GET['id_dosen'];
$admin_email = $_SESSION['user_email'];

// Ambil data dosen
$query = "SELECT * FROM dosen WHERE id_dosen = $target_dosen_id LIMIT 1";
$result = mysqli_query($koneksi, $query);
$target_user = mysqli_fetch_assoc($result);

if (!$target_user) { die("Dosen tidak ditemukan."); }

// --- PROSES IMPERSONATE ---
$_SESSION['original_admin_email'] = $admin_email;

// Reset session lama
unset($_SESSION['nim']);
unset($_SESSION['program']);
unset($_SESSION['pr_role']); // Reset session bridge jika ada

// Set session baru
$_SESSION['logged_in']    = true;
$_SESSION['role']         = 'dosen';
$_SESSION['user_email']   = $target_user['email'];
$_SESSION['user_name']    = $target_user['nama'];
$_SESSION['user_picture'] = 'https://ui-avatars.com/api/?name='.urlencode($target_user['nama']); // Pakai avatar generate nama

// LOG KE DATABASE (Sesuai tabel impersonation_logs kamu)
// Kolom: id, admin_email, target_user_email, start_time (otomatis)
$target_email = $target_user['email'];
$log_query = "INSERT INTO impersonation_logs (admin_email, target_user_email) VALUES ('$admin_email', '$target_email')";
mysqli_query($koneksi, $log_query);

header("Location: ../index.php");
exit();
?>