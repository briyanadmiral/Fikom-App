<?php
// mahasiswa/dashboard.php - Dashboard untuk Mahasiswa
require_once '../config/database.php';

startSession();

// Check users session
if (!checkSessionRole(['users'])) {
    header("Location: ../login.php");
    exit;
}

$user_info = getUserInfo();

// Get user's booking statistics
$database = new Database();
$db = $database->getConnection();

$stats = [
    'total_pengajuan' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

try {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Total pengajuan
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM pengajuan_peminjaman WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_pengajuan'] = $stmt->fetch()['total'];
        
        // Pending
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM pengajuan_peminjaman WHERE user_id = ? AND status = 'pending'");
        $stmt->execute([$user_id]);
        $stats['pending'] = $stmt->fetch()['total'];
        
        // Approved
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM pengajuan_peminjaman WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$user_id]);
        $stats['approved'] = $stmt->fetch()['total'];
        
        // Rejected
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM pengajuan_peminjaman WHERE user_id = ? AND status = 'rejected'");
        $stmt->execute([$user_id]);
        $stats['rejected'] = $stmt->fetch()['total'];
    }
} catch(PDOException $e) {
    error_log("Error getting stats: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Sentralisasi Ruangan FIKOM</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="student-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon"><i class="bi bi-person-circle"></i></div>
                    <span>User Panel</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
                
                <a href="lihat_jadwal.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'lihat_jadwal.php') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3 me-2"></i> Lihat Jadwal
                </a>

                <a href="pengajuan.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'pengajuan.php') ? 'active' : '' ?>">
                    <i class="bi bi-pencil-square me-2"></i> Pengajuan & Riwayat
                    <?php if (isset($stats['pending']) && $stats['pending'] > 0): ?>
                        <span class="badge"><?php echo $stats['pending']; ?></span>
                    <?php endif; ?>
                </a>

                <hr class="mx-3 opacity-25">

                <a href="../logout.php" class="nav-item logout">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Dashboard Pengguna</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($user_info['nama'] ?? 'Pengguna'); ?></span>
                    <div class="user-avatar">👤</div>
                </div>
            </header>

            <div class="welcome-section">
                <div class="welcome-card">
                    <h2>🏫 Selamat Datang di Sistem Peminjaman Ruangan FIKOM</h2>
                    <p>Ajukan peminjaman ruangan dengan mudah dan pantau status pengajuan Anda secara real-time</p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_pengajuan']; ?></h3>
                        <p>Total Pengajuan</p>
                    </div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Menunggu Persetujuan</p>
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['approved']; ?></h3>
                        <p>Disetujui</p>
                    </div>
                </div>
                
                <div class="stat-card red">
                    <div class="stat-icon">❌</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['rejected']; ?></h3>
                        <p>Ditolak</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>🚀 Aksi Cepat</h2>
                <div class="action-cards">
                    <a href="pengajuan.php" class="action-card primary">
                        <div class="action-icon">➕</div>
                        <h3>Ajukan Peminjaman Baru</h3>
                        <p>Buat pengajuan peminjaman ruangan untuk kegiatan Anda</p>
                    </a>
                    
                    <a href="lihat_jadwal.php" class="action-card">
                        <div class="action-icon">📅</div>
                        <h3>Cek Jadwal Ruangan</h3>
                        <p>Lihat ketersediaan ruangan sebelum mengajukan</p>
                    </a>
                    
                    <a href="pengajuan.php?tab=history" class="action-card">
                        <div class="action-icon">📋</div>
                        <h3>Lihat Riwayat</h3>
                        <p>Pantau status pengajuan Anda</p>
                    </a>
                    
                    </div>
            </div>

            <div class="recent-bookings">
                <h2>📋 Pengajuan Terbaru Anda</h2>
                <div class="booking-list" id="recentBookings">
                    <div class="loading">🔄 Memuat pengajuan terbaru...</div>
                </div>
            </div>

            </main>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>