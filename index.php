<?php
session_start();
if(!isset($_SESSION['logged_in'])){
    header('Location: login.php'); exit;
}

// Banner untuk Superadmin yang sedang impersonating
if (isset($_SESSION['original_admin_email'])) {
    echo '<div style="background-color: #dc3545; color: white; padding: 12px; text-align: center; position: fixed; width: 100%; top: 0; z-index: 9999; font-size: 16px;">';
    echo 'Anda sedang login sebagai <strong>' . htmlspecialchars($_SESSION['user_name']) . '</strong>. ';
    echo '<a href="stop_impersonating.php" style="color: white; font-weight: bold; text-decoration: underline; margin-left: 15px;">Kembali ke Akun Superadmin</a>';
    echo '</div>';
    echo '<div style="height: 50px;"></div>'; // Spacer
}

// Data dari session
$name    = $_SESSION['user_name'];
$role    = $_SESSION['role'];
$program = $_SESSION['program'] ?? null;
$nim     = $_SESSION['nim'] ?? '-';
$email   = $_SESSION['user_email'];
$picture = $_SESSION['user_picture'];


$aktivitas_terbaru = [
    ['icon' => 'fa-box', 'text' => 'Peminjaman <strong>Proyektor EPSON-01</strong> telah disetujui.', 'time' => '2 jam yang lalu'],
    ['icon' => 'fa-calendar-check', 'text' => 'Peminjaman <strong>Ruang Diskusi 3</strong> berhasil dikonfirmasi.', 'time' => '1 hari yang lalu'],
    ['icon' => 'fa-file-alt', 'text' => 'Nilai OBE untuk <strong>Dasar Pemrograman</strong> telah diperbarui.', 'time' => '3 hari yang lalu'],
];

$pengumuman = [
    'title' => 'Perawatan Sistem Inventory',
    'body' => 'Akan diadakan perawatan sistem pada hari Sabtu pukul 22:00. Mohon untuk tidak melakukan transaksi peminjaman pada waktu tersebut.',
];

$status_peminjaman = [
    'alat' => 2, // contoh: 2 alat sedang dipinjam
    'ruangan' => 1, // contoh: 1 ruangan sedang dipinjam
];
// --- AKHIR DATA DUMMY ---

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FIKOM APP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --body-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #495057;
            --heading-color: #212529;
            --border-color: #dee2e6;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--body-bg); color: var(--text-color); }
        a { text-decoration: none; color: inherit; }

        .dashboard-container {
            display: flex;
            max-width: 1400px;
            margin: auto;
            padding: 20px;
            gap: 25px;
        }
        .main-content { flex: 3; }
        .right-sidebar { flex: 1; }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 25px;
            background-color: var(--card-bg);
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .header .logo img { max-width: 150px; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-profile img { width: 45px; height: 45px; border-radius: 50%; }
        .user-profile .user-details strong { font-weight: 600; color: var(--heading-color); }
        .user-profile .user-details small { color: var(--secondary-color); }
        .logout-btn { color: #dc3545; font-size: 20px; margin-left: 15px; }
        
        /* General Card Style */
        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--heading-color);
            margin-bottom: 20px;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(90deg, #0d6efd, #0d95fd);
            color: white;
            padding: 30px;
            border-radius: 12px;
        }
        .welcome-banner h1 { font-size: 28px; font-weight: 700; }
        .welcome-banner p { margin-top: 5px; opacity: 0.9; }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        .action-card {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .action-card i { font-size: 32px; margin-bottom: 15px; }
        .action-card h3 { font-size: 16px; font-weight: 600; }

        /* Activity Feed */
        .activity-feed ul { list-style: none; }
        .activity-feed li { display: flex; align-items: flex-start; gap: 15px; padding: 15px 0; border-bottom: 1px solid var(--border-color); }
        .activity-feed li:last-child { border-bottom: none; }
        .activity-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background-color: #e9ecef;
            color: var(--secondary-color);
        }
        .activity-details p { margin: 0; line-height: 1.5; }
        .activity-details small { color: var(--secondary-color); font-size: 13px; }

        /* Right Sidebar */
        .status-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px; text-align: center;
        }
        .status-item .count { font-size: 28px; font-weight: 700; color: var(--primary-color); }
        .status-item .label { font-size: 14px; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <main class="main-content">
            <header class="header">
                <div class="logo">
                    <img src="assets/img/fikom.png" alt="Logo Fikom">
                </div>
                <div class="user-profile">
                    <img src="<?php echo htmlspecialchars($picture); ?>" alt="User Avatar">
                    <div class="user-details">
                        <strong><?php echo htmlspecialchars($name); ?></strong><br>
                        <small>
                            <?php echo ucfirst($role); ?>
                            <?php if ($role == 'mahasiswa') echo " • NIM: ".htmlspecialchars($nim); ?>
                        </small>
                    </div>
                    <a href="logout.php" class="logout-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="welcome-banner card">
                <h1>Selamat Datang Kembali, <?php echo strtok($name, " "); ?>!</h1>
                <p>Semoga harimu menyenangkan. Manfaatkan semua fitur yang ada untuk mendukung aktivitasmu.</p>
            </div>

            <div class="card">
                <h2 class="card-title">Menu Utama</h2>
                <div class="quick-actions">
                    <a href="inventory.php" class="action-card">
                        <i class="fas fa-boxes-stacked"></i>
                        <h3>Inventory</h3>
                    </a>
                    <a href="obe.php" class="action-card">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>OBE</h3>
                    </a>
                    <a href="peminjamanRuangan.php" class="action-card">
                        <i class="fas fa-door-open"></i>
                        <h3>Peminjaman Ruangan</h3>
                    </a>
                    <?php if($role == 'dosen'): ?>
                    <a href="surat.php" class="action-card">
                        <i class="fas fa-envelope-open-text"></i>
                        <h3>Surat</h3>
                    </a>
                    <?php endif; ?>
                    <?php if($role == 'dosen'): ?>
                    <a href="mou.php" class="action-card">
                        <i class="fas fa-envelope-open-text"></i>
                        <h3>MOU</h3>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card activity-feed">
                <h2 class="card-title">Aktivitas Terbaru</h2>
                <ul>
                    <?php foreach ($aktivitas_terbaru as $aktivitas): ?>
                    <li>
                        <div class="activity-icon"><i class="fas <?php echo $aktivitas['icon']; ?>"></i></div>
                        <div class="activity-details">
                            <p><?php echo $aktivitas['text']; ?></p>
                            <small><?php echo $aktivitas['time']; ?></small>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </main>

        <aside class="right-sidebar">
            <div class="card">
                <h2 class="card-title"><i class="fas fa-bullhorn" style="margin-right: 8px;"></i> Pengumuman</h2>
                <strong><?php echo htmlspecialchars($pengumuman['title']); ?></strong>
                <p style="margin-top: 5px; font-size: 14px;"><?php echo htmlspecialchars($pengumuman['body']); ?></p>
            </div>
            
            <div class="card">
                <h2 class="card-title">Status Peminjaman Aktif</h2>
                <div class="status-grid">
                    <div class="status-item">
                        <div class="count"><?php echo $status_peminjaman['alat']; ?></div>
                        <div class="label">Alat</div>
                    </div>
                    <div class="status-item">
                        <div class="count"><?php echo $status_peminjaman['ruangan']; ?></div>
                        <div class="label">Ruangan</div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

</body>
</html>