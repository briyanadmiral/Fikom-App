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

// Ambil id pelaksanaan dari URL
$id_pelaksanaan = intval($_GET['id']);

// Ambil data kegiatan pelaksanaan berdasarkan id_pelaksanaan
$query = mysqli_query($conn, "SELECT * FROM pelaksanaan WHERE id_pelaksanaan = $id_pelaksanaan");
$data = mysqli_fetch_assoc($query);

// Ambil id_mou untuk link kembali & menampilkan nama MOU
$id_mou = $data['id_mou'];

// Ambil nama MOU
$namaMouQuery = mysqli_query($conn, "SELECT * FROM mou WHERE id_mou = $id_mou");
$data_mou = mysqli_fetch_assoc($namaMouQuery);

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mou = intval($_POST['id_mou']);
    $nama_pelaksanaan = mysqli_real_escape_string($conn, $_POST['nama_pelaksanaan']);
    $tanggal_kegiatan = mysqli_real_escape_string($conn, $_POST['tanggal_kegiatan']);
    $tanggal_selesai = mysqli_real_escape_string($conn, $_POST['tanggal_selesai']);
    $pic_kegiatan = mysqli_real_escape_string($conn, $_POST['pic_kegiatan']);

    // Query UPDATE
    $update = mysqli_query($conn,
        "UPDATE pelaksanaan 
         SET nama_pelaksanaan='$nama_pelaksanaan',
             tanggal_kegiatan='$tanggal_kegiatan',
             tanggal_selesai='$tanggal_selesai',
             pic_kegiatan='$pic_kegiatan'
         WHERE id_pelaksanaan=$id_pelaksanaan"
    );

    if ($update) {
        echo "<script>alert('Kegiatan berhasil diperbarui.'); window.location.href='pelaksanaan.php?id=$id_mou';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui kegiatan.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Kegiatan Pelaksanaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container-fluid">
    <div class="row min-vh-100">
        <?php include 'sidebar.php'; ?>

        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Kegiatan Pelaksanaan</h1>
                <p class="text-muted mb-0">MOU: <?= htmlspecialchars($data_mou['nama_mou']) ?></p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="id_mou" value="<?= $id_mou ?>">

                        <div class="mb-3">
                            <label for="nama_pelaksanaan" class="form-label">Nama Kegiatan</label>
                            <input type="text" class="form-control" id="nama_pelaksanaan"
                                   name="nama_pelaksanaan"
                                   value="<?= htmlspecialchars($data['nama_pelaksanaan']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                            <input type="date" class="form-control" id="tanggal_kegiatan"
                                   name="tanggal_kegiatan"
                                   value="<?= $data['tanggal_kegiatan'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="tanggal_selesai"
                                   name="tanggal_selesai"
                                   value="<?= $data['tanggal_selesai'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="pic_kegiatan" class="form-label">PIC Kegiatan</label>
                            <input type="text" class="form-control" id="pic_kegiatan"
                                   name="pic_kegiatan"
                                   value="<?= htmlspecialchars($data['pic_kegiatan']) ?>" required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="pelaksanaan.php?id=<?= $id_mou ?>" class="btn btn-secondary me-2">Kembali</a>
                            <button type="submit" class="btn btn-primary">Perbarui</button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>
</body>
</html>
