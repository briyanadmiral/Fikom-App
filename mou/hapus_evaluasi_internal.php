<?php
session_start();

// --- SATPAM SESSION DARI BRIDGE ---
if (!isset($_SESSION['mou_admin']) || $_SESSION['mou_admin'] !== true) {
    header("Location: ../mou.php");
    exit;
}

include 'koneksi.php';

$id_eval = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_pelaksanaan = isset($_GET['id_pelaksanaan']) ? intval($_GET['id_pelaksanaan']) : 0;

if ($id_eval > 0 && $id_pelaksanaan > 0) {
    // Soft delete by updating deleted_at column
    $query = "UPDATE evaluasi_internal SET deleted_at = NOW() WHERE id_eval_internal = $id_eval";
    if (mysqli_query($conn, $query)) {
        header("Location: evaluasi1_pelaksanaan.php?id=$id_pelaksanaan&deleted_eval=1");
    } else {
        header("Location: evaluasi1_pelaksanaan.php?id=$id_pelaksanaan&error_eval=" . urlencode(mysqli_error($conn)));
    }
} else {
    header("Location: index.php");
}
exit();
