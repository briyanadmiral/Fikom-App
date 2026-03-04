<?php
session_start();

// --- SATPAM SESSION DARI BRIDGE ---
// Cek apakah tiket 'mou_admin' sudah ada?
if (!isset($_SESSION['mou_admin']) || $_SESSION['mou_admin'] !== true) {
    // Kalau belum punya tiket, tendang balik ke Bridge
    header("Location: ../mou.php");
    exit;
}

include 'koneksi.php';

$id_perencanaan = intval($_GET['id_perencanaan']);
$id_mou = intval($_GET['id_mou']);

// Ambil data dari perencanaan
$query = "SELECT * FROM perencanaan WHERE id_perencanaan = $id_perencanaan";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if ($data) {
    // Ambil data yang akan disalin
    $kegiatan = $data['keg_perencanaan'];
    $tanggal = $data['tanggal_rencana'];
    $pic = $data['pic_kegiatan'];

    // Cek apakah sudah ada data pelaksanaan yang sama untuk menghindari duplikasi
    $cek = mysqli_query($conn, "SELECT * FROM pelaksanaan WHERE id_mou = $id_mou AND nama_pelaksanaan = '$kegiatan'");
    if (mysqli_num_rows($cek) === 0) {
        // Masukkan ke tabel pelaksanaan
        $insert = mysqli_query($conn, "INSERT INTO pelaksanaan (id_mou, nama_pelaksanaan, tanggal_kegiatan, pic_kegiatan, status) 
            VALUES ($id_mou, '$kegiatan', " . ($tanggal ? "'$tanggal'" : "NULL") . ", " . ($pic ? "'$pic'" : "NULL") . ", 0)");

        if ($insert) {
            header("Location: perencanaan.php?id=$id_mou&msg=sukses");
            exit;
        } else {
            echo "Gagal memasukkan ke pelaksanaan: " . mysqli_error($conn);
        }
    } else {
        header("Location: perencanaan.php?id=$id_mou&msg=duplikat");
        exit;
    }
} else {
    echo "Data tidak ditemukan.";
}
?>
