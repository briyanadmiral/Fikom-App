<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - FIKOM UNIKA Soegijapranata</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --primary: #8a9ccc;
            --primary-hover: #7587b8;
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
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.6s ease-out;
        }

        .card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            padding: 40px 30px;
            text-align: center;
        }

        .header {
            margin-bottom: 25px;
        }

        .logo-text {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--dark);
            letter-spacing: 2px;
            margin-bottom: 5px;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .error-box {
            background-color: rgba(220, 53, 69, 0.08);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #b21f2d;
            padding: 18px;
            border-radius: 12px;
            font-size: 0.92rem;
            text-align: left;
            margin-bottom: 30px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .error-box i {
            margin-top: 3px;
            font-size: 1.25rem;
            color: #dc3545;
        }

        .summary-list {
            background: rgba(255, 255, 255, 0.25);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
        }

        .summary-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--dark);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding-bottom: 6px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.92rem;
        }

        .summary-item:last-child {
            margin-bottom: 0;
        }

        .summary-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .summary-val {
            color: var(--dark);
            font-weight: 600;
            text-align: right;
            word-break: break-all;
            padding-left: 10px;
        }

        .status-badge {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .btn-exit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(138, 156, 204, 0.2);
            text-decoration: none;
        }

        .btn-exit:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 156, 204, 0.3);
            color: white;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="header">
            <div class="logo-text">FIKOM</div>
            <div class="subtitle">Permohonan Ditolak</div>
        </div>

        <div class="error-box">
            <i class="fas fa-times-circle"></i>
            <div>
                <strong>Akses Ditolak!</strong><br>
                Permohonan akses Anda ke sistem Arsip Surat FIKOM telah ditolak oleh Admin Tata Usaha (TU). Silakan hubungi bagian TU Fakultas untuk informasi lebih lanjut.
            </div>
        </div>

        <div class="summary-list">
            <div class="summary-title">Detail Akun Anda</div>
            
            <div class="summary-item">
                <span class="summary-label">Nama</span>
                <span class="summary-val">{{ $user->nama_lengkap }}</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">NIM</span>
                <span class="summary-val">{{ $user->nim ?? '—' }}</span>
            </div>
            
            <div class="summary-item">
                <span class="summary-label">Email</span>
                <span class="summary-val">{{ $user->email }}</span>
            </div>

            <div class="summary-item" style="margin-top: 15px; border-top: 1px dashed rgba(0,0,0,0.08); padding-top: 10px;">
                <span class="summary-label">Status Akses</span>
                <span class="summary-val">
                    <span class="status-badge">Rejected / Ditolak</span>
                </span>
            </div>
        </div>

        <form action="{{ route('external.exit') }}" method="POST">
            @csrf
            <button type="submit" class="btn-exit">
                <i class="fas fa-right-from-bracket"></i> Kembali ke Portal Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
