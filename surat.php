<?php
session_start();

// 1. Cek apakah sudah login Google
if(!isset($_SESSION['logged_in'])){
    header('Location: login.php'); 
    exit;
}

$email = $_SESSION['user_email'];

// 2. Koneksi ke Database Laravel (surat_siega)
// Kita harus mencari tahu berapa 'id' user ini di database surat
$conn_surat = mysqli_connect('localhost', 'root', '', 'surat_siega'); // Sesuaikan nama DB-nya jika beda
if (!$conn_surat) {
    die("Koneksi ke database surat_siega gagal.");
}

// Cari user berdasarkan email Google
$stmt = mysqli_prepare($conn_surat, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_surat = mysqli_fetch_assoc($result);

if ($user_surat) {
    // === USER DITEMUKAN ===
    $userId = $user_surat['id'];

    // 3. Buat Token Keamanan Harian
    // PENTING: Kata sandi rahasia ini HARUS SAMA dengan yang ada di Laravel temanmu!
    $sharedSecret = '7bf5429f72beebd2f98b046e4527d46e83ba56f161e0508fb97fa33615b413f1'; 

    // Rumus rahasia dari Laravel: hash_hmac('sha256', ID + Tanggal Hari Ini, Secret Key)
    $token = hash_hmac('sha256', $userId . date('Y-m-d'), $sharedSecret);

    // 4. Buka Pintu Rahasia Laravel
    // Sesuaikan URL-nya dengan cara kamu membuka laravel di browser lokal
    // Tambahkan /fikomapp/ di depannya karena sekarang dia ada di dalam folder fikomapp
$url_tujuan = "http://localhost/fikomapp/surat_siega/public/entry?user_id=" . $userId . "&token=" . $token;

    // Alihkan langsung ke sistem surat
    header("Location: " . $url_tujuan);
    exit;

} else {
    // === USER TIDAK ADA DI DATABASE SURAT ===
    echo "<script>
            alert('Akses Ditolak: Email Anda belum didaftarkan di Sistem Surat Siega.');
            window.location='index.php';
          </script>";
    exit;
}
?>