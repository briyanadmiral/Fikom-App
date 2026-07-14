<?php
session_start();

// --- SATPAM SESSION DARI BRIDGE ---
// Cek apakah tiket 'mou_admin' sudah ada?
if (!isset($_SESSION['mou_admin']) || $_SESSION['mou_admin'] !== true) {
    // Kalau belum punya tiket, tendang balik ke Bridge
    header("Location: ../mou.php");
    exit;
}

include 'koneksi.php';

$id_pelaksanaan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pelaksanaan <= 0) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h2>ID Kegiatan tidak valid!</h2></div>");
}

// Fetch kegiatan and company data
$query = "
    SELECT p.*, m.pihak_2, m.nama_mou, m.id_mou
    FROM pelaksanaan p 
    JOIN mou m ON p.id_mou = m.id_mou 
    WHERE p.id_pelaksanaan = $id_pelaksanaan AND p.deleted_at IS NULL AND m.deleted_at IS NULL
";
$result = mysqli_query($conn, $query);
$kegiatan = mysqli_fetch_assoc($result);

if (!$kegiatan) {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h2>Data Kegiatan tidak ditemukan atau telah dihapus!</h2></div>");
}

$keteranganList = mysqli_query($conn, "SELECT * FROM keterangan_evaluasi");

$historyQuery = "
    SELECT ee.*, ke.ket_evaluasi 
    FROM evaluasi_eksternal ee
    LEFT JOIN keterangan_evaluasi ke ON ee.id_ket_evaluasi = ke.id_ket_evaluasi
    WHERE ee.id_pelaksanaan = $id_pelaksanaan AND ee.deleted_at IS NULL
    ORDER BY ee.tanggal_evaluasi DESC
";
$historyList = mysqli_query($conn, $historyQuery);

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
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
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

        .btn-back-mou {
            background: rgba(255, 255, 255, 0.4) !important;
            color: var(--dark) !important;
            border: 1px solid var(--border) !important;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-back-mou:hover {
            background: rgba(255, 255, 255, 0.7) !important;
            transform: translateY(-2px);
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
            <p class="text-muted mb-4">Evaluasi kepuasan Anda telah berhasil dikirim. Feedback Anda sangat berharga bagi kami untuk meningkatkan kualitas layanan dan kerja sama.</p>
            <a href="pelaksanaan.php?id=<?= $kegiatan['id_mou'] ?>" class="btn btn-back-mou">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Pelaksanaan
            </a>
        </div>
    <?php else: ?>
        <div class="d-flex justify-content-start mb-3">
            <a href="pelaksanaan.php?id=<?= $kegiatan['id_mou'] ?>" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
        
        <?php if (isset($_GET['deleted_eval'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Sukses!</strong> Evaluasi berhasil dihapus dari tampilan.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error_eval'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal!</strong> <?= htmlspecialchars($_GET['error_eval']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

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
                    <?php while ($row = mysqli_fetch_assoc($keteranganList)): ?>
                        <option value="<?= $row['id_ket_evaluasi'] ?>"><?= htmlspecialchars($row['ket_evaluasi']) ?></option>
                    <?php endwhile; ?>
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
                <label class="form-label" for="bukti">Upload Bukti Pelaksanaan / Pendukung (Optional)</label>
                <input type="file" name="bukti" id="bukti" class="form-control">
                <div class="form-text">Format dokumen atau foto pendukung.</div>
            </div>

            <div class="row g-2">
                <div class="col-md-6">
                    <a href="pelaksanaan.php?id=<?= $kegiatan['id_mou'] ?>" class="btn btn-back-mou">
                        <i class="bi bi-x-circle me-2"></i> Batal
                    </a>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-send me-2"></i> Kirim Evaluasi
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- History Card -->
<div class="card mt-4" style="background: var(--bg-card); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); border: 1px solid var(--border); border-radius: 20px; box-shadow: var(--shadow); max-width: 650px; width: 100%; padding: 30px; margin-top: 30px !important;">
    <h4 class="form-title text-start mb-3" style="font-size: 1.25rem; font-weight: 700; color: var(--dark);"><i class="bi bi-clock-history me-2"></i>Riwayat Evaluasi Pelaksanaan</h4>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th>Pengisi / Tanggal</th>
                    <th>Status</th>
                    <th>Detail Kuesioner</th>
                    <th style="width: 80px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="history-tbody">
                <?php 
                // Reload history query to fetch newly inserted submission dynamically on submission success
                $historyList = mysqli_query($conn, $historyQuery);
                if ($historyList && mysqli_num_rows($historyList) > 0): 
                ?>
                    <?php while ($row = mysqli_fetch_assoc($historyList)): ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($row['pemberi_evaluasi']) ?></strong>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= date('d M Y', strtotime($row['tanggal_evaluasi'])) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['ket_evaluasi'] ?? '-') ?></span>
                            </td>
                            <td>
                                <div style="max-height: 150px; overflow-y: auto; padding-right: 5px; line-height: 1.4;">
                                    <?php if (!empty($row['q1'])): ?>
                                        <div class="mb-1"><strong>Q1:</strong> <span class="text-muted"><?= htmlspecialchars($row['q1']) ?></span></div>
                                        <div class="mb-1"><strong>Q2:</strong> <span class="text-muted"><?= htmlspecialchars($row['q2']) ?></span></div>
                                        <div class="mb-1"><strong>Q3:</strong> <span class="text-muted"><?= htmlspecialchars($row['q3']) ?></span></div>
                                        <div><strong>Q4:</strong> <span class="text-muted"><?= htmlspecialchars($row['q4']) ?></span></div>
                                    <?php else: ?>
                                        <div class="text-muted"><?= nl2br(htmlspecialchars($row['evaluasi'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                 <a href="detail_evaluasi.php?id=<?= $row['id_eval_eksternal'] ?>" class="btn btn-outline-primary btn-sm border-0 me-1" title="Lihat Detail">
                                     <i class="bi bi-eye"></i> Detail
                                 </a>
                                 <a href="hapus_evaluasi.php?id=<?= $row['id_eval_eksternal'] ?>&id_pelaksanaan=<?= $id_pelaksanaan ?>" 
                                    class="btn btn-outline-danger btn-sm border-0" 
                                    onclick="return confirm('Yakin ingin menghapus evaluasi ini? (Catatan: Data akan tetap disimpan sebagai arsip di database, namun tidak akan muncul lagi di sistem)')" 
                                    title="Hapus Evaluasi">
                                     <i class="bi bi-trash"></i> Hapus
                                 </a>
                             </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="no-data-row">
                        <td colspan="4" class="text-center text-muted py-3">Belum ada riwayat evaluasi untuk kegiatan ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function removeRowFromDisplay(button) {
    if (confirm('Yakin ingin menyembunyikan riwayat ini dari tampilan? (Catatan: Data di database tetap aman)')) {
        let tr = button.closest('tr');
        tr.style.transition = 'all 0.5s ease';
        tr.style.opacity = '0';
        tr.style.transform = 'translateX(20px)';
        setTimeout(() => {
            tr.remove();
            let tbody = document.getElementById('history-tbody');
            let rows = tbody.querySelectorAll('tr:not(.no-data-row)');
            if (rows.length === 0) {
                tbody.innerHTML = `
                    <tr class="no-data-row">
                        <td colspan="4" class="text-center text-muted py-3">Belum ada riwayat evaluasi untuk kegiatan ini.</td>
                    </tr>
                `;
            }
        }, 500);
    }
}
</script>

</body>
</html>
