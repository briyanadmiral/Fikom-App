<?php
// admin/riwayat.php - Riwayat Semua Pengajuan (Approved & Rejected)
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

$history_bookings = [];
try {
    // Query untuk mengambil SEMUA pengajuan yang sudah 'approved' atau 'rejected'
    $stmt = $db->query("SELECT 
                            pp.*, 
                            u.nama as nama_peminjam, 
                            r.nama_ruangan,
                            admin.nama as nama_admin
                        FROM 
                            pengajuan_peminjaman pp 
                        JOIN 
                            users u ON pp.user_id = u.id 
                        JOIN 
                            ruangan r ON pp.ruangan_id = r.id
                        LEFT JOIN 
                            users admin ON pp.approved_by = admin.id
                        WHERE 
                            pp.status IN ('approved', 'rejected') 
                        ORDER BY 
                            pp.created_at DESC");
    $history_bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading booking history: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengajuan - Admin FIKOM</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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

                <hr class="mx-3 opacity-25">

                <a href="../logout.php" class="nav-item logout">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Riwayat Pengajuan</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user_info['nama'] ?? 'Admin'); ?></span>
                    <div class="user-avatar">👨‍💼</div>
                </div>
            </header>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="section-card">
                <h2>Riwayat Pengajuan Selesai</h2>
                
                <?php if (empty($history_bookings)): ?>
                    <div class="empty-state">
                        <p>Belum ada riwayat pengajuan yang disetujui atau ditolak.</p>
                    </div>
                <?php else: ?>
                    <div class="bookings-list">
                        <?php foreach ($history_bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <h3><?php echo htmlspecialchars($booking['keperluan']); ?></h3>
                                    <span class="status-badge <?php echo htmlspecialchars($booking['status']); ?>">
                                        <?php if ($booking['status'] == 'approved'): ?>
                                            ✅ Disetujui
                                        <?php else: ?>
                                            ❌ Ditolak
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="booking-details">
                                    <div class="detail-grid">
                                        <div><strong>Pemohon:</strong> <?php echo htmlspecialchars($booking['nama_peminjam']); ?></div>
                                        <div><strong>Ruangan:</strong> <?php echo htmlspecialchars($booking['nama_ruangan']); ?></div>
                                        <div><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($booking['tanggal_pinjam'])); ?></div>
                                        <div><strong>Waktu:</strong> <?php echo substr($booking['jam_mulai'], 0, 5) . ' - ' . substr($booking['jam_selesai'], 0, 5); ?></div>
                                    </div>
                                    
                                    <?php if (!empty($booking['surat_peminjaman'])): ?>
                                        <p><strong>Surat:</strong> <a href="../assets/uploads/<?php echo htmlspecialchars($booking['surat_peminjaman']); ?>" target="_blank" class="file-link">📄 Lihat Surat</a></p>
                                    <?php else: ?>
                                        <p><strong>Surat:</strong> <span class="text-muted">Tidak ada file</span></p>
                                    <?php endif; ?>

                                    <?php if (!empty($booking['keterangan_admin'])): ?>
                                        <p><strong>Keterangan Admin:</strong> <?php echo htmlspecialchars($booking['keterangan_admin']); ?></p>
                                    <?php endif; ?>
                                    
                                    <small class="booking-date">
                                        Diajukan: <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?>
                                        <?php if (!empty($booking['nama_admin'])): ?>
                                            | Di-review oleh: <?php echo htmlspecialchars($booking['nama_admin']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>