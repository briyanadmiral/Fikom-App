<?php
// admin/dashboard.php - Admin Dashboard
require_once '../config/database.php';

startSession();

// Check admin session
if (!checkSessionRole(['admin'])) {
    header("Location: ../login.php");
    exit;
}

$user_info = getUserInfo();

// Get statistics
$database = new Database();
$db = $database->getConnection();

// Count statistics
$stats = [
    'total_ruangan' => 0,
    'pending_requests' => 0,
    'approved_today' => 0,
    'total_users' => 0
];

try {
    // Total ruangan
    $stmt = $db->query("SELECT COUNT(*) as total FROM ruangan WHERE status = 'active'");
    $stats['total_ruangan'] = $stmt->fetch()['total'];
    
    // Pending requests
    $stmt = $db->query("SELECT COUNT(*) as total FROM pengajuan_peminjaman WHERE status = 'pending'");
    $stats['pending_requests'] = $stmt->fetch()['total'];
    
    // Approved today
    $stmt = $db->query("SELECT COUNT(*) as total FROM pengajuan_peminjaman WHERE status = 'approved' AND DATE(approved_at) = CURDATE()");
    $stats['approved_today'] = $stmt->fetch()['total'];
    
    // Total users
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $stats['total_users'] = $stmt->fetch()['total'];
    
} catch(PDOException $e) {
    error_log("Error getting stats: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sentralisasi Ruangan FIKOM</title>
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
                    <?php if ($stats['pending_requests'] > 0): ?>
                        <span class="badge"><?php echo $stats['pending_requests']; ?></span>
                    <?php endif; ?>
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
                <h1>Dashboard Admin</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($user_info['nama'] ?? 'Admin'); ?></span>
                    <div class="user-avatar">👨‍💼</div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">🏛️</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_ruangan']; ?></h3>
                        <p>Total Ruangan</p>
                    </div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending_requests']; ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['approved_today']; ?></h3>
                        <p>Approved Hari Ini</p>
                    </div>
                </div>
                
                <div class="stat-card purple">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>🚀 Aksi Cepat</h2>
                <div class="action-cards">
                    <a href="kelola-ruangan.php?tab=rooms" class="action-card">
                        <div class="action-icon">➕</div>
                        <h3>Tambah Ruangan</h3>
                        <p>Tambahkan ruangan baru ke sistem</p>
                    </a>
                    
                    <a href="kelola-ruangan.php?tab=approvals" class="action-card">
                        <div class="action-icon">📝</div>
                        <h3>Review Pengajuan</h3>
                        <p>Tinjau pengajuan peminjaman ruangan</p>
                    </a>
                    
                    <a href="lihat_jadwal.php" class="action-card">
                        <div class="action-icon">📅</div>
                        <h3>Lihat Jadwal</h3>
                        <p>Cek jadwal penggunaan ruangan</p>
                    </a>
                    
                    <a href="kelola-ruangan.php" class="action-card">
                        <div class="action-icon">📈</div>
                        <h3>Kelola Sistem</h3>
                        <p>Manajemen ruangan dan pengajuan</p>
                    </a>
                </div>
            </div>

            <div class="recent-activities">
                <h2>📋 Aktivitas Terbaru</h2>
                <div class="activity-list" id="activityList">
                    <div class="loading">🔄 Memuat aktivitas terbaru...</div>
                </div>
            </div>

            <div class="pending-preview">
                <h2>⏳ Pengajuan Menunggu Persetujuan</h2>
                <div class="pending-list" id="pendingList">
                    <div class="loading">🔄 Memuat pengajuan pending...</div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>