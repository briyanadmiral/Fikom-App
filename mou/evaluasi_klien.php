<?php
include 'koneksi.php';

$id_pelaksanaan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pelaksanaan <= 0) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h2>ID Kegiatan tidak valid!</h2></div>");
}

// Fetch kegiatan and company data
$query = "
    SELECT p.*, m.pihak_2, m.nama_mou 
    FROM pelaksanaan p 
    JOIN mou m ON p.id_mou = m.id_mou 
    WHERE p.id_pelaksanaan = $id_pelaksanaan AND p.deleted_at IS NULL AND m.deleted_at IS NULL
";
$result = mysqli_query($conn, $query);
$kegiatan = mysqli_fetch_assoc($result);

if (!$kegiatan) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h2>Data Kegiatan tidak ditemukan atau telah dihapus!</h2></div>");
}

// Status implementasi kegiatan - hardcoded options
$statusImplementasi = [
    1 => 'Sudah Selesai Terlaksana',
    2 => 'Belum Terlaksana',
    3 => 'Tidak Selesai Terlaksana',
];

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = intval($_POST['status']);
    $tanggal = $_POST['tanggal_evaluasi'];
    $pemberi = mysqli_real_escape_string($conn, $_POST['pemberi_evaluasi']);
    
    $q1 = mysqli_real_escape_string($conn, $_POST['q1']);
    $q2 = mysqli_real_escape_string($conn, $_POST['q2']);
    $q3 = mysqli_real_escape_string($conn, $_POST['q3']);
    $q4 = mysqli_real_escape_string($conn, $_POST['q4']);
    
    // Combine for legacy 'evaluasi' column support
    $evaluasi = mysqli_real_escape_string($conn, "Penilaian: $q1 | Komunikasi: $q2 | Dampak: $q3 | Saran: $q4");

    $bukti = '';
    if (!empty($_FILES['bukti']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $bukti = $target_dir . time() . '_' . basename($_FILES["bukti"]["name"]);
        if (!move_uploaded_file($_FILES["bukti"]["tmp_name"], $bukti)) {
            $error = 'Gagal mengupload bukti.';
        }
    }

    if (empty($error)) {
        $insert = "INSERT INTO evaluasi_eksternal (id_pelaksanaan, evaluasi, tanggal_evaluasi, pemberi_evaluasi, id_ket_evaluasi, bukti, q1, q2, q3, q4)
                   VALUES ($id_pelaksanaan, '$evaluasi', '$tanggal', '$pemberi', $status, '$bukti', '$q1', '$q2', '$q3', '$q4')";
        if (mysqli_query($conn, $insert)) {
            $success = true;
        } else {
            $error = 'Gagal menyimpan data evaluasi ke database: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Evaluasi Kepuasan Klien - FIKOM APP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #8a9ccc;
            --primary-soft: rgba(138, 156, 204, 0.15);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.6);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
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
            padding: 20px;
        }

        .form-card {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow);
            max-width: 650px;
            width: 100%;
            padding: 40px;
            transition: all 0.3s ease;
        }

        .header-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .header-logo img {
            height: 50px;
            width: auto;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
            margin-bottom: 5px;
        }

        .form-subtitle {
            font-size: 0.95rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 30px;
        }

        .info-box {
            background: rgba(255, 255, 255, 0.3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }

        .info-item {
            margin-bottom: 8px;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-weight: 600;
            display: block;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.5) !important;
            border: 1px solid var(--border) !important;
            border-radius: 10px;
            padding: 10px 15px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.7) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 0.25rem rgba(138, 156, 204, 0.2) !important;
        }

        .btn-submit {
            background: var(--primary) !important;
            color: white !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(138, 156, 204, 0.3);
        }

        .btn-submit:hover {
            background: #7a8cbc !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 156, 204, 0.4);
        }

        .success-card {
            text-align: center;
            padding: 20px 10px;
        }

        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="form-card">
    <div class="header-logo">
        <img src="../assets/img/fikom.png" alt="Logo Fikom">
    </div>

    <?php if ($success): ?>
        <div class="success-card">
            <div class="success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h3 class="form-title mb-2">Terima Kasih!</h3>
            <p class="text-muted">Evaluasi kepuasan Anda telah berhasil dikirim. Feedback Anda sangat berharga bagi kami untuk meningkatkan kualitas layanan dan kerja sama.</p>
        </div>
    <?php else: ?>
        <h2 class="form-title">Evaluasi Kepuasan Kerja Sama</h2>
        <p class="form-subtitle">Silakan isi formulir evaluasi pelaksanaan kerja sama di bawah ini.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <div class="row">
                <div class="col-md-6 info-item">
                    <span class="info-label">Nama Perusahaan / Mitra</span>
                    <span class="info-value"><?= htmlspecialchars($kegiatan['pihak_2']) ?></span>
                </div>
                <div class="col-md-6 info-item">
                    <span class="info-label">Nama Kegiatan</span>
                    <span class="info-value"><?= htmlspecialchars($kegiatan['nama_pelaksanaan']) ?></span>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="pemberi_evaluasi" class="form-label">Nama Perwakilan Pengisi Evaluasi</label>
                <input type="text" name="pemberi_evaluasi" id="pemberi_evaluasi" class="form-control" placeholder="Contoh: Budi Santoso (HRD Manager)" required>
            </div>

            <div class="mb-3">
                <label for="tanggal_evaluasi" class="form-label">Tanggal Pengisian</label>
                <input type="date" name="tanggal_evaluasi" id="tanggal_evaluasi" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status Implementasi Kegiatan</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="">-- Pilih Status --</option>
                    <?php foreach ($statusImplementasi as $id => $label): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h4 class="mt-4 mb-3 border-bottom pb-2" style="color: var(--dark); font-weight: 700;">PERTANYAAN KUESIONER KEPUASAN KERJA SAMA</h4>

            <!-- Question 1 -->
            <div class="mb-4">
                <label class="form-label d-block fw-bold mb-2">1. Bagaimana penilaian Bapak/Ibu secara keseluruhan terhadap jalannya program kerja sama yang telah dilakukan bersama Fakultas kami? <span class="text-muted fw-normal d-block mt-1" style="font-size: 0.85rem;">(Contoh program: Magang/KP, riset bersama, praktisi mengajar, pengabdian masyarakat, atau rekrutmen).</span></label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q1" id="q1_sangat_puas" value="Sangat Puas" required>
                    <label class="form-check-label" for="q1_sangat_puas">Sangat Puas</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q1" id="q1_puas" value="Puas">
                    <label class="form-check-label" for="q1_puas">Puas</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q1" id="q1_cukup_puas" value="Cukup Puas">
                    <label class="form-check-label" for="q1_cukup_puas">Cukup Puas</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q1" id="q1_kurang_puas" value="Kurang Puas">
                    <label class="form-check-label" for="q1_kurang_puas">Kurang Puas</label>
                </div>
            </div>

            <!-- Question 2 -->
            <div class="mb-4">
                <label class="form-label d-block fw-bold mb-2">2. Bagaimana penilaian Bapak/Ibu mengenai aspek komunikasi, responsivitas, dan pelayanan administrasi dari pihak Fakultas selama proses kerja sama berlangsung?</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q2" id="q2_sangat_baik" value="Sangat Baik" required>
                    <label class="form-check-label" for="q2_sangat_baik">Sangat Baik (Cepat, tanggap, dan komunikatif)</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q2" id="q2_baik" value="Baik">
                    <label class="form-check-label" for="q2_baik">Baik (Responsif dan lancar)</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q2" id="q2_cukup" value="Cukup">
                    <label class="form-check-label" for="q2_cukup">Cukup (Standar namun ada beberapa kendala minor)</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q2" id="q2_kurang" value="Kurang">
                    <label class="form-check-label" for="q2_kurang">Kurang (Lambat merespons atau koordinasi kurang jelas)</label>
                </div>
            </div>

            <!-- Question 3 -->
            <div class="mb-4">
                <label class="form-label d-block fw-bold mb-2">3. Apakah program kerja sama yang telah dijalankan memberikan dampak positif atau nilai tambah yang nyata bagi instansi/perusahaan Bapak/Ibu?</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q3" id="q3_signifikan" value="Ya, memberikan dampak yang signifikan" required>
                    <label class="form-check-label" for="q3_signifikan">Ya, memberikan dampak yang signifikan</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q3" id="q3_cukup" value="Ya, cukup memberikan dampak">
                    <label class="form-check-label" for="q3_cukup">Ya, cukup memberikan dampak</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="q3" id="q3_kurang" value="Kurang memberikan dampak nyata">
                    <label class="form-check-label" for="q3_kurang">Kurang memberikan dampak nyata</label>
                </div>
            </div>

            <!-- Question 4 -->
            <div class="mb-4">
                <label class="form-label d-block fw-bold mb-2" for="q4">4. Saran singkat atau area mana yang paling perlu kami tingkatkan agar kerja sama di masa mendatang bisa berjalan lebih optimal? <span class="text-muted fw-normal d-block mt-1" style="font-size: 0.85rem;">(Contoh: Penyederhanaan birokrasi, penyesuaian waktu program, atau sinkronisasi kebutuhan teknologi/industri).</span></label>
                <textarea name="q4" id="q4" rows="4" class="form-control" placeholder="Tuliskan saran singkat atau area peningkatan..." required></textarea>
            </div>

            <div class="mb-4">
                <label for="bukti" class="form-label">Upload Bukti Pelaksanaan / Pendukung (Optional)</label>
                <input type="file" name="bukti" id="bukti" class="form-control">
                <div class="form-text">Format dokumen atau foto pendukung.</div>
            </div>

            <button type="submit" class="btn btn-submit">
                <i class="bi bi-send me-2"></i> Kirim Evaluasi
            </button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
