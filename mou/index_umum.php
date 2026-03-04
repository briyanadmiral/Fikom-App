<?php
include 'koneksi.php';

// helper: cek apakah kolom ada
function columnExists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($res && mysqli_num_rows($res) > 0);
}

$hasDeletedOnMou = columnExists($conn, 'mou', 'deleted_at');
$hasDeletedOnPelaksanaan = columnExists($conn, 'pelaksanaan', 'deleted_at');

/* ========================
   Pagination
======================== */
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

/* ========================
   Filter (TIDAK DIUBAH)
======================== */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$bulan  = isset($_GET['bulan']) && $_GET['bulan'] !== '' ? intval($_GET['bulan']) : '';
$tahun  = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? intval($_GET['tahun']) : date('Y');

/* ========================
   Statistik
======================== */
$total_mou_q = "SELECT COUNT(*) FROM mou";
if ($hasDeletedOnMou) $total_mou_q .= " WHERE deleted_at IS NULL";
$total_mou = mysqli_fetch_row(mysqli_query($conn, $total_mou_q))[0] ?? 0;

$total_finish_q = "
    SELECT COUNT(DISTINCT m.id_mou)
    FROM mou m
    JOIN pelaksanaan p ON m.id_mou = p.id_mou
    JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
    JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
    WHERE ei.id_ket_evaluasi = 1
      AND ee.id_ket_evaluasi = 1
";
if ($hasDeletedOnPelaksanaan) $total_finish_q .= " AND p.deleted_at IS NULL";
$total_finish = mysqli_fetch_row(mysqli_query($conn, $total_finish_q))[0] ?? 0;

$total_unfinish = max(0, $total_mou - $total_finish);

/* ========================
   WHERE filter (SAMA)
======================== */
$where_clauses = [];

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(no_mou LIKE '%$s%' OR nama_mou LIKE '%$s%')";
}
if ($bulan !== '') {
    $where_clauses[] = "MONTH(tgl_mou) = $bulan";
}
if ($tahun !== '') {
    $where_clauses[] = "YEAR(tgl_mou) = $tahun";
}
if ($hasDeletedOnMou) {
    $where_clauses[] = "deleted_at IS NULL";
}

$where = count($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

/* ========================
   Pagination count
======================== */
$total_data_q = "SELECT COUNT(*) FROM mou $where";
$total_data = mysqli_fetch_row(mysqli_query($conn, $total_data_q))[0] ?? 0;
$total_pages = max(1, ceil($total_data / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

/* ========================
   Data
======================== */
$query = "SELECT * FROM mou $where ORDER BY tgl_mou ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar MOU</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<div class="container-fluid">
  <div class="row min-vh-100">

    <?php include 'sidebar.php'; ?>

    <main class="col-md-10 ms-sm-auto px-md-4">

      <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar MOU</h1>
      </div>

      <!-- Statistik -->
      <div class="row mb-4">
        <div class="col-md-6 mb-3">
          <div class="card bg-primary text-white">
            <div class="card-body">
              <h5>Total MOU</h5>
              <p class="display-6"><?= $total_mou ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="card bg-success text-white">
            <div class="card-body">
              <h5>MOU Selesai</h5>
              <p class="display-6"><?= $total_finish ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================
           FILTER (UTUH)
      ======================= -->
      <div class="d-flex justify-content-between align-items-end mb-4">
        <form method="GET" class="row g-2 mb-4">
          <div class="col-auto">
            <input type="text" name="search" class="form-control"
                   placeholder="Search..."
                   value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="col-auto">
            <select name="bulan" class="form-select">
              <option value="">Semua Bulan</option>
              <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= ($bulan == $i) ? 'selected' : '' ?>>
                  <?= date('F', mktime(0,0,0,$i,10)) ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-auto">
            <input type="number" name="tahun" class="form-control"
                   placeholder="Tahun"
                   value="<?= htmlspecialchars($tahun) ?>">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="index_umum.php" class="btn btn-secondary">Reset</a>
          </div>
        </form>
      </div>

      <!-- TABEL -->
      <table class="table table-striped text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>No MOU (Eksternal)</th>
            <th>No MOU (Internal)</th>
            <th>Nama MOU</th>
            <th>Pihak 1</th>
            <th>Pihak 2</th>
            <th>Tingkat</th>
            <th>Tanggal</th>
            <th>File</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $no = $offset + 1;
        while ($row = mysqli_fetch_assoc($result)):
        ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['no_mou_eks']) ?></td>
            <td><?= htmlspecialchars($row['no_mou']) ?></td>
            <td><?= htmlspecialchars($row['nama_mou']) ?></td>
            <td><?= htmlspecialchars($row['pihak_1']) ?></td>
            <td><?= htmlspecialchars($row['pihak_2']) ?></td>
            <td><?= htmlspecialchars($row['tingkat']) ?></td>
            <td><?= date('d F Y', strtotime($row['tgl_mou'])) ?></td>
            <td>
              <?php if (!empty($row['file'])): ?>
                <a href="file_mou/<?= htmlspecialchars($row['file']) ?>"
                   target="_blank"
                   class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-file-earmark-pdf"></i> Lihat
                </a>
              <?php else: ?>
                <span class="text-muted">Tidak Ada</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <nav>
        <ul class="pagination justify-content-center">
          <?php
          $qs = [];
          if ($search !== '') $qs['search'] = $search;
          if ($bulan !== '')  $qs['bulan']  = $bulan;
          if ($tahun !== '')  $qs['tahun']  = $tahun;
          $base_qs = http_build_query($qs);
          ?>

          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page-1 ?>&<?= $base_qs ?>">Previous</a>
            </li>
          <?php endif; ?>

          <?php for ($i=1; $i<=$total_pages; $i++): ?>
            <li class="page-item <?= ($i==$page)?'active':'' ?>">
              <a class="page-link" href="?page=<?= $i ?>&<?= $base_qs ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page+1 ?>&<?= $base_qs ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>

    </main>
  </div>
</div>

</body>
</html>
