<?php 
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../login.php'); 
    exit;
}

$koneksi = mysqli_connect('localhost', 'root', '', 'fikomapp');
if (!$koneksi) {
  die("Koneksi database gagal: " . mysqli_connect_error());
}

$query_dosen = "SELECT * FROM dosen ORDER BY nama ASC";
$tampil_dosen = mysqli_query($koneksi, $query_dosen);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Superadmin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* === TEMA GLASSMORPHISM (GREY UI/UX) === */
        :root {
            --primary: #8a9ccc;
            --primary-soft: rgba(255, 255, 255, 0.5);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.4);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
            
            /* Warna khusus aksi */
            --danger: #ef4444;
            --danger-soft: rgba(239, 68, 68, 0.15);
            --warning: #f59e0b;
            --warning-soft: rgba(245, 158, 11, 0.15);
            --info: #0ea5e9;
            --info-soft: rgba(14, 165, 233, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

        /* Layout Utama */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            padding: 24px;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px 40px;
            min-height: 100vh;
        }

        /* Styling Sidebar */
        .sidebar .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        .sidebar .logo img {
            max-width: 140px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .sidebar a i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        .sidebar a:hover {
            background-color: rgba(255,255,255,0.4);
            color: var(--dark);
        }
        .sidebar a.active {
            background-color: var(--primary-soft);
            color: var(--primary);
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            border: 1px solid rgba(255,255,255,0.5);
        }

        .sidebar .logout-link {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .sidebar .logout-link a {
            color: var(--danger);
        }
        .sidebar .logout-link a:hover {
            background-color: var(--danger-soft);
        }

        /* Header / Navbar di Main Content */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            padding: 15px 25px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.01em;
            text-shadow: 0 1px 2px rgba(255,255,255,0.8);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-left: 2px solid rgba(255, 255, 255, 0.6);
        }
        .user-info span {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dark);
        }
        .user-info img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid var(--primary-soft);
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* Card Tabel */
        .card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }

        /* Tombol */
        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary { 
            background-color: var(--primary); 
            color: white; 
            box-shadow: 0 4px 6px -1px rgb(37 99 235 / 0.2);
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            box-shadow: 0 6px 8px -1px rgb(37 99 235 / 0.3);
        }

        /* Tombol Aksi di Tabel (Ikon Saja) */
        .btn-action {
            padding: 8px;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .btn-impersonate { background-color: var(--warning-soft); color: #d97706; }
        .btn-impersonate:hover { background-color: var(--warning); color: white; }

        .btn-edit { background-color: var(--info-soft); color: var(--info); }
        .btn-edit:hover { background-color: var(--info); color: white; }

        .btn-delete { background-color: var(--danger-soft); color: var(--danger); }
        .btn-delete:hover { background-color: var(--danger); color: white; }

        /* Styling Tabel ala SaaS */
        #userTable {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        #userTable th, #userTable td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        #userTable th {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background-color: #f8fafc;
        }
        #userTable th:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        #userTable th:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        
        #userTable tbody tr {
            transition: background-color 0.2s;
        }
        #userTable tbody tr:hover {
            background-color: var(--bg-body);
        }
        #userTable td {
            font-size: 0.95rem;
            color: var(--text-main);
        }
        
        .actions-cell {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
    
    <aside class="sidebar">
        <div class="logo">
            <img src="../assets/img/fikom.png" alt="Logo Fikom"> 
        </div>
        <nav class="nav-links">
            <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="../inventory.php"><i class="fas fa-boxes-stacked"></i> Inventory</a>
            <a href="../peminjamanRuangan.php"><i class="fas fa-door-open"></i> Peminjaman Ruangan</a>
            <a href="../mou.php"><i class="fas fa-file-contract"></i> Arsip MOU</a>
            <a href="../surat.php"><i class="fas fa-envelope-open-text"></i> Sistem Surat</a>
        </nav>
        
        <div class="logout-link">
             <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="header">
            <h1>Selamat Datang, Superadmin!</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <img src="<?php echo htmlspecialchars($_SESSION['user_picture']); ?>" alt="Admin Avatar">
            </div>
        </header>

        <div class="card">
            <div class="card-header">
                <h2>Manajemen User Dosen</h2>
                <a href="tambah_user.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Dosen</a>
            </div>
            
            <table id="userTable">
                <thead>
                    <tr>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Jurusan</th>
                        <th>Email</th>
                        <th style="width: 150px;">Aksi</th> 
                    </tr>
                </thead>
                <tbody>
                <?php while($td = mysqli_fetch_assoc($tampil_dosen)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($td['nip']); ?></td>
                        <td><strong><?php echo htmlspecialchars($td['nama']); ?></strong></td>
                        <td><?php echo htmlspecialchars($td['jurusan']); ?></td>
                        <td><?php echo htmlspecialchars($td['email']); ?></td>
                        <td class="actions-cell">
                            <a href="impersonate.php?id_dosen=<?php echo $td['id_dosen']; ?>" class="btn btn-action btn-impersonate" title="Login sebagai user ini">
                                <i class="fas fa-user-secret"></i>
                            </a>
                            <a href="edit_dosen.php?id_dosen=<?php echo $td['id_dosen']; ?>" class="btn btn-action btn-edit" title="Edit user">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a href="hapus_dosen.php?id_dosen=<?php echo $td['id_dosen']; ?>" class="btn btn-action btn-delete" title="Hapus user" onclick="return confirm('Yakin ingin menghapus data <?php echo htmlspecialchars($td['nama']); ?>?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>