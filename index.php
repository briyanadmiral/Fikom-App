<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
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
$name = $_SESSION['user_name'];
$role = $_SESSION['role'];
$program = $_SESSION['program'] ?? null;
$nim = $_SESSION['nim'] ?? '-';
$email = $_SESSION['user_email'];
$picture = $_SESSION['user_picture'];

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* === TEMA GLASSMORPHISM (GREY UI/UX) === */
        :root {
            --primary: #8a9ccc; /* Subtle blue/purple accent */
            --primary-soft: rgba(255, 255, 255, 0.5);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.4); /* Translucent */
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Efek Kaca (Glassmorphism) Reusable */
        .glass-panel {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        /* Top Navigation / Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .header .logo img { height: 40px; width: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-left: 12px;
            border-left: 2px solid rgba(255, 255, 255, 0.6);
        }

        .user-profile img { 
            width: 40px; height: 40px; 
            border-radius: 10px; 
            object-fit: cover;
            border: 2px solid var(--primary-soft);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .user-details strong { font-size: 0.9rem; color: var(--dark); display: block; }
        .user-details small { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }

        .logout-btn { 
            width: 35px; height: 35px; 
            display: grid; place-items: center;
            background: rgba(255, 255, 255, 0.5); 
            color: #ef4444; border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 8px; font-size: 0.9rem;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
        }
        .logout-btn:hover { background: #ef4444; color: white; transform: translateY(-2px); border-color: #ef4444; }

        /* Welcome Section */
        .welcome-section {
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            color: var(--dark);
        }

        .welcome-section h1 { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.5rem; text-shadow: 0 1px 2px rgba(255,255,255,0.8); }
        .welcome-section p { color: var(--text-muted); font-size: 1rem; max-width: 600px; }

        /* Menu Grid */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::before {
            content: ''; width: 4px; height: 20px; background: var(--primary); border-radius: 10px;
            box-shadow: 0 0 5px var(--primary);
        }

        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            width: 100%;
        }

        .action-card {
            flex: 1 1 200px;
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            text-decoration: none;
            padding: 2rem 1.5rem;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.7) 0%, transparent 60%);
            opacity: 0; transition: opacity 0.3s; pointer-events: none;
        }

        .action-card i {
            width: 60px; height: 60px;
            display: grid; place-items: center;
            background: rgba(255, 255, 255, 0.6);
            color: var(--primary);
            font-size: 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
            transition: all 0.3s;
        }

        .action-card h3 { 
            font-size: 1rem; 
            font-weight: 600; 
            color: var(--dark);
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255,255,255,0.9);
            box-shadow: 0 15px 35px 0 rgba(31, 38, 135, 0.12);
        }

        .action-card:hover::before { opacity: 1; }

        .action-card:hover i {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
            border-color: var(--primary);
        }

        /* Responsivitas */
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 1rem; text-align: center; }
            .user-profile { border: none; padding: 0; }
            .welcome-section { text-align: center; padding: 1.5rem; }
            .quick-actions { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <header class="header">
            <div class="logo">
                <img src="assets/img/fikom.png" alt="Logo Fikom">
            </div>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="user-profile">
                    <div class="user-details" style="text-align: right;">
                        <strong><?php echo htmlspecialchars($name); ?></strong>
                        <small>
                            <?php echo ucfirst($role); ?>
                            <?php if ($role == 'mahasiswa')
    echo " • " . htmlspecialchars($nim); ?>
                        </small>
                    </div>
                    <img src="<?php echo htmlspecialchars($picture); ?>" alt="User Avatar">
                </div>
                <a href="logout.php" class="logout-btn" title="Keluar">
                    <i class="fas fa-power-off"></i>
                </a>
            </div>
        </header>

        <section class="welcome-section">
            <h1>Halo, <?php echo strtok($name, " "); ?>! 👋</h1>
            <p>Akses cepat ke semua layanan akademik dan inventaris FIKOM UNIKA dalam satu tempat.</p>
        </section>

        <h2 class="section-title">Layanan Utama</h2>
        <div class="quick-actions">
            <a href="inventory.php" class="action-card">
                <i class="fas fa-boxes-stacked"></i>
                <h3>Inventory</h3>
            </a>
            
            <a href="peminjamanRuangan.php" class="action-card">
                <i class="fas fa-door-open"></i>
                <h3>Pinjam Ruangan</h3>
            </a>

            <?php if ($role == 'dosen' || $role == 'superadmin'): ?>
            <a href="surat.php" class="action-card">
                <i class="fas fa-envelope-open-text"></i>
                <h3>Manajemen Surat</h3>
            </a>
            
            <a href="mou.php" class="action-card">
                <i class="fas fa-file-contract"></i>
                <h3>Arsip MOU</h3>
            </a>
            <?php
endif; ?>
        </div>
    </div>

</body>
</html>