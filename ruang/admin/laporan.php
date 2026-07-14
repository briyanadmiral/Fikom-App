<?php
// admin/laporan.php - Laporan Aktivitas Peminjaman Ruangan
require_once '../config/database.php';

startSession();

// Check admin session
if (!checkSessionRole(['admin'])) {
    header("Location: ../login.php");
    exit;
}

$user_info = getUserInfo();
$database = new Database();
$db = $database->getConnection();

// Get active rooms for dropdown filter
$rooms = [];
try {
    if ($db) {
        $stmt_rooms = $db->query("SELECT id, nama_ruangan, kode_ruangan FROM ruangan WHERE status = 'active' ORDER BY nama_ruangan");
        $rooms = $stmt_rooms->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $error = 'Error loading rooms: ' . $e->getMessage();
}

// Get filter parameters
$ruangan_id = $_GET['ruangan_id'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? '';

$report_bookings = [];
$error = '';

try {
    if ($db) {
        $query = "SELECT 
                    pp.*, 
                    u.nama as nama_peminjam, 
                    u.email as email_peminjam, 
                    r.nama_ruangan,
                    r.kode_ruangan,
                    admin.nama as nama_admin
                  FROM 
                    pengajuan_peminjaman pp 
                  JOIN 
                    users u ON pp.user_id = u.id 
                  JOIN 
                    ruangan r ON pp.ruangan_id = r.id
                  LEFT JOIN 
                    users admin ON pp.approved_by = admin.id
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($ruangan_id)) {
            $query .= " AND pp.ruangan_id = ?";
            $params[] = $ruangan_id;
        }
        if (!empty($start_date)) {
            $query .= " AND pp.tanggal_pinjam >= ?";
            $params[] = $start_date;
        }
        if (!empty($end_date)) {
            $query .= " AND pp.tanggal_pinjam <= ?";
            $params[] = $end_date;
        }
        if (!empty($status)) {
            $query .= " AND pp.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY pp.tanggal_pinjam DESC, pp.jam_mulai DESC";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $report_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = 'Koneksi database tidak tersedia.';
    }
} catch (Exception $e) {
    $error = 'Error loading report: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman - Admin FIKOM</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        .report-filter-card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 1px solid rgba(255,255,255,0.7);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
        }
        .report-table-wrapper {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.7);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
            overflow-x: auto;
            margin-top: 1rem;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.95rem;
        }
        .report-table th {
            background: rgba(255, 255, 255, 0.5);
            color: #333;
            font-weight: 600;
            padding: 15px;
            border-bottom: 2px solid rgba(255,255,255,0.7);
        }
        .report-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            color: #333;
            vertical-align: top;
        }
        .report-table tr:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .print-header-doc {
            display: none;
        }
        
        /* Specific Print Adjustments inside style tag (fallback) */
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            .sidebar, .top-bar, .report-filter-card, .btn, hr {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .print-header-doc {
                display: block !important;
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px double #333;
                padding-bottom: 15px;
            }
            .print-header-doc h1 {
                font-size: 22px;
                margin: 0 0 5px 0;
                color: #000;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .print-header-doc p {
                margin: 0;
                font-size: 14px;
                color: #555;
            }
            .report-table-wrapper {
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
            .report-table {
                font-size: 11px !important;
                width: 100% !important;
                border: 1px solid #333 !important;
            }
            .report-table th, .report-table td {
                border: 1px solid #333 !important;
                padding: 8px !important;
                color: black !important;
                background: transparent !important;
            }
            .report-table th {
                font-weight: bold !important;
                background-color: #f2f2f2 !important;
            }
            .status-badge {
                border: none !important;
                background: transparent !important;
                padding: 0 !important;
                color: black !important;
                font-weight: bold !important;
            }
            @page {
                size: landscape;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon"><i class="bi bi-building-gear"></i></div>
                    <span>Ruang FIKOM</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
                
                <a href="kelola-ruangan.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'kelola-ruangan.php') ? 'active' : '' ?>">
                    <i class="bi bi-building me-2"></i> Kelola Ruangan
                </a>
                
                <a href="lihat_jadwal.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'lihat_jadwal.php') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3 me-2"></i> Lihat Jadwal
                </a>
                
                <a href="riwayat.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'riwayat.php') ? 'active' : '' ?>">
                    <i class="bi bi-clock-history me-2"></i> Riwayat Pengajuan
                </a>
                
                <a href="laporan.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Laporan Peminjaman
                </a>

                <hr class="mx-3 opacity-25">

                <a href="../logout.php" class="nav-item logout">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <!-- Kop Cetak Laporan (Hanya tampil saat dicetak ke PDF) -->
            <div class="print-header-doc">
                <h1>Laporan Aktivitas Peminjaman Ruangan</h1>
                <p>Fakultas Ilmu Komputer (FIKOM) - Universitas Katolik Soegijapranata</p>
                <p style="font-size: 12px; margin-top: 5px;">
                    Filter: 
                    Ruangan: <?= !empty($ruangan_id) ? htmlspecialchars(array_values(array_filter($rooms, fn($r) => $r['id'] == $ruangan_id))[0]['nama_ruangan'] ?? 'Semua') : 'Semua Ruangan' ?> | 
                    Periode: <?= !empty($start_date) ? date('d/m/Y', strtotime($start_date)) : 'Awal' ?> s/d <?= !empty($end_date) ? date('d/m/Y', strtotime($end_date)) : 'Sekarang' ?> | 
                    Status: <?= !empty($status) ? ucfirst(htmlspecialchars($status)) : 'Semua' ?>
                </p>
            </div>

            <header class="top-bar">
                <h1>Laporan Peminjaman</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user_info['nama'] ?? 'Admin'); ?></span>
                    <div class="user-avatar">👨‍💼</div>
                </div>
            </header>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="report-filter-card">
                <form method="GET" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="ruangan_id">Ruangan</label>
                            <select name="ruangan_id" id="ruangan_id">
                                <option value="">-- Semua Ruangan --</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= $room['id'] ?>" <?= $ruangan_id == $room['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($room['nama_ruangan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $status == 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= $status == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter Data</button>
                        <button type="button" class="btn btn-success" onclick="window.print()"><i class="bi bi-file-earmark-pdf me-1"></i> Cetak Laporan (PDF)</button>
                    </div>
                </form>
            </div>

            <div class="section-card" style="padding-top: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 style="font-size: 1.25rem;">📊 Data Aktivitas Peminjaman (<?= count($report_bookings) ?> record)</h2>
                </div>
                
                <?php if (empty($report_bookings)): ?>
                    <div class="empty-state">
                        <p>📭 Tidak ada data peminjaman yang cocok dengan filter pencarian.</p>
                    </div>
                <?php else: ?>
                    <div class="report-table-wrapper">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Ruangan</th>
                                    <th>Peminjam</th>
                                    <th>Keperluan</th>
                                    <th>Waktu Peminjaman</th>
                                    <th>Status</th>
                                    <th>Reviewer & Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($report_bookings as $booking): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['nama_ruangan']) ?></strong><br>
                                            <span style="font-size: 0.8rem; color: #666;"><?= htmlspecialchars($booking['kode_ruangan']) ?></span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['nama_peminjam']) ?></strong><br>
                                            <span style="font-size: 0.8rem; color: #666;"><?= htmlspecialchars($booking['email_peminjam']) ?></span><br>
                                            <span style="font-size: 0.8rem; color: #666;">WA: <?= !empty($booking['no_wa']) ? htmlspecialchars($booking['no_wa']) : '-' ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($booking['keperluan']) ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($booking['tanggal_pinjam'])) ?><br>
                                            <span style="font-size: 0.8rem; color: #666;">
                                                <?= substr($booking['jam_mulai'], 0, 5) ?> - <?= substr($booking['jam_selesai'], 0, 5) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= htmlspecialchars($booking['status']) ?>">
                                                <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($booking['status'] !== 'pending'): ?>
                                                <span style="font-size: 0.85rem;">
                                                    <strong>Reviewer:</strong> <?= htmlspecialchars($booking['nama_admin'] ?? 'System') ?><br>
                                                    <strong>Keterangan:</strong> <?= !empty($booking['keterangan_admin']) ? htmlspecialchars($booking['keterangan_admin']) : '-' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted" style="font-size: 0.85rem;">Menunggu keputusan</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
