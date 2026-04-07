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

setlocale(LC_TIME, 'id_ID.utf8');

$id_mou = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_mou <= 0) {
    header("Location: index.php");
    exit;
}

$namaSurat = mysqli_query($conn, "SELECT * FROM mou WHERE id_mou = $id_mou");
$data_mou = mysqli_fetch_assoc($namaSurat);

if (!$data_mou) {
    echo "<script>alert('Data MOU tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search = '';
$search_condition = '';
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_condition = " AND (id_pelaksanaan LIKE '%$search%' OR nama_pelaksanaan LIKE '%$search%')";
}

$count_query = "SELECT COUNT(*) AS total FROM pelaksanaan WHERE id_mou = $id_mou AND deleted_at IS NULL $search_condition";
$count_result = mysqli_fetch_assoc(mysqli_query($conn, $count_query));
$total_data = $count_result['total'];
$total_pages = ceil($total_data / $limit);

$query = "SELECT * FROM pelaksanaan WHERE id_mou = $id_mou AND deleted_at IS NULL $search_condition ORDER BY id_pelaksanaan ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pelaksanaan MOU</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/glass.css?v=<?= time() ?>">
</head>
<body>
<div class="container-fluid">
  <div class="row min-vh-100">
    <?php include 'sidebar.php'; ?>

    <main class="col-md-10 ms-sm-auto px-md-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Pelaksanaan MOU: <?= htmlspecialchars($data_mou['nama_mou']) ?></h1>
      </div>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Data pelaksanaan berhasil diperbarui!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          Data pelaksanaan berhasil dihapus!
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_GET['error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="d-flex justify-content-between align-items-end mb-4">
        <form method="GET" class="row g-2 align-items-end flex-grow-1">
          <input type="hidden" name="id" value="<?= htmlspecialchars($id_mou) ?>">
          <div class="col-auto">
            <label for="search" class="form-label">Cari Kegiatan</label>
            <input type="text" id="search" name="search" class="form-control" placeholder="No atau Nama Kegiatan" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-search"></i> Search
            </button>
            <a href="pelaksanaan.php?id=<?= $id_mou ?>" class="btn btn-secondary">
              <i class="bi bi-arrow-counterclockwise"></i> Reset
            </a>
            <a href="index.php" class="btn btn-secondary">
              <i class="bi bi-arrow-left"></i> Kembali
            </a>
          </div>
        </form>
        <div class="ms-auto">
          <a href="tambah_pelaksanaan.php?id=<?= $id_mou ?>" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> ADD Pelaksanaan
          </a>
        </div>
      </div>

      <div class="card border-0 shadow-none overflow-hidden">
        <div class="card-body p-0">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Pelaksanaan</th>
                <th>Tanggal Kegiatan</th>
                <th>Tanggal Selesai</th>
                <th>PIC Kegiatan</th>
                <th>Evaluasi Internal</th>
                <th>Evaluasi Eksternal</th>
                <th>Aksi</th>
              </tr>
            </thead>
          <tbody class="text-center">
            <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($result)):
              $id_pelaksanaan = $row['id_pelaksanaan'];
              $count_internal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM evaluasi_internal WHERE id_pelaksanaan = $id_pelaksanaan"))['total'];
              $count_eksternal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM evaluasi_eksternal WHERE id_pelaksanaan = $id_pelaksanaan"))['total'];
            ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['nama_pelaksanaan']) ?></td>
              <td><?= date('d F Y', strtotime($row['tanggal_kegiatan'])) ?></td>
              <td>
                  <?php 
                  if ($row['tanggal_selesai']) {
                      echo date('d F Y', strtotime($row['tanggal_selesai']));
                  } else {
                      echo "-";
                  }
                  ?>
              </td>
              <td><?= htmlspecialchars($row['pic_kegiatan']) ?></td>
              <td>
                <a href="evaluasi1_pelaksanaan.php?id=<?= $id_pelaksanaan ?>" class="btn btn-outline-primary btn-sm">
                  <?= $count_internal ?> data
                </a>
              </td>
              <td>
                <a href="evaluasi2_pelaksanaan.php?id=<?= $id_pelaksanaan ?>" class="btn btn-outline-primary btn-sm">
                  <?= $count_eksternal ?> data
                </a>
              </td>
              <td>
                  <div class="d-flex justify-content-center gap-2">
                      <!-- Tombol Edit -->
                      <a href="edit_pelaksanaan.php?id=<?= $id_pelaksanaan ?>&id_mou=<?= $id_mou ?>" 
                        class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                      </a>

                      <!-- Logika tombol hapus -->
                      <?php if ($count_internal == 0 && $count_eksternal == 0): ?>
                          <!-- Tombol hapus aktif -->
                          <a href="hapus_pelaksanaan.php?id=<?= $id_pelaksanaan ?>&id_mou=<?= $id_mou ?>" 
                            class="btn btn-danger btn-sm"
                            title="Hapus"
                            onclick="return confirm('Apakah Anda yakin ingin menghapus kegiatan <?= htmlspecialchars(addslashes($row['nama_pelaksanaan'])) ?>?')">
                            <i class="bi bi-trash"></i>
                          </a>
                      <?php else: ?>
                          <!-- Tombol hapus terkunci -->
                          <button class="btn btn-secondary btn-sm" title="Tidak dapat dihapus" disabled>
                            <i class="bi bi-trash"></i>
                          </button>
                      <?php endif; ?>

                  </div>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) === 0): ?>
              <tr><td colspan="7" class="text-muted">Tidak ada data kegiatan.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <nav>
        <ul class="pagination justify-content-center mt-4">
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?id=<?= $id_mou ?>&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
            </li>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
              <a class="page-link" href="?id=<?= $id_mou ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <li class="page-item">
              <a class="page-link" href="?id=<?= $id_mou ?>&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => new bootstrap.Alert(alert).close());
  }, 5000);
</script>
</body>
</html>
