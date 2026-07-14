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

// Safety validation: Check pelaksanaan and perencanaan counts before deletion
$count_pelaksanaan_q = "SELECT COUNT(*) as total FROM pelaksanaan WHERE id_mou = $id";
$res_col1 = mysqli_query($conn, "SHOW COLUMNS FROM pelaksanaan LIKE 'deleted_at'");
if ($res_col1 && mysqli_num_rows($res_col1) > 0) {
    $count_pelaksanaan_q .= " AND deleted_at IS NULL";
}
$count_pelaksanaan = mysqli_fetch_assoc(mysqli_query($conn, $count_pelaksanaan_q))['total'] ?? 0;

$count_perencanaan_q = "SELECT COUNT(*) as total FROM perencanaan WHERE id_mou = $id";
$res_col2 = mysqli_query($conn, "SHOW COLUMNS FROM perencanaan LIKE 'deleted_at'");
if ($res_col2 && mysqli_num_rows($res_col2) > 0) {
    $count_perencanaan_q .= " AND deleted_at IS NULL";
}
$count_perencanaan = mysqli_fetch_assoc(mysqli_query($conn, $count_perencanaan_q))['total'] ?? 0;

if ($count_pelaksanaan > 0 || $count_perencanaan > 0) {
    header("Location: index.php?error=" . urlencode("Tidak dapat menghapus MOU karena masih memiliki data pelaksanaan atau perencanaan."));
    exit();
}

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