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

// helper: cek apakah kolom ada di tabel
function columnExists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($res && mysqli_num_rows($res) > 0);
}

// cek kolom deleted_at di tabel-tabel yang relevan
$hasDeletedOnMou = columnExists($conn, 'mou', 'deleted_at');
$hasDeletedOnPelaksanaan = columnExists($conn, 'pelaksanaan', 'deleted_at');
$hasDeletedOnPerencanaan = columnExists($conn, 'perencanaan', 'deleted_at');

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// ambil filter dari querystring, sanitasi dasar
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$bulan = isset($_GET['bulan']) && $_GET['bulan'] !== '' ? intval($_GET['bulan']) : '';
$tahun = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? intval($_GET['tahun']) : date('Y');

// ========================
// Statistik Umum
// ========================
// Total MOU (hanya hitung yang tidak dihapus jika kolom deleted_at tersedia)
$total_mou_q = "SELECT COUNT(*) FROM mou";
if ($hasDeletedOnMou) $total_mou_q .= " WHERE deleted_at IS NULL";
$total_mou = mysqli_fetch_row(mysqli_query($conn, $total_mou_q))[0] ?? 0;

/* MOU Selesai: 1 MOU selesai jika ada pelaksanaan yang punya evaluasi internal & eksternal
yang keduanya = 'sudah terlaksana' (id_ket_evaluasi = 1).
Menghitung DISTINCT m.id_mou agar tidak terduplikasi. */
$total_finish_q = "
    SELECT COUNT(DISTINCT m.id_mou) AS cnt
    FROM mou m
    JOIN pelaksanaan p ON m.id_mou = p.id_mou
    JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
    JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
    WHERE ei.id_ket_evaluasi = 1
      AND ee.id_ket_evaluasi = 1
";
if ($hasDeletedOnPelaksanaan) $total_finish_q .= " AND p.deleted_at IS NULL";
$total_finish = mysqli_fetch_row(mysqli_query($conn, $total_finish_q))[0] ?? 0;

$total_unfinish = $total_mou - $total_finish;
if ($total_unfinish < 0) $total_unfinish = 0;

/* Kegiatan Selesai: Hitung pelaksanaan yang punya kedua evaluasi = 1 */
$done_kegiatan_q = "
    SELECT COUNT(DISTINCT p.id_pelaksanaan) AS cnt
    FROM pelaksanaan p
    JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
    JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
    WHERE ei.id_ket_evaluasi = 1
      AND ee.id_ket_evaluasi = 1
";
if ($hasDeletedOnPelaksanaan) $done_kegiatan_q .= " AND p.deleted_at IS NULL";
$done_kegiatan = mysqli_fetch_row(mysqli_query($conn, $done_kegiatan_q))[0] ?? 0;

/* Kegiatan Dalam Proses: Salah satu evaluasi sudah = 1, sedangkan sisi lain belum = 1 (NULL atau !=1) */
$in_progress_kegiatan_q = "
    SELECT COUNT(DISTINCT p.id_pelaksanaan) AS cnt
    FROM pelaksanaan p
    LEFT JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
    LEFT JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
    WHERE 1=1
";
if ($hasDeletedOnPelaksanaan) $in_progress_kegiatan_q .= " AND p.deleted_at IS NULL";
$in_progress_kegiatan_q .= "
    AND (
        (ei.id_ket_evaluasi = 1 AND (ee.id_ket_evaluasi IS NULL OR ee.id_ket_evaluasi != 1))
        OR
        (ee.id_ket_evaluasi = 1 AND (ei.id_ket_evaluasi IS NULL OR ei.id_ket_evaluasi != 1))
    )
";
$in_progress_kegiatan = mysqli_fetch_row(mysqli_query($conn, $in_progress_kegiatan_q))[0] ?? 0;

/* Kegiatan Belum Dikerjakan: Belum ada evaluasi internal maupun eksternal untuk pelaksanaan tersebut */
$not_started_kegiatan_q = "
    SELECT COUNT(*) AS cnt
    FROM pelaksanaan p
    LEFT JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
    LEFT JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
    WHERE ei.id_pelaksanaan IS NULL
      AND ee.id_pelaksanaan IS NULL
";
if ($hasDeletedOnPelaksanaan) $not_started_kegiatan_q .= " AND p.deleted_at IS NULL";
$not_started_kegiatan = mysqli_fetch_row(mysqli_query($conn, $not_started_kegiatan_q))[0] ?? 0;

// ========================
// Filter & Pagination for listing MOU
// ========================
$where_clauses = [];
if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(no_mou LIKE '%$s%' OR nama_mou LIKE '%$s%')";
}
if ($bulan !== '') {
    $where_clauses[] = "MONTH(tgl_mou) = " . intval($bulan);
}
if ($tahun !== '') {
    $where_clauses[] = "YEAR(tgl_mou) = " . intval($tahun);
}
if ($hasDeletedOnMou) {
    $where_clauses[] = "deleted_at IS NULL";
}

$where = "";
if (count($where_clauses) > 0) {
    $where = "WHERE " . implode(" AND ", $where_clauses);
}

$total_data_q = "SELECT COUNT(*) FROM mou $where";
$total_data = mysqli_fetch_row(mysqli_query($conn, $total_data_q))[0] ?? 0;
$total_pages = ($total_data > 0) ? ceil($total_data / $limit) : 1;
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM mou $where ORDER BY tgl_mou ASC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard MOU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Selamat Datang Admin</h1>
                </div>

                <!-- Statistik cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-primary h-100">
                            <div class="card-body">
                                <h5 class="card-title">Total MOU</h5>
                                <p class="card-text display-6"><?= $total_mou ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-success h-100">
                            <div class="card-body">
                                <h5 class="card-title">MOU Selesai</h5>
                                <p class="card-text display-6"><?= $total_finish ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-danger h-100">
                            <div class="card-body">
                                <h5 class="card-title">MOU Belum Selesai</h5>
                                <p class="card-text display-6"><?= $total_unfinish ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-warning h-100">
                            <div class="card-body">
                                <h5 class="card-title">Kegiatan Dalam Proses</h5>
                                <p class="card-text display-6"><?= $in_progress_kegiatan ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-success h-100">
                            <div class="card-body">
                                <h5 class="card-title">Kegiatan Selesai</h5>
                                <p class="card-text display-6"><?= $done_kegiatan ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card text-white bg-secondary h-100">
                            <div class="card-body">
                                <h5 class="card-title">Belum Dikerjakan</h5>
                                <p class="card-text display-6"><?= $not_started_kegiatan ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-auto">
                            <input type="text" name="search" class="form-control" placeholder="Search..."
                                value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-auto">
                            <select name="bulan" class="form-select">
                                <option value="">Semua Bulan</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= ($bulan == $i) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0,0,0,$i,10)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="number" name="tahun" class="form-control" placeholder="Tahun"
                                value="<?= htmlspecialchars($tahun) ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="index.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                    <div class="ms-auto">
                        <a href="tambah_mou.php" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> ADD MOU
                        </a>
                    </div>
                </div>

                <!-- Tabel MOU -->
                <table class="table table-striped text-center">
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
                            <th>Pelaksanaan</th>
                            <th>Perencanaan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
            // Inisialisasi nomor urut berdasarkan halaman saat ini
            $nomor = $offset + 1; 
            while ($data = mysqli_fetch_assoc($result)): 
              $id_mou = intval($data['id_mou']);

              // hitung pelaksanaan/perencanaan dengan aman (cek deleted_at)
              $count_pelaksanaan_q = "SELECT COUNT(*) as total FROM pelaksanaan WHERE id_mou = $id_mou";
              if ($hasDeletedOnPelaksanaan) $count_pelaksanaan_q .= " AND deleted_at IS NULL";
              $count_pelaksanaan = mysqli_fetch_assoc(mysqli_query($conn, $count_pelaksanaan_q))['total'] ?? 0;

              $count_perencanaan_q = "SELECT COUNT(*) as total FROM perencanaan WHERE id_mou = $id_mou";
              if ($hasDeletedOnPerencanaan) $count_perencanaan_q .= " AND deleted_at IS NULL";
              $count_perencanaan = mysqli_fetch_assoc(mysqli_query($conn, $count_perencanaan_q))['total'] ?? 0;
            ?>
                        <tr>
                            <td><?= $nomor++ ?></td>
                            <td><?= htmlspecialchars($data['no_mou_eks']) ?></td>
                            <td><?= htmlspecialchars($data['no_mou']) ?></td>
                            <td><?= htmlspecialchars($data['nama_mou']) ?></td>
                            <td><?= htmlspecialchars($data['pihak_1']) ?></td>
                            <td><?= htmlspecialchars($data['pihak_2']) ?></td>
                            <td><?= htmlspecialchars($data['tingkat']) ?></td>
                            <td><?= date('d F Y', strtotime($data['tgl_mou'])) ?></td>
                            <td>
                                <?php if (!empty($data['file'])): ?>
                                <a href="file_mou/<?= htmlspecialchars($data['file']) ?>" target="_blank">Lihat</a>
                                <?php else: ?>
                                Tidak Ada
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="pelaksanaan.php?id=<?= $id_mou ?>"
                                    class="btn btn-outline-primary btn-sm"><?= $count_pelaksanaan ?></a>
                            </td>
                            <td>
                                <a href="perencanaan.php?id=<?= $id_mou ?>"
                                    class="btn btn-outline-primary btn-sm"><?= $count_perencanaan ?></a>
                            </td>
                            <td>
                                <a href="edit_mou.php?id=<?= $id_mou ?>" class="btn btn-warning btn-sm">Edit</a>

                                <?php if ($count_pelaksanaan == 0 && $count_perencanaan == 0): ?>
                                <!-- Jika dua-duanya 0, tombol hapus aktif -->
                                <a href="hapus_mou.php?id=<?= $id_mou ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menghapus data MOU ini?')">Hapus</a>
                                <?php else: ?>
                                <!-- Jika salah satu ada datanya, tombol hapus dinonaktifkan -->
                                <button class="btn btn-secondary btn-sm" disabled
                                    title="Tidak bisa dihapus karena masih ada data pelaksanaan atau perencanaan">
                                    Hapus
                                </button>
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
            // build query string tambahan agar pagination mempertahankan filter
            $qs = [];
            if ($search !== '') $qs['search'] = $search;
            if ($bulan !== '') $qs['bulan'] = $bulan;
            if ($tahun !== '') $qs['tahun'] = $tahun;
            $base_qs = http_build_query($qs);
            ?>
                        <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link"
                                href="?page=<?= $page - 1 ?>&<?= $base_qs ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= $base_qs ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link"
                                href="?page=<?= $page + 1 ?>&<?= $base_qs ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </main>
        </div>
    </div>
</body>

</html>