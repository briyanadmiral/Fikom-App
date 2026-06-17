<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "fike8938_fikom_mou";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
