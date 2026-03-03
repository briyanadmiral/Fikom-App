<?php
session_start();

// Keamanan: Pastikan proses ini hanya berjalan jika sedang dalam mode impersonation
if (!isset($_SESSION['original_admin_email'])) {
    header("Location: index.php");
    exit();
}

// Ambil kembali email superadmin yang asli
$admin_email = $_SESSION['original_admin_email'];

// --- PROSES MENGEMBALIKAN SESI SUPERADMIN ---

// 1. Hapus penanda impersonation
unset($_SESSION['original_admin_email']);

// 2. Kembalikan semua data session superadmin (meniru login.php)
$_SESSION['logged_in']    = true;
$_SESSION['role']         = 'superadmin';
$_SESSION['user_email']   = $admin_email; // Kembalikan email asli
$_SESSION['user_name']    = 'Super Admin'; // Bisa hardcode atau ambil dari DB jika ada
$_SESSION['user_picture'] = 'https://i.pravatar.cc/150?u=' . $admin_email; // Ganti foto profil

// 3. Arahkan kembali ke dashboard superadmin
header("Location: superadmin/superadmin_home.php");
exit();