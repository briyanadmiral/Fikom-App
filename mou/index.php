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
$limit_options = [5, 10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $limit_options) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// ambil filter dari querystring, sanitasi dasar
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$bulan = isset($_GET['bulan']) && $_GET['bulan'] !== '' ? intval($_GET['bulan']) : '';
$tahun = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? intval($_GET['tahun']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

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
    $where_clauses[] = "(no_mou LIKE '%$s%' OR nama_mou LIKE '%$s%' OR no_mou_eks LIKE '%$s%' OR pihak_2 LIKE '%$s%')";
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

// Filter berdasarkan status dari dashboard card
if ($status !== '' && $status !== 'total') {
    if ($status === 'mou_selesai') {
        $where_clauses[] = "id_mou IN (
            SELECT DISTINCT m.id_mou
            FROM mou m
            JOIN pelaksanaan p ON m.id_mou = p.id_mou
            JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
            JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
            WHERE ei.id_ket_evaluasi = 1
              AND ee.id_ket_evaluasi = 1
              " . ($hasDeletedOnPelaksanaan ? "AND p.deleted_at IS NULL" : "") . "
        )";
    } elseif ($status === 'mou_belum_selesai') {
        $where_clauses[] = "id_mou NOT IN (
            SELECT DISTINCT m.id_mou
            FROM mou m
            JOIN pelaksanaan p ON m.id_mou = p.id_mou
            JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
            JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
            WHERE ei.id_ket_evaluasi = 1
              AND ee.id_ket_evaluasi = 1
              " . ($hasDeletedOnPelaksanaan ? "AND p.deleted_at IS NULL" : "") . "
        )";
    } elseif ($status === 'kegiatan_proses') {
        $where_clauses[] = "id_mou IN (
            SELECT DISTINCT p.id_mou
            FROM pelaksanaan p
            LEFT JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
            LEFT JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
            WHERE 1=1
              " . ($hasDeletedOnPelaksanaan ? "AND p.deleted_at IS NULL" : "") . "
              AND (
                  (ei.id_ket_evaluasi = 1 AND (ee.id_ket_evaluasi IS NULL OR ee.id_ket_evaluasi != 1))
                  OR
                  (ee.id_ket_evaluasi = 1 AND (ei.id_ket_evaluasi IS NULL OR ei.id_ket_evaluasi != 1))
              )
        )";
    } elseif ($status === 'kegiatan_selesai') {
        $where_clauses[] = "id_mou IN (
            SELECT DISTINCT p.id_mou
            FROM pelaksanaan p
            JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
            JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
            WHERE ei.id_ket_evaluasi = 1
              AND ee.id_ket_evaluasi = 1
              " . ($hasDeletedOnPelaksanaan ? "AND p.deleted_at IS NULL" : "") . "
        )";
    } elseif ($status === 'belum_dikerjakan') {
        $where_clauses[] = "id_mou IN (
            SELECT DISTINCT p.id_mou
            FROM pelaksanaan p
            LEFT JOIN evaluasi_internal ei ON ei.id_pelaksanaan = p.id_pelaksanaan
            LEFT JOIN evaluasi_eksternal ee ON ee.id_pelaksanaan = p.id_pelaksanaan
            WHERE ei.id_pelaksanaan IS NULL
              AND ee.id_pelaksanaan IS NULL
              " . ($hasDeletedOnPelaksanaan ? "AND p.deleted_at IS NULL" : "") . "
        )";
    }
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

$query = "SELECT * FROM mou $where ORDER BY tgl_mou DESC, id_mou DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

// Query string tanpa status untuk tautan kartu
$card_qs_arr = [];
if ($search !== '') $card_qs_arr['search'] = $search;
if ($bulan !== '') $card_qs_arr['bulan'] = $bulan;
if ($tahun !== '') $card_qs_arr['tahun'] = $tahun;
$card_qs_arr['limit'] = $limit;
$card_qs = http_build_query($card_qs_arr);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard MOU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/glass.css?v=<?= time() ?>">
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

                <!-- Alerts -->
                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Sukses!</strong> Data MOU berhasil dihapus.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Gagal!</strong> <?= htmlspecialchars($_GET['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistik cards -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <a href="?status=total&<?= $card_qs ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 <?= ($status === '' || $status === 'total') ? 'border-primary border-2 shadow-sm' : 'border-0 shadow-sm' ?>" style="transition: all 0.2s ease; cursor: pointer;" onmouseover="this.classList.add('shadow'); this.style.transform='translateY(-2px)';" onmouseout="this.classList.remove('shadow'); this.style.transform='translateY(0)';">
                                <div class="card-body">
                                    <h5 class="card-title text-primary"><i class="bi bi-file-earmark-text me-2"></i>Total MOU</h5>
                                    <p class="card-text display-6"><?= $total_mou ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="?status=mou_selesai&<?= $card_qs ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 <?= $status === 'mou_selesai' ? 'border-success border-2 shadow-sm' : 'border-0 shadow-sm' ?>" style="transition: all 0.2s ease; cursor: pointer;" onmouseover="this.classList.add('shadow'); this.style.transform='translateY(-2px)';" onmouseout="this.classList.remove('shadow'); this.style.transform='translateY(0)';">
                                <div class="card-body">
                                    <h5 class="card-title text-success"><i class="bi bi-check-circle me-2"></i>MOU Selesai</h5>
                                    <p class="card-text display-6"><?= $total_finish ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="?status=mou_belum_selesai&<?= $card_qs ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 <?= $status === 'mou_belum_selesai' ? 'border-danger border-2 shadow-sm' : 'border-0 shadow-sm' ?>" style="transition: all 0.2s ease; cursor: pointer;" onmouseover="this.classList.add('shadow'); this.style.transform='translateY(-2px)';" onmouseout="this.classList.remove('shadow'); this.style.transform='translateY(0)';">
                                <div class="card-body">
                                    <h5 class="card-title text-danger"><i class="bi bi-x-circle me-2"></i>MOU Belum Selesai</h5>
                                    <p class="card-text display-6"><?= $total_unfinish ?></p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4 mb-3">
                        <a href="?status=kegiatan_proses&<?= $card_qs ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 <?= $status === 'kegiatan_proses' ? 'border-warning border-2 shadow-sm' : 'border-0 shadow-sm' ?>" style="transition: all 0.2s ease; cursor: pointer;" onmouseover="this.classList.add('shadow'); this.style.transform='translateY(-2px)';" onmouseout="this.classList.remove('shadow'); this.style.transform='translateY(0)';">
                                <div class="card-body">
                                    <h5 class="card-title text-warning"><i class="bi bi-clock-history me-2"></i>Kegiatan Dalam Proses</h5>
                                    <p class="card-text display-6"><?= $in_progress_kegiatan ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="?status=kegiatan_selesai&<?= $card_qs ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 <?= $status === 'kegiatan_selesai' ? 'border-success border-2 shadow-sm' : 'border-0 shadow-sm' ?>" style="transition: all 0.2s ease; cursor: pointer;" onmouseover="this.classList.add('shadow'); this.style.transform='translateY(-2px)';" onmouseout="this.classList.remove('shadow'); this.style.transform='translateY(0)';">
                                <div class="card-body">
                                    <h5 class="card-title text-success"><i class="bi bi-calendar-check me-2"></i>Kegiatan Selesai</h5>
                                    <p class="card-text display-6"><?= $done_kegiatan ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="?status=belum_dikerjakan&<?= $card_qs ?>" class="text-decoration-none text-dark">
                            <div class="card h-100 <?= $status === 'belum_dikerjakan' ? 'border-dark border-2 shadow-sm' : 'border-0 shadow-sm' ?>" style="transition: all 0.2s ease; cursor: pointer;" onmouseover="this.classList.add('shadow'); this.style.transform='translateY(-2px)';" onmouseout="this.classList.remove('shadow'); this.style.transform='translateY(0)';">
                                <div class="card-body">
                                    <h5 class="card-title text-dark"><i class="bi bi-calendar-x me-2"></i>Belum Dikerjakan</h5>
                                    <p class="card-text display-6"><?= $not_started_kegiatan ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Filter -->
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <form method="GET" class="row g-2 mb-4">
                        <?php if ($status !== ''): ?>
                            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
                        <?php endif; ?>
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
                            <select name="limit" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($limit_options as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($limit == $opt) ? 'selected' : '' ?>><?= $opt ?> data/hal</option>
                                <?php endforeach; ?>
                            </select>
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

                <!-- Active Filter Indicator -->
                <?php if ($status !== '' && $status !== 'total'): ?>
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3 py-2 px-3">
                        <span>
                            <i class="bi bi-funnel-fill me-2"></i>Status Filter: 
                            <strong>
                                <?php
                                if ($status === 'mou_selesai') echo 'MOU Selesai';
                                elseif ($status === 'mou_belum_selesai') echo 'MOU Belum Selesai';
                                elseif ($status === 'kegiatan_proses') echo 'Kegiatan Dalam Proses';
                                elseif ($status === 'kegiatan_selesai') echo 'Kegiatan Selesai';
                                elseif ($status === 'belum_dikerjakan') echo 'Belum Dikerjakan';
                                ?>
                            </strong>
                        </span>
                        <a href="?<?= $card_qs ?>" class="btn btn-sm btn-outline-info text-dark border-0"><i class="bi bi-x-circle me-1"></i>Hapus Filter</a>
                    </div>
                <?php endif; ?>

                <!-- Tabel MOU -->
                <div class="card border-0 shadow-none">
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover text-center mb-0">
                            <thead>
                        <tr>
                            <th>No</th>
                            <th>No MOU (Eksternal)</th>
                            <th>No MOU (Internal)</th>
                            <th>Nama MOU</th>
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
            $qs['limit'] = $limit;
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
                </div></div>
            </main>
        </div>
    </div>
</body>

</html>