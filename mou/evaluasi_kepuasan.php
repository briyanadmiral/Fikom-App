<?php
session_start();
if (!isset($_SESSION['mou_admin']) && !isset($_SESSION['mou_user'])) {
    header("Location: ../mou.php");
    exit;
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'dosen' && $role !== 'superadmin') {
    header('Location: ../index.php');
    exit;
}

include 'koneksi.php';

// helper: check if column exists
function columnExists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($res && mysqli_num_rows($res) > 0);
}

$hasDeletedOnMou = columnExists($conn, 'mou', 'deleted_at');
$hasDeletedOnPelaksanaan = columnExists($conn, 'pelaksanaan', 'deleted_at');

// AJAX Detail Request
if (isset($_GET['ajax_detail_id'])) {
    $id_pel = intval($_GET['ajax_detail_id']);
    $q = mysqli_query($conn, "
        SELECT ee.*, ke.ket_evaluasi 
        FROM evaluasi_eksternal ee
        LEFT JOIN keterangan_evaluasi ke ON ee.id_ket_evaluasi = ke.id_ket_evaluasi
        WHERE ee.id_pelaksanaan = $id_pel AND ee.deleted_at IS NULL
        ORDER BY ee.tanggal_evaluasi DESC
    ");
    $data = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $data[] = [
            'pemberi' => htmlspecialchars($row['pemberi_evaluasi']),
            'tanggal' => date('d F Y', strtotime($row['tanggal_evaluasi'])),
            'status' => htmlspecialchars($row['ket_evaluasi'] ?? '-'),
            'evaluasi' => nl2br(htmlspecialchars($row['evaluasi'])),
            'bukti' => htmlspecialchars($row['bukti']),
            'q1' => isset($row['q1']) ? htmlspecialchars($row['q1']) : null,
            'q2' => isset($row['q2']) ? htmlspecialchars($row['q2']) : null,
            'q3' => isset($row['q3']) ? htmlspecialchars($row['q3']) : null,
            'q4' => isset($row['q4']) ? nl2br(htmlspecialchars($row['q4'])) : null
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// --- STATISTIK ANALISA ---
// 1. Jumlah Perusahaan Kerja Sama
$q_perusahaan = "SELECT COUNT(DISTINCT pihak_2) FROM mou";
if ($hasDeletedOnMou) $q_perusahaan .= " WHERE deleted_at IS NULL";
$total_perusahaan = mysqli_fetch_row(mysqli_query($conn, $q_perusahaan))[0] ?? 0;

// 2. Perusahaan Sudah Mengisi Kuesioner (at least one evaluation)
$q_sudah_isi = "
    SELECT COUNT(DISTINCT m.pihak_2) 
    FROM mou m
    JOIN pelaksanaan p ON m.id_mou = p.id_mou
    JOIN evaluasi_eksternal ee ON p.id_pelaksanaan = ee.id_pelaksanaan
    WHERE ee.deleted_at IS NULL
";
if ($hasDeletedOnMou) $q_sudah_isi .= " AND m.deleted_at IS NULL";
if ($hasDeletedOnPelaksanaan) $q_sudah_isi .= " AND p.deleted_at IS NULL";
$total_sudah_isi = mysqli_fetch_row(mysqli_query($conn, $q_sudah_isi))[0] ?? 0;

// 3. Siapa yang belum mengisi kuesioner (list of companies that have activities but no evaluation)
$q_list_belum = "
    SELECT DISTINCT m.pihak_2 
    FROM mou m
    JOIN pelaksanaan p ON m.id_mou = p.id_mou
    LEFT JOIN evaluasi_eksternal ee ON p.id_pelaksanaan = ee.id_pelaksanaan AND ee.deleted_at IS NULL
    WHERE ee.id_eval_eksternal IS NULL
";
if ($hasDeletedOnMou) $q_list_belum .= " AND m.deleted_at IS NULL";
if ($hasDeletedOnPelaksanaan) $q_list_belum .= " AND p.deleted_at IS NULL";
$q_list_belum .= " ORDER BY m.pihak_2 ASC";
$res_list_belum = mysqli_query($conn, $q_list_belum);
$list_belum = [];
while ($row = mysqli_fetch_assoc($res_list_belum)) {
    $list_belum[] = $row['pihak_2'];
}
$total_belum_isi = count($list_belum);

// 4. Kegiatan Sudah Terlaksana (evaluasi status = 1 "sudah selesai terlaksana")
$q_terlaksana = "
    SELECT COUNT(DISTINCT p.id_pelaksanaan) 
    FROM pelaksanaan p
    JOIN evaluasi_eksternal ee ON p.id_pelaksanaan = ee.id_pelaksanaan
    WHERE ee.id_ket_evaluasi = 1 AND ee.deleted_at IS NULL
";
if ($hasDeletedOnPelaksanaan) $q_terlaksana .= " AND p.deleted_at IS NULL";
$total_terlaksana = mysqli_fetch_row(mysqli_query($conn, $q_terlaksana))[0] ?? 0;

// 5. Kegiatan Belum Terlaksana (no evaluation or evaluation is not "sudah selesai terlaksana")
$q_total_kegiatan = "SELECT COUNT(*) FROM pelaksanaan WHERE 1=1";
if ($hasDeletedOnPelaksanaan) $q_total_kegiatan .= " AND deleted_at IS NULL";
$total_kegiatan = mysqli_fetch_row(mysqli_query($conn, $q_total_kegiatan))[0] ?? 0;
$total_belum_terlaksana = max(0, $total_kegiatan - $total_terlaksana);

// --- PAGINATION SETUP ---
$limit_options = [5, 10, 25, 50, 100];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $limit_options) ? intval($_GET['limit']) : 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// --- TABLE DATA ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$query_count = "
    SELECT COUNT(*) 
    FROM pelaksanaan p
    JOIN mou m ON p.id_mou = m.id_mou
    WHERE 1=1
";
if ($hasDeletedOnMou) $query_count .= " AND m.deleted_at IS NULL";
if ($hasDeletedOnPelaksanaan) $query_count .= " AND p.deleted_at IS NULL";

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $query_count .= " AND (m.pihak_2 LIKE '%$s%' OR p.nama_pelaksanaan LIKE '%$s%')";
}

if ($filter_status === 'sudah') {
    $query_count .= " AND EXISTS (SELECT 1 FROM evaluasi_eksternal ee WHERE ee.id_pelaksanaan = p.id_pelaksanaan AND ee.deleted_at IS NULL)";
} elseif ($filter_status === 'belum') {
    $query_count .= " AND NOT EXISTS (SELECT 1 FROM evaluasi_eksternal ee WHERE ee.id_pelaksanaan = p.id_pelaksanaan AND ee.deleted_at IS NULL)";
}

$total_data = mysqli_fetch_row(mysqli_query($conn, $query_count))[0] ?? 0;
$total_pages = ($total_data > 0) ? ceil($total_data / $limit) : 1;
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

$query_table = "
    SELECT p.id_pelaksanaan, p.nama_pelaksanaan, p.tanggal_kegiatan, p.pic_kegiatan, m.pihak_2, m.nama_mou,
           (SELECT COUNT(*) FROM evaluasi_eksternal ee WHERE ee.id_pelaksanaan = p.id_pelaksanaan AND ee.deleted_at IS NULL) AS sudah_mengisi
    FROM pelaksanaan p
    JOIN mou m ON p.id_mou = m.id_mou
    WHERE 1=1
";
if ($hasDeletedOnMou) $query_table .= " AND m.deleted_at IS NULL";
if ($hasDeletedOnPelaksanaan) $query_table .= " AND p.deleted_at IS NULL";

if ($search !== '') {
    $s = mysqli_real_escape_string($conn, $search);
    $query_table .= " AND (m.pihak_2 LIKE '%$s%' OR p.nama_pelaksanaan LIKE '%$s%')";
}

if ($filter_status === 'sudah') {
    $query_table .= " AND EXISTS (SELECT 1 FROM evaluasi_eksternal ee WHERE ee.id_pelaksanaan = p.id_pelaksanaan AND ee.deleted_at IS NULL)";
} elseif ($filter_status === 'belum') {
    $query_table .= " AND NOT EXISTS (SELECT 1 FROM evaluasi_eksternal ee WHERE ee.id_pelaksanaan = p.id_pelaksanaan AND ee.deleted_at IS NULL)";
}

$query_table .= " ORDER BY m.pihak_2 ASC, p.tanggal_kegiatan DESC LIMIT $limit OFFSET $offset";
$result_table = mysqli_query($conn, $query_table);

// Base URL detection for client forms (runs safely on both local and production server environment)
$http_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$dir_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($dir_path === '/') {
    $dir_path = '';
}
$base_url = $http_protocol . "://" . $domain . $dir_path . "/evaluasi_klien.php?id=";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Evaluasi Kepuasan - FIKOM APP</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #8a9ccc;
            --primary-soft: rgba(138, 156, 204, 0.15);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.45);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
            --success: #10b981;
            --danger: #ef4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            background-image:
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.8) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(200, 205, 215, 0.5) 0%, transparent 60%);
            background-attachment: fixed;
            color: var(--text-main);
            line-height: 1.6;
        }

        .glass-panel {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: 16px;
        }

        /* Stats Cards */
        .stat-card {
            padding: 1.5rem;
            border-radius: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.1);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 1.5rem;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid var(--border);
        }

        .stat-info h5 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 2px;
            font-weight: 600;
        }

        .stat-info .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }

        /* Custom color variants for stats */
        .stat-card.blue .stat-icon { color: #3b82f6; }
        .stat-card.green .stat-icon { color: #10b981; }
        .stat-card.red .stat-icon { color: #ef4444; }
        .stat-card.orange .stat-icon { color: #f59e0b; }

        /* Tables & Lists */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: rgba(255, 255, 255, 0.2) !important;
            color: var(--dark) !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px;
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            background: transparent !important;
            color: var(--text-main);
            border-color: var(--border);
        }

        .table tbody tr:hover td {
            background-color: rgba(255, 255, 255, 0.2) !important;
        }

        /* Badges */
        .badge-custom {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-success-custom {
            background-color: rgba(16, 185, 129, 0.15);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-danger-custom {
            background-color: rgba(239, 68, 68, 0.15);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .last-border-none:last-child {
            border-bottom: none !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Inputs & Buttons */
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.5) !important;
            border: 1px solid var(--border) !important;
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.7) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(138, 156, 204, 0.2) !important;
        }

        .btn-custom-action {
            background: rgba(255, 255, 255, 0.5) !important;
            backdrop-filter: blur(5px);
            border: 1px solid var(--border) !important;
            color: var(--dark) !important;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.2s ease;
        }

        .btn-custom-action:hover {
            background: rgba(255, 255, 255, 0.8) !important;
            border-color: var(--primary) !important;
            transform: translateY(-1px);
        }

        .copy-input-group {
            max-width: 280px;
        }

        .btn-copy {
            background: rgba(255, 255, 255, 0.6) !important;
            border: 1px solid var(--border) !important;
            border-left: none !important;
            color: var(--text-muted);
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            transition: all 0.2s;
        }

        .btn-copy:hover {
            background: var(--primary) !important;
            color: white;
        }

        .pagination .page-link {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid var(--border);
            color: var(--dark);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            transition: all 0.2s;
            font-size: 0.9rem;
            padding: 8px 14px;
        }
        .pagination .page-link:hover {
            background: rgba(255, 255, 255, 0.7);
            color: var(--primary);
            border-color: var(--primary);
        }
        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            background: rgba(255, 255, 255, 0.1);
            color: #ccc;
            border-color: var(--border);
            pointer-events: none;
        }

        /* Modal styling */
        .modal-content {
            background: rgba(240, 242, 245, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 16px;
        }
        .modal-header {
            border-bottom: 1px solid var(--border);
        }
        .modal-footer {
            border-top: 1px solid var(--border);
        }
        
        /* Sidebar styling override to match MoU design */
        .mou-sidebar {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(255, 255, 255, 0.7);
        }
        .mou-sidebar .nav-link {
            color: var(--dark);
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.2s;
        }
        .mou-sidebar .nav-link:hover, .mou-sidebar .nav-link.active {
            background: var(--primary-soft);
            color: var(--dark);
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row min-vh-100">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                
                <!-- Title Block -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Evaluasi Kepuasan Mitra & Klien</h1>
                </div>
                <div class="mb-4">
                    <p class="text-muted mb-0">Dashboard pemantauan kuesioner evaluasi kepuasan eksternal dari arsip MOU FIKOM UNIKA.</p>
                </div>

                <!-- Analisa Dashboard (Statistik) -->
                <div class="row g-3 mb-4">
                    <!-- Jumlah Perusahaan -->
                    <div class="col-md-3">
                        <div class="stat-card glass-panel blue">
                            <div class="stat-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-info">
                                <h5>Total Perusahaan Mitra</h5>
                                <div class="stat-number"><?= $total_perusahaan ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sudah Mengisi -->
                    <div class="col-md-3">
                        <div class="stat-card glass-panel green">
                            <div class="stat-icon">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="stat-info">
                                <h5>Sudah Isi Evaluasi</h5>
                                <div class="stat-number"><?= $total_sudah_isi ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted);">perusahaan</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Belum Mengisi -->
                    <div class="col-md-3">
                        <div class="stat-card glass-panel red">
                            <div class="stat-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stat-info">
                                <h5>Belum Isi Evaluasi</h5>
                                <div class="stat-number"><?= $total_belum_isi ?> <span style="font-size: 0.9rem; font-weight: normal; color: var(--text-muted);">perusahaan</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Kegiatan -->
                    <div class="col-md-3">
                        <div class="stat-card glass-panel orange">
                            <div class="stat-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-info">
                                <h5>Kegiatan Terlaksana</h5>
                                <div class="stat-number"><?= $total_terlaksana ?> / <?= $total_kegiatan ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Main Table Card -->
                    <div class="col-lg-9">
                        <div class="card glass-panel border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title h5 mb-0" style="font-weight: 700; color: var(--dark);"><i class="fas fa-list-check me-2"></i>Status Kuesioner Kegiatan</h3>
                                <a href="evaluasi_analisis.php" class="btn btn-custom-action btn-sm"><i class="fas fa-chart-pie me-2 text-primary"></i>Lihat Analisis Kuesioner</a>
                            </div>
                            
                            <!-- Filters -->
                            <form method="GET" class="row g-2 mb-3">
                                <input type="hidden" name="page" value="1">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0" style="border: 1px solid var(--border);"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari Mitra atau Nama Kegiatan..." value="<?= htmlspecialchars($search) ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-select">
                                        <option value="">Semua Status Pengisian</option>
                                        <option value="sudah" <?= $filter_status === 'sudah' ? 'selected' : '' ?>>Sudah Mengisi</option>
                                        <option value="belum" <?= $filter_status === 'belum' ? 'selected' : '' ?>>Belum Mengisi</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="limit" class="form-select" onchange="this.form.submit()">
                                        <?php foreach ($limit_options as $opt): ?>
                                            <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>><?= $opt ?> data/hal</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-custom-action flex-grow-1"><i class="fas fa-filter me-2"></i>Filter</button>
                                    <a href="evaluasi_kepuasan.php" class="btn btn-custom-action"><i class="fas fa-rotate-left"></i></a>
                                </div>
                            </form>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Mitra / Perusahaan</th>
                                            <th>Kegiatan</th>
                                            <th>PIC</th>
                                            <th style="width: 150px;">Status Kuesioner</th>
                                            <th>Link Evaluasi Klien</th>
                                            <th style="width: 120px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = $offset + 1;
                                        while ($row = mysqli_fetch_assoc($result_table)): 
                                            $id_pel = $row['id_pelaksanaan'];
                                            $link_evaluasi = $base_url . $id_pel;
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td>
                                                    <strong style="color: var(--dark);"><?= htmlspecialchars($row['pihak_2']) ?></strong>
                                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($row['nama_mou']) ?></div>
                                                </td>
                                                <td><?= htmlspecialchars($row['nama_pelaksanaan']) ?></td>
                                                <td><span class="text-muted" style="font-size: 0.9rem;"><?= htmlspecialchars($row['pic_kegiatan']) ?></span></td>
                                                <td>
                                                    <?php if ($row['sudah_mengisi'] > 0): ?>
                                                        <span class="badge-custom badge-success-custom">
                                                            <i class="fas fa-check-circle"></i> Sudah Mengisi
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-custom badge-danger-custom">
                                                            <i class="fas fa-times-circle"></i> Belum Mengisi
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="input-group copy-input-group">
                                                        <input type="text" class="form-control form-control-sm text-truncate bg-white" id="link-<?= $id_pel ?>" value="<?= $link_evaluasi ?>" readonly style="font-size: 0.8rem;">
                                                        <button class="btn btn-copy btn-sm" type="button" onclick="copyLink(<?= $id_pel ?>)" title="Salin Link">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($row['sudah_mengisi'] > 0): ?>
                                                        <button class="btn btn-custom-action btn-sm" onclick="showEvaluationDetails(<?= $id_pel ?>, '<?= htmlspecialchars(addslashes($row['pihak_2'])) ?>', '<?= htmlspecialchars(addslashes($row['nama_pelaksanaan'])) ?>')">
                                                            <i class="fas fa-eye me-1"></i> Detail
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.5;">
                                                            <i class="fas fa-eye-slash me-1"></i> Detail
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <?php if (mysqli_num_rows($result_table) === 0): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Tidak ada data evaluasi kegiatan yang ditemukan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav class="mt-3">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php
                                        $qs = [];
                                        if ($search !== '') $qs['search'] = $search;
                                        if ($filter_status !== '') $qs['status'] = $filter_status;
                                        $qs['limit'] = $limit;
                                        $base_qs = http_build_query($qs);
                                        ?>
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= $base_qs ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&laquo;</span>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>&<?= $base_qs ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= $base_qs ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&raquo;</span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sidebar / Who has not filled yet card -->
                    <div class="col-lg-3">
                        <div class="card glass-panel border-0 p-4 h-100">
                            <h3 class="card-title h5 mb-3" style="font-weight: 700; color: var(--dark);"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Belum Mengisi</h3>
                            <p class="text-muted" style="font-size: 0.85rem;">Berikut adalah mitra yang belum mengisi kuesioner evaluasi kepuasan untuk kegiatannya:</p>
                            
                            <div class="list-group list-group-flush border-0 bg-transparent overflow-auto" style="max-height: 400px;">
                                <?php if (count($list_belum) > 0): ?>
                                    <?php foreach ($list_belum as $mitra): ?>
                                        <div class="list-group-item bg-transparent border-0 px-0 py-2 d-flex align-items-center gap-2">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: var(--danger); display: inline-block; flex-shrink:0;"></span>
                                            <span style="font-size: 0.9rem; font-weight: 500; color: var(--dark);"><?= htmlspecialchars($mitra) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-4" style="font-size: 0.85rem;">
                                        <i class="fas fa-check-double text-success d-block fs-3 mb-2"></i>
                                        Semua mitra sudah mengisi kuesioner!
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            </main>
        </div>
    </div>

    <!-- Detail Evaluation Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel" style="font-weight:700; color:var(--dark);"><i class="fas fa-file-lines me-2 text-primary"></i>Riwayat Evaluasi Klien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Mitra / Kegiatan</span>
                        <strong id="modal-subtitle" style="font-size:1.1rem; color:var(--dark);">Nama Perusahaan - Nama Kegiatan</strong>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" style="font-size:0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 150px;">Pengisi / Tanggal</th>
                                    <th style="width: 150px;">Status Implementasi</th>
                                    <th>Evaluasi / Feedback</th>
                                    <th style="width: 110px;">Bukti</th>
                                </tr>
                            </thead>
                            <tbody id="modal-table-body">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom-action" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyLink(id) {
            const copyText = document.getElementById("link-" + id);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            
            const btn = copyText.nextElementSibling;
            const icon = btn.querySelector('i');
            icon.className = 'fas fa-check text-success';
            btn.title = 'Tautan Disalin!';
            
            setTimeout(() => {
                icon.className = 'fas fa-copy';
                btn.title = 'Salin Link';
            }, 2000);
        }

        function showEvaluationDetails(id_pel, mitraName, kegiatanName) {
            document.getElementById('modal-subtitle').innerText = `${mitraName} - ${kegiatanName}`;
            const tableBody = document.getElementById('modal-table-body');
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat...</td></tr>';
            
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();

            fetch(`evaluasi_kepuasan.php?ajax_detail_id=${id_pel}`)
                .then(res => res.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    if (data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Belum ada evaluasi untuk kegiatan ini.</td></tr>';
                        return;
                    }
                    data.forEach((row, idx) => {
                        let buktiLink = '-';
                        if (row.bukti) {
                            buktiLink = `<a href="uploads/${row.bukti}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Unduh</a>`;
                        }
                        
                        let evalContent = '';
                        if (row.q1) {
                            evalContent = `
                                <div class="mb-2 pb-2 border-bottom" style="font-size: 0.85rem; line-height: 1.4;">
                                    <div class="fw-bold text-dark">1. Penilaian Keseluruhan Jalannya Kerja Sama:</div>
                                    <div class="text-muted">${row.q1}</div>
                                </div>
                                <div class="mb-2 pb-2 border-bottom" style="font-size: 0.85rem; line-height: 1.4;">
                                    <div class="fw-bold text-dark">2. Komunikasi & Pelayanan Administrasi:</div>
                                    <div class="text-muted">${row.q2}</div>
                                </div>
                                <div class="mb-2 pb-2 border-bottom" style="font-size: 0.85rem; line-height: 1.4;">
                                    <div class="fw-bold text-dark">3. Dampak Positif bagi Mitra:</div>
                                    <div class="text-muted">${row.q3}</div>
                                </div>
                                <div style="font-size: 0.85rem; line-height: 1.4;">
                                    <div class="fw-bold text-dark">4. Saran / Area Peningkatan:</div>
                                    <div class="text-muted">${row.q4 || '-'}</div>
                                </div>
                            `;
                        } else {
                            evalContent = row.evaluasi;
                        }
                        
                        tableBody.innerHTML += `
                            <tr>
                                <td class="text-center">${idx + 1}</td>
                                <td>
                                    <strong>${row.pemberi}</strong>
                                    <div class="text-muted" style="font-size:0.75rem;">${row.tanggal}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">${row.status}</span></td>
                                <td>${evalContent}</td>
                                <td>${buktiLink}</td>
                            </tr>
                        `;
                    });
                })
                .catch(err => {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Gagal memuat data evaluasi.</td></tr>';
                    console.error(err);
                });
        }
    </script>
</body>
</html>
