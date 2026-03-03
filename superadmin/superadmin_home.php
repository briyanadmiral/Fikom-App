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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* CSS Variables untuk kemudahan kustomisasi warna */
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --body-bg: #f4f7fc;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-color: #495057;
            --border-color: #dee2e6;
        }

        /* Reset & Body Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-color);
        }

        /* Layout Utama: Sidebar + Main Content */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background-color: var(--sidebar-bg);
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .main-content {
            margin-left: 260px; /* Lebar sidebar */
            padding: 20px;
        }

        /* Styling Sidebar */
        .sidebar .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        .sidebar .logo img {
            max-width: 150px;
        }
        .sidebar .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--secondary-color);
            transition: background-color 0.2s, color 0.2s;
        }
        .sidebar .nav-links a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }
        .sidebar .nav-links a:hover,
        .sidebar .nav-links a.active {
            background-color: var(--primary-color);
            color: white;
        }
        .sidebar .logout-link {
            margin-top: auto; /* Mendorong link logout ke bawah */
        }

        /* Header / Navbar di Main Content */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        /* Card untuk membungkus tabel */
        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
        }
        
        /* Tombol Aksi */
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: opacity 0.2s;
        }
        .btn:hover {
            opacity: 0.85;
        }
        .btn-primary { background-color: var(--primary-color); color: white; }
        .btn-edit { background-color: var(--secondary-color); color: white; }
        .btn-impersonate { background-color: var(--warning-color); color: var(--dark-color); }
        .btn-delete { background-color: var(--danger-color); color: white; }

        /* Styling Tabel */
        #userTable {
            width: 100%;
            border-collapse: collapse;
        }
        #userTable th, #userTable td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        #userTable th {
            font-weight: 600;
            font-size: 14px;
            background-color: var(--light-color);
        }
        #userTable tbody tr:hover {
            background-color: #f8f9fa;
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
            <a href="#"><i class="fas fa-users"></i> Manajemen Dosen</a>
            <a href="#"><i class="fas fa-user-graduate"></i> Manajemen Mahasiswa</a>
            <a href="#"><i class="fas fa-cog"></i> Pengaturan</a>
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
                        <th style="width: 200px;">Aksi</th> </tr>
                </thead>
                <tbody>
                <?php while($td = mysqli_fetch_assoc($tampil_dosen)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($td['nip']); ?></td>
                        <td><?php echo htmlspecialchars($td['nama']); ?></td>
                        <td><?php echo htmlspecialchars($td['jurusan']); ?></td>
                        <td><?php echo htmlspecialchars($td['email']); ?></td>
                        <td class="actions-cell">
                            <a href="impersonate.php?id_dosen=<?php echo $td['id_dosen']; ?>" class="btn btn-impersonate" title="Login sebagai user ini">
                                <i class="fas fa-user-secret"></i>
                            </a>
                            <a href="edit_dosen.php?id_dosen=<?php echo $td['id_dosen']; ?>" class="btn btn-edit" title="Edit user">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                            <a href="hapus_dosen.php?id_dosen=<?php echo $td['id_dosen']; ?>" class="btn btn-delete" title="Hapus user" onclick="return confirm('Yakin ingin menghapus data <?php echo htmlspecialchars($td['nama']); ?>?')">
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