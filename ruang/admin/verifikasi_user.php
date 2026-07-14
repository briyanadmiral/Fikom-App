<?php
// admin/verifikasi_user.php - Verifikasi Mahasiswa Baru
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

$message = '';
$message_type = '';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($db) {
        try {
            if ($action === 'approve') {
                $stmt = $db->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                if ($stmt->rowCount() > 0) {
                    $message = "Mahasiswa berhasil disetujui.";
                    $message_type = "success";
                }
            } elseif ($action === 'decline') {
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([id]);
                if ($stmt->rowCount() > 0) {
                    $message = "Pengajuan akses mahasiswa berhasil ditolak dan dihapus.";
                    $message_type = "success";
                }
            }
        } catch (Exception $e) {
            $message = "Gagal memproses aksi: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Fetch pending & approved users
$pending_users = [];
$active_users = [];

if ($db) {
    try {
        // Pending
        $stmt = $db->prepare("SELECT * FROM users WHERE role = 'mahasiswa' AND status = 'pending' ORDER BY id DESC");
        $stmt->execute();
        $pending_users = $stmt->fetchAll();

        // Active
        $stmt = $db->prepare("SELECT * FROM users WHERE role = 'mahasiswa' AND status = 'active' ORDER BY id DESC");
        $stmt->execute();
        $active_users = $stmt->fetchAll();

        // Count pending requests for sidebar badge
        $stmt = $db->query("SELECT COUNT(*) as total FROM pengajuan_peminjaman WHERE status = 'pending'");
        $pending_requests_count = $stmt->fetch()['total'] ?? 0;
    } catch (Exception $e) {
        error_log("Error fetching users: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Mahasiswa - Sentralisasi Ruangan FIKOM</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .tabs-container {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .tab-menu {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 20px;
            gap: 20px;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #1a73e8;
            border-bottom: 2px solid #1a73e8;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-success {
            background-color: #16a34a;
            color: white;
            border: none;
        }
        .btn-success:hover {
            background-color: #15803d;
        }
        .btn-danger {
            background-color: #dc2626;
            color: white;
            border: none;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #d97706;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-active {
            background-color: #dcfce7;
            color: #16a34a;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
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
                <a href="dashboard.php" class="nav-item">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
                
                <a href="kelola-ruangan.php" class="nav-item">
                    <i class="bi bi-building me-2"></i> Kelola Ruangan
                    <span class="badge" id="nav-badge-pending-admin" style="<?= ($pending_requests_count > 0) ? '' : 'display: none;' ?>"><?php echo $pending_requests_count; ?></span>
                </a>
                
                <a href="lihat_jadwal.php" class="nav-item">
                    <i class="bi bi-calendar3 me-2"></i> Lihat Jadwal
                </a>
                
                <a href="riwayat.php" class="nav-item">
                    <i class="bi bi-clock-history me-2"></i> Riwayat Pengajuan
                </a>
                
                <a href="laporan.php" class="nav-item">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Laporan Peminjaman
                </a>
                
                <a href="verifikasi_user.php" class="nav-item active">
                    <i class="bi bi-person-check me-2"></i> Verifikasi Mahasiswa
                </a>

                <hr class="mx-3 opacity-25">

                <a href="../logout.php" class="nav-item logout">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Verifikasi Mahasiswa Baru</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($user_info['nama'] ?? 'Admin'); ?></span>
                    <div class="user-avatar">👨‍💼</div>
                </div>
            </header>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="tabs-container">
                <div class="tab-menu">
                    <button class="tab-btn active" onclick="openTab(event, 'pending-tab')">Pending (<?= count($pending_users) ?>)</button>
                    <button class="tab-btn" onclick="openTab(event, 'active-tab')">Approved (<?= count($active_users) ?>)</button>
                </div>

                <!-- Tab Pending -->
                <div id="pending-tab" class="tab-content active">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0; text-align: left;">
                                <th style="padding: 12px;">Nama</th>
                                <th style="padding: 12px;">NIM</th>
                                <th style="padding: 12px;">Email</th>
                                <th style="padding: 12px;">Jurusan</th>
                                <th style="padding: 12px;">Status</th>
                                <th style="padding: 12px; width: 220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pending_users) === 0): ?>
                                <tr>
                                    <td colspan="6" style="padding: 20px; text-align: center; color: #64748b;">Tidak ada mahasiswa menunggu verifikasi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pending_users as $user): ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($user['nama']) ?></td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($user['nim_nip']) ?></td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($user['email']) ?></td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($user['jurusan']) ?></td>
                                        <td style="padding: 12px;"><span class="badge-pending">Pending</span></td>
                                        <td style="padding: 12px;">
                                            <a href="?action=approve&id=<?= $user['id'] ?>" class="btn-sm btn-success" onclick="return confirm('Setujui mahasiswa ini untuk mengakses Peminjaman Ruangan?');">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </a>
                                            <a href="?action=decline&id=<?= $user['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Tolak dan hapus pengajuan akses mahasiswa ini?');" style="margin-left: 8px;">
                                                <i class="bi bi-x-lg"></i> Decline
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tab Active -->
                <div id="active-tab" class="tab-content">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0; text-align: left;">
                                <th style="padding: 12px;">Nama</th>
                                <th style="padding: 12px;">NIM</th>
                                <th style="padding: 12px;">Email</th>
                                <th style="padding: 12px;">Jurusan</th>
                                <th style="padding: 12px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($active_users) === 0): ?>
                                <tr>
                                    <td colspan="5" style="padding: 20px; text-align: center; color: #64748b;">Belum ada mahasiswa yang aktif.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($active_users as $user): ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 12px; font-weight: 600;"><?= htmlspecialchars($user['nama']) ?></td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($user['nim_nip']) ?></td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($user['email']) ?></td>
                                        <td style="padding: 12px;"><?= htmlspecialchars($user['jurusan']) ?></td>
                                        <td style="padding: 12px;"><span class="badge-active">Approved</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openTab(evt, tabId) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>
