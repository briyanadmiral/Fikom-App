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

// Get MOU ID
$id = $_GET['id'] ?? 0;

// Get file info before deletion
$query = "SELECT file FROM mou WHERE id_mou = $id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// Delete query
$delete_query = "DELETE FROM mou WHERE id_mou = $id";

if (mysqli_query($conn, $delete_query)) {
    // Delete associated file if exists
    if (!empty($data['file']) && file_exists("file_mou/" . $data['file'])) {
        unlink("file_mou/" . $data['file']);
    }
    
    // Also delete related records in other tables if needed
    // Example: DELETE FROM pelaksanaan WHERE id_mou = $id
    // Example: DELETE FROM perencanaan WHERE id_mou = $id
    header("Location: index.php?deleted=1");
} else {
    header("Location: index.php?error=" . urlencode(mysqli_error($conn)));
}

exit();