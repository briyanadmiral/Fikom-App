<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika status bukan pending, kembalikan ke ../mou.php
if (!isset($_SESSION['mou_status']) || $_SESSION['mou_status'] !== 'pending') {
    header("Location: ../mou.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Room - MOU FIKOM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #8a9ccc;
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.4);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .waiting-wrapper {
            width: 550px;
            max-width: 90%;
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 40px;
            text-align: center;
        }

        .icon-container {
            font-size: 60px;
            color: #e6a23c;
            margin-bottom: 25px;
            animation: pulse 2s infinite ease-in-out;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
            letter-spacing: -0.02em;
        }

        p {
            font-size: 15px;
            color: var(--text-muted);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
            text-align: left;
        }

        .info-box-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-box-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: var(--dark);
        }

        .info-value {
            color: var(--text-main);
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--border);
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.6);
            color: var(--dark);
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            border-color: var(--primary);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="waiting-wrapper">
        <div class="icon-container">
            <i class="fas fa-clock-rotate-left"></i>
        </div>
        <h2>Akses MOU Menunggu Verifikasi</h2>
        <p>Akun Dosen Anda telah terdaftar di sistem Arsip Kerja Sama (MOU) FIKOM. Silakan hubungi Admin MOU untuk memverifikasi dan menyetujui akun Anda.</p>
        
        <div class="info-box">
            <div class="info-box-item">
                <span class="info-label">Nama:</span>
                <span class="info-value"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Pengguna'); ?></span>
            </div>
            <div class="info-box-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($_SESSION['user_email'] ?? '-'); ?></span>
            </div>
            <div class="info-box-item">
                <span class="info-label">NIP / NIDN:</span>
                <span class="info-value"><?= htmlspecialchars($_SESSION['nim'] ?? '-'); ?></span>
            </div>
            <div class="info-box-item">
                <span class="info-label">Status Akses:</span>
                <span class="info-value" style="color: #e6a23c; font-weight: 600;">Pending Verification</span>
            </div>
        </div>

        <div class="btn-group">
            <a href="../mou.php" class="btn btn-primary">
                <i class="fas fa-rotate"></i> Cek Status
            </a>
            <a href="../index.php" class="btn btn-danger">
                <i class="fas fa-arrow-left"></i> Kembali ke Menu Utama
            </a>
        </div>
    </div>
</body>
</html>
