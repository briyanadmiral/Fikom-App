<?php
$host = "localhost";
$username = "fike8938_fikom_app";
$password = "fikom#12345";
$database = "fike8938_fikom_surat";

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $username, $password, $database);

// Mengecek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
} else {
    echo "Koneksi database berhasil!";
}
?>