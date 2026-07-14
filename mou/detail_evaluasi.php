<?php
session_start();

// --- SATPAM SESSION DARI BRIDGE ---
if (!isset($_SESSION['mou_admin']) || $_SESSION['mou_admin'] !== true) {
    header("Location: ../mou.php");
    exit;
}

include 'koneksi.php';

$id_eval = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_eval <= 0) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h2>ID Evaluasi tidak valid!</h2></div>");
}

$query = "
    SELECT ee.*, ke.ket_evaluasi, p.nama_pelaksanaan, p.id_pelaksanaan
    FROM evaluasi_eksternal ee
    JOIN pelaksanaan p ON ee.id_pelaksanaan = p.id_pelaksanaan
    LEFT JOIN keterangan_evaluasi ke ON ee.id_ket_evaluasi = ke.id_ket_evaluasi
    WHERE ee.id_eval_eksternal = $id_eval
";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h2>Data Evaluasi tidak ditemukan!</h2></div>");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Hasil Evaluasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-soft: rgba(79, 70, 229, 0.1);
            --bg-body: #eef2f6;
            --bg-card: rgba(255, 255, 255, 0.65);
            --border: rgba(255, 255, 255, 0.8);
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --dark: #111827;
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.08);
            --glass-blur: blur(12px);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.8) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255, 255, 255, 0.7) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(200, 205, 215, 0.5) 0%, transparent 60%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .detail-card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            max-width: 680px;
            width: 100%;
            padding: 40px;
        }

        .header-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .header-logo img {
            max-height: 50px;
            object-fit: contain;
        }

        .detail-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
            margin-bottom: 5px;
        }

        .detail-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark);
            border-bottom: 1.5px solid rgba(0, 0, 0, 0.08);
            padding-bottom: 8px;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .detail-item {
            margin-bottom: 12px;
            font-size: 0.92rem;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 180px;
            display: inline-block;
            vertical-align: top;
        }

        .detail-val {
            color: var(--dark);
            display: inline-block;
            width: calc(100% - 190px);
            vertical-align: top;
        }

        .question-box {
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .question-text {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .answer-text {
            font-size: 0.92rem;
            color: var(--primary);
            font-weight: 600;
        }

        .btn-back-detail {
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-main);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-back-detail:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary);
        }
    </style>
</head>
<body>

<div class="detail-card">
    <div class="header-logo">
        <img src="../assets/img/fikom.png" alt="Logo Fikom" onerror="this.style.display='none'">
    </div>

    <h2 class="detail-title">Detail Hasil Evaluasi</h2>
    <p class="detail-subtitle">Hasil kuesioner kepuasan pelaksanaan kerja sama mitra eksternal.</p>

    <div class="section-title"><i class="bi bi-info-circle me-2"></i>Informasi Umum</div>
    
    <div class="detail-item">
        <span class="detail-label">Nama Kegiatan</span>
        <span class="detail-val"><?= htmlspecialchars($data['nama_pelaksanaan']) ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Pengisi Evaluasi</span>
        <span class="detail-val"><?= htmlspecialchars($data['pemberi_evaluasi']) ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Tanggal Pengisian</span>
        <span class="detail-val"><?= date('d F Y', strtotime($data['tanggal_evaluasi'])) ?></span>
    </div>
    
    <div class="detail-item">
        <span class="detail-label">Status Implementasi</span>
        <span class="detail-val">
            <span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($data['ket_evaluasi'] ?? '-') ?></span>
        </span>
    </div>

    <div class="section-title"><i class="bi bi-clipboard-check me-2"></i>Hasil Kuesioner Kepuasan</div>

    <?php if (!empty($data['q1'])): ?>
        <!-- Q1 -->
        <div class="question-box">
            <div class="question-text">1. Bagaimana penilaian Bapak/Ibu secara keseluruhan terhadap jalannya program kerja sama?</div>
            <div class="answer-text"><i class="bi bi-patch-check-fill me-1"></i> <?= htmlspecialchars($data['q1']) ?></div>
        </div>

        <!-- Q2 -->
        <div class="question-box">
            <div class="question-text">2. Aspek komunikasi, responsivitas, dan pelayanan administrasi dari pihak Fakultas?</div>
            <div class="answer-text"><i class="bi bi-patch-check-fill me-1"></i> <?= htmlspecialchars($data['q2']) ?></div>
        </div>

        <!-- Q3 -->
        <div class="question-box">
            <div class="question-text">3. Apakah program kerja sama memberikan dampak positif atau nilai tambah yang nyata?</div>
            <div class="answer-text"><i class="bi bi-patch-check-fill me-1"></i> <?= htmlspecialchars($data['q3']) ?></div>
        </div>

        <!-- Q4 -->
        <div class="question-box">
            <div class="question-text">4. Saran singkat atau area mana yang paling perlu kami tingkatkan?</div>
            <div class="answer-text text-dark fw-normal" style="white-space: pre-line;"><i class="bi bi-chat-right-text me-1 text-primary"></i> <?= htmlspecialchars($data['q4']) ?></div>
        </div>
    <?php else: ?>
        <div class="question-box">
            <div class="question-text">Evaluasi / Saran (Legacy)</div>
            <div class="answer-text text-dark fw-normal" style="white-space: pre-line;"><?= htmlspecialchars($data['evaluasi']) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($data['bukti'])): ?>
        <div class="section-title"><i class="bi bi-paperclip me-2"></i>Bukti Pelaksanaan / Pendukung</div>
        <div class="mb-4">
            <a href="uploads/<?= htmlspecialchars($data['bukti']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-file-earmark-arrow-down me-1"></i> Lihat Bukti Dokumen
            </a>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-center mt-4">
        <a href="evaluasi2_pelaksanaan.php?id=<?= $data['id_pelaksanaan'] ?>" class="btn btn-back-detail">
            <i class="bi bi-arrow-left me-2"></i> Kembali ke Form & Riwayat
        </a>
    </div>
</div>

</body>
</html>
