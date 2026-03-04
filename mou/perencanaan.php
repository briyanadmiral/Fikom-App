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

// Aktifkan Bahasa Indonesia untuk tanggal
setlocale(LC_TIME, 'id_ID.utf8');

// Ambil id MOU dari URL
$id_mou = intval($_GET['id']);

// Ambil nama MOU
$namaSurat = mysqli_query($conn, "SELECT * FROM mou WHERE id_mou = $id_mou");
$data_mou = mysqli_fetch_assoc($namaSurat);

// Setup pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filter pencarian
$search = '';
$search_condition = '';
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_condition = " AND (id_perencanaan LIKE '%$search%' OR keg_perencanaan LIKE '%$search%')";
}

// Hitung total data
$count_query = "SELECT COUNT(*) AS total FROM perencanaan WHERE id_mou = $id_mou $search_condition";
$count_result = mysqli_fetch_assoc(mysqli_query($conn, $count_query));
$total_data = $count_result['total'];
$total_pages = ceil($total_data / $limit);

// Query data utama
$query = "SELECT * FROM perencanaan WHERE id_mou = $id_mou $search_condition ORDER BY id_perencanaan ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perencanaan MOU</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .action-buttons {
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row min-vh-100">
      <?php include 'sidebar.php'; ?>

      <main class="col-md-10 ms-sm-auto px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Perencanaan MOU: <?= htmlspecialchars($data_mou['nama_mou']) ?></h1>
        </div>

        <!-- Search & Add Button -->
        <div class="d-flex justify-content-between align-items-end mb-4">
          <!-- Form Filter -->
          <form method="GET" class="row g-2 align-items-end flex-grow-1">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id_mou) ?>">
            <div class="col-auto">
              <label for="search" class="form-label">Cari Perencanaan</label>
              <input type="text" id="search" name="search" class="form-control" placeholder="No atau Nama Kegiatan" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Search
              </button>
              <a href="perencanaan.php?id=<?= $id_mou ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
              </a>
              <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
              </a>
            </div>
          </form>

          <!-- Tombol Tambah -->
          <div class="ms-auto">
            <a href="tambah_perencanaan.php?id=<?= $id_mou ?>" class="btn btn-success">
              <i class="bi bi-plus-circle"></i> ADD Perencanaan
            </a>
          </div>
        </div>

        <!-- Tabel Perencanaan -->
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-dark text-center">
              <tr>
                <th>No</th>
                <th>Kegiatan Perencanaan</th>
                <th>Tanggal Rencana</th>
                <th>PIC Kegiatan</th>
                <th>Keterangan</th>
                <th class="action-buttons">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <?php
              $no = $offset + 1;
              while ($row = mysqli_fetch_assoc($result)):
                $tgl_rencana = strtotime($row['tanggal_rencana']);
              ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['keg_perencanaan']) ?></td>
                <td><?= $row['tanggal_rencana'] ? date('d F Y', $tgl_rencana) : '-' ?></td>
                <td><?= htmlspecialchars($row['pic_kegiatan']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['ket'])) ?></td>
                <td class="action-buttons">
                  <div class="d-flex justify-content-center gap-2">
                    <a href="edit_perencanaan.php?id=<?= $row['id_perencanaan'] ?>&id_mou=<?= $id_mou ?>" 
                       class="btn btn-warning btn-sm" title="Edit">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus_perencanaan.php?id=<?= $row['id_perencanaan'] ?>&id_mou=<?= $id_mou ?>"
                       class="btn btn-danger btn-sm" 
                       title="Hapus"
                       onclick="return confirm('Yakin ingin menghapus perencanaan <?= htmlspecialchars(addslashes($row['keg_perencanaan'])) ?>?')">
                      <i class="bi bi-trash"></i>
                    </a>
                    <a href="salin_ke_pelaksanaan.php?id_perencanaan=<?= $row['id_perencanaan'] ?>&id_mou=<?= $id_mou ?>"
                       class="btn btn-info btn-sm" title="Salin ke Pelaksanaan"
                       onclick="return confirm('Salin kegiatan ini ke pelaksanaan?')">
                      <i class="bi bi-arrow-down-square"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
              <?php if (mysqli_num_rows($result) === 0): ?>
              <tr><td colspan="6" class="text-muted">Tidak ada data perencanaan.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
