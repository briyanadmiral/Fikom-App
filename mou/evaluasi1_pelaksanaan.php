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
// Status implementasi kegiatan - hardcoded options
$statusImplementasi = [
    1 => 'Sudah Selesai Terlaksana',
    2 => 'Belum Terlaksana',
    3 => 'Tidak Selesai Terlaksana',
];

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

// Ambil evaluasi yang sudah ada (kecuali yang sudah di-soft-delete)
$evaluasi_tersimpan = mysqli_query($conn, "
    SELECT ei.*
    FROM evaluasi_internal ei
    WHERE ei.id_pelaksanaan = $id_pelaksanaan
      AND (ei.deleted_at IS NULL)
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

      <!-- Alerts -->
      <?php if (isset($_GET['deleted_eval']) && $_GET['deleted_eval'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong>Sukses!</strong> Evaluasi internal berhasil dihapus.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['error_eval'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong>Gagal!</strong> <?= htmlspecialchars($_GET['error_eval']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

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
                <?php foreach ($statusImplementasi as $id => $label): ?>
                  <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
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
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($evaluasi_tersimpan)): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= nl2br(htmlspecialchars($row['evaluasi'])) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_evaluasi']) ?></td>
                    <td><?= htmlspecialchars($row['pemberi_evaluasi']) ?></td>
                    <td><?= htmlspecialchars($statusImplementasi[$row['id_ket_evaluasi']] ?? 'Tidak Diketahui') ?></td>
                    <td>
                      <?php if ($row['bukti']): ?>
                        <a href="<?= $row['bukti'] ?>" target="_blank">Lihat Bukti</a>
                      <?php else: ?>
                        <span class="text-muted">Tidak Ada</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <a href="hapus_evaluasi_internal.php?id=<?= $row['id_eval_internal'] ?>&id_pelaksanaan=<?= $id_pelaksanaan ?>"
                         class="btn btn-danger btn-sm"
                         onclick="return confirm('Yakin ingin menghapus evaluasi internal ini?')">
                        <i class="bi bi-trash"></i> Hapus
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
                <?php if ($no === 1): ?>
                  <tr><td colspan="7" class="text-center text-muted">Belum ada data evaluasi</td></tr>
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
