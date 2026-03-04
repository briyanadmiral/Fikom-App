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

$id_pelaksanaan = intval($_GET['id']);
$id_mou = intval($_GET['id_mou']);

// Soft delete approach (recommended)
$query = "UPDATE pelaksanaan SET deleted_at = NOW() WHERE id_pelaksanaan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pelaksanaan);

if ($stmt->execute()) {
    header("Location: pelaksanaan.php?id=$id_mou&deleted=1");
} else {
    header("Location: pelaksanaan.php?id=$id_mou&error=" . urlencode(mysqli_error($conn)));
}

$stmt->close();

// Alternative hard delete approach (uncomment if you prefer this)
/*
$query = "DELETE FROM pelaksanaan WHERE id_pelaksanaan = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pelaksanaan);

if ($stmt->execute()) {
    header("Location: pelaksanaan.php?id=$id_mou&deleted=1");
} else {
    header("Location: pelaksanaan.php?id=$id_mou&error=" . urlencode(mysqli_error($conn)));
}
$stmt->close();
*/
?>