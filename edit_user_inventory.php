<?php
session_start();

// 1. Keamanan: Pastikan hanya Superadmin yang bisa akses halaman ini
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: login.php'); 
    exit;
}

// 2. Koneksi ke database Inventory
$conn = mysqli_connect('localhost', 'root', '', 'db_inventaris_lab');

// 3. Tangkap ID dari URL
if (!isset($_GET['id'])) {
    header("Location: inventory.php"); 
    exit;
}
$id_user = (int)$_GET['id'];

// 4. PROSES UPDATE DATA JIKA FORM DISUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $n_email = mysqli_real_escape_string($conn, $_POST['email']);
    $n_nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $n_role  = mysqli_real_escape_string($conn, $_POST['role']);
    $n_prodi = (int)$_POST['id_prodi'];
    
    $query_update = "UPDATE users SET email='$n_email', nama='$n_nama', role='$n_role', id_prodi=$n_prodi WHERE id_user=$id_user";
    mysqli_query($conn, $query_update);
    
    // Kembali ke Control Panel setelah sukses
    header("Location: inventory.php");
    exit;
}

// 5. AMBIL DATA USER SAAT INI UNTUK DITAMPILKAN DI FORM
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $id_user");
$user_data = mysqli_fetch_assoc($query);

// Jika user tidak ditemukan di database, kembalikan ke halaman sebelumnya
if (!$user_data) {
    header("Location: inventory.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Inventory - FIKOM APP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* === TEMA MODEL A1 === */
        :root {
            --primary: #2563eb;
            --primary-soft: #eff6ff;
            --dark: #1e293b;
            --text-main: #334155;
            --text-muted: #64748b;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --shadow: 0 10px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.01);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .edit-card {
            background: var(--bg-card);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .edit-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .edit-header i {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 15px;
            background: var(--primary-soft);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
        }

        .edit-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            letter-spacing: -0.01em;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .form-control {
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s;
            background-color: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px var(--primary-soft);
        }

        .btn-wrapper {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgb(37 99 235 / 0.2);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px -1px rgb(37 99 235 / 0.3);
        }

        .btn-secondary {
            background: var(--bg-body);
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: var(--dark);
        }
    </style>
</head>
<body>

    <div class="edit-card">
        <div class="edit-header">
            <i class="fas fa-user-edit"></i>
            <h2>Edit Akses Pengguna</h2>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user_data['nama']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email Kampus / Pribadi</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user_data['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Hak Akses (Role)</label>
                <select name="role" class="form-control" required>
                    <option value="admin" <?= ($user_data['role'] == 'admin') ? 'selected' : '' ?>>Admin Lab (Pengelola)</option>
                    <option value="user" <?= ($user_data['role'] == 'user') ? 'selected' : '' ?>>User Biasa (Peminjam)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Program Studi</label>
                <select name="id_prodi" class="form-control" required>
                    <option value="1" <?= ($user_data['id_prodi'] == 1) ? 'selected' : '' ?>>Sistem Informasi</option>
                    <option value="2" <?= ($user_data['id_prodi'] == 2) ? 'selected' : '' ?>>Teknik Informatika</option>
                </select>
            </div>

            <div class="btn-wrapper">
                <a href="inventory.php" class="btn btn-secondary">Batal</a>
                <button type="submit" name="update_user" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

</body>
</html>