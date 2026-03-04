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

$id_perencanaan = intval($_GET['id']);
$id_mou = intval($_GET['id_mou']);

$delete = "DELETE FROM perencanaan WHERE id_perencanaan = $id_perencanaan";
mysqli_query($conn, $delete);
header("Location: perencanaan.php?id=$id_mou");
exit;
?>