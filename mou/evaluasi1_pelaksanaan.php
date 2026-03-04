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

$id_pelaksanaan = $_GET['id'] ?? 0;
$kegiatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM pelaksanaan WHERE id_pelaksanaan = $id_pelaksanaan"));
$keteranganList = mysqli_query($conn, "SELECT * FROM keterangan_evaluasi");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evaluasi = mysqli_real_escape_string($conn, $_POST['evaluasi']);
    $status = intval($_POST['status']);
    $tanggal = $_POST['tanggal_evaluasi'];
    $pemberi = mysqli_real_escape_string($conn, $_POST['pemberi_evaluasi']);

    $bukti = '';
    if (!empty($_FILES['bukti']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $bukti = $target_dir . basename($_FILES["bukti"]["name"]);
        move_uploaded_file($_FILES["bukti"]["tmp_name"], $bukti);
    }

    $insert = "INSERT INTO evaluasi_internal (id_pelaksanaan, evaluasi, tanggal_evaluasi, pemberi_evaluasi, id_ket_evaluasi, bukti)
               VALUES ($id_pelaksanaan, '$evaluasi', '$tanggal', '$pemberi', $status, '$bukti')";
    mysqli_query($conn, $insert);
    header("Location: evaluasi1_pelaksanaan.php?id=$id_pelaksanaan");
    exit;
}

// Ambil evaluasi yang sudah ada
$evaluasi_tersimpan = mysqli_query($conn, "
    SELECT ei.*, ke.ket_evaluasi 
    FROM evaluasi_internal ei
    JOIN keterangan_evaluasi ke ON ei.id_ket_evaluasi = ke.id_ket_evaluasi
    WHERE ei.id_pelaksanaan = $id_pelaksanaan
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Evaluasi Internal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container-fluid">
  <div class="row min-vh-100">
    <?php include 'sidebar.php'; ?>
    <main class="col-md-10 ms-sm-auto px-md-4">
      
      <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Evaluasi Internal</h1>
        <p class="text-muted mb-0">Kegiatan: <?= htmlspecialchars($kegiatan['nama_pelaksanaan'] ?? 'Kegiatan Tidak Ditemukan') ?></p>
      </div>

      <!-- Form Evaluasi -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="evaluasi" class="form-label">Evaluasi</label>
              <textarea name="evaluasi" id="evaluasi" rows="4" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
              <label for="tanggal_evaluasi" class="form-label">Tanggal Evaluasi</label>
              <input type="date" name="tanggal_evaluasi" id="tanggal_evaluasi" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="pemberi_evaluasi" class="form-label">Pemberi Evaluasi</label>
              <input type="text" name="pemberi_evaluasi" id="pemberi_evaluasi" class="form-control" required>
            </div>

            <div class="mb-3">
              <label for="status" class="form-label">Status Implementasi</label>
              <select name="status" id="status" class="form-select" required>
                <option value="">-- Pilih Status --</option>
                <?php while ($row = mysqli_fetch_assoc($keteranganList)): ?>
                  <option value="<?= $row['id_ket_evaluasi'] ?>"><?= $row['ket_evaluasi'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="bukti" class="form-label">Upload Bukti</label>
              <input type="file" name="bukti" id="bukti" class="form-control">
            </div>

            <div class="d-flex justify-content-end">
              <a href="pelaksanaan.php?id=<?= $kegiatan['id_mou'] ?>" class="btn btn-secondary me-2">Kembali</a>
              <button type="submit" class="btn btn-primary">Simpan Evaluasi</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tabel Riwayat -->
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3">Riwayat Evaluasi</h5>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Evaluasi</th>
                  <th>Tanggal</th>
                  <th>Pemberi</th>
                  <th>Status</th>
                  <th>Bukti</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($evaluasi_tersimpan)): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= nl2br(htmlspecialchars($row['evaluasi'])) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_evaluasi']) ?></td>
                    <td><?= htmlspecialchars($row['pemberi_evaluasi']) ?></td>
                    <td><?= $row['ket_evaluasi'] ?></td>
                    <td>
                      <?php if ($row['bukti']): ?>
                        <a href="<?= $row['bukti'] ?>" target="_blank">Lihat Bukti</a>
                      <?php else: ?>
                        <span class="text-muted">Tidak Ada</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
                <?php if ($no === 1): ?>
                  <tr><td colspan="6" class="text-center text-muted">Belum ada data evaluasi</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>
</body>
</html>
