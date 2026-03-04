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

// Ambil id MOU dari URL
$id_mou = intval($_GET['id']);

// Ambil nama MOU
$namaSurat = mysqli_query($conn, "SELECT * FROM mou WHERE id_mou = $id_mou");
$data_mou = mysqli_fetch_assoc($namaSurat);

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mou          = intval($_POST['id_mou']);
    $kegiatan        = mysqli_real_escape_string($conn, $_POST['kegiatan']);
    $tanggal_rencana = mysqli_real_escape_string($conn, $_POST['tanggal_rencana']);
    $pic_kegiatan    = mysqli_real_escape_string($conn, $_POST['pic_kegiatan']);
    $keterangan      = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $insert = mysqli_query($conn, 
        "INSERT INTO perencanaan (id_mou, keg_perencanaan, tanggal_rencana, pic_kegiatan, ket)
         VALUES ($id_mou, '$kegiatan', '$tanggal_rencana', '$pic_kegiatan', '$keterangan')"
    );

    if ($insert) {
        echo "<script>alert('Perencanaan berhasil ditambahkan.'); window.location.href='perencanaan.php?id=$id_mou';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan perencanaan.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Perencanaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container-fluid">
    <div class="row min-vh-100">
        <?php include 'sidebar.php'; ?>
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Tambah Perencanaan</h1>
                <p class="text-muted mb-0">MOU: <?= htmlspecialchars($data_mou['nama_mou']) ?></p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id_mou" value="<?= $id_mou ?>">

                        <div class="mb-3">
                            <label for="kegiatan" class="form-label">Kegiatan Perencanaan</label>
                            <input type="text" class="form-control" id="kegiatan" name="kegiatan" required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_rencana" class="form-label">Tanggal Rencana</label>
                            <input type="date" class="form-control" id="tanggal_rencana" name="tanggal_rencana" required>
                        </div>

                        <div class="mb-3">
                            <label for="pic_kegiatan" class="form-label">PIC Kegiatan</label>
                            <input type="text" class="form-control" id="pic_kegiatan" name="pic_kegiatan" required>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="perencanaan.php?id=<?= $id_mou ?>" class="btn btn-secondary me-2">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
