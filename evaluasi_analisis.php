<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
if ($role !== 'dosen' && $role !== 'superadmin') {
    header('Location: index.php');
    exit;
}

// Database Connection
if (!isset($_ENV['DB_HOST']) && file_exists(__DIR__ . '/.env')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();
    }
}
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$user = $_ENV['DB_USERNAME'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? '';
$db   = $_ENV['DB_DATABASE_MOU'] ?? 'fike8938_fikom_mou';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database MOU gagal: " . mysqli_connect_error());
}

// helper: check if column exists
function columnExists($conn, $table, $column) {
    $table = mysqli_real_escape_string($conn, $table);
    $column = mysqli_real_escape_string($conn, $column);
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($res && mysqli_num_rows($res) > 0);
}

// --- STATISTIK KUESIONER ---
$q1_sangat_puas = 0;
$q1_puas = 0;
$q1_cukup_puas = 0;
$q1_kurang_puas = 0;

$q1_sangat_puas_respondents = [];
$q1_puas_respondents = [];
$q1_cukup_puas_respondents = [];
$q1_kurang_puas_respondents = [];

$q2_sangat_baik = 0;
$q2_baik = 0;
$q2_cukup = 0;
$q2_kurang = 0;

$q2_sangat_baik_respondents = [];
$q2_baik_respondents = [];
$q2_cukup_respondents = [];
$q2_kurang_respondents = [];

$q3_signifikan = 0;
$q3_cukup_dampak = 0;
$q3_kurang_dampak = 0;

$q3_signifikan_respondents = [];
$q3_cukup_dampak_respondents = [];
$q3_kurang_dampak_respondents = [];

$saran_list = [];

if (columnExists($conn, 'evaluasi_eksternal', 'q1') && columnExists($conn, 'evaluasi_eksternal', 'q2') && columnExists($conn, 'evaluasi_eksternal', 'q3') && columnExists($conn, 'evaluasi_eksternal', 'q4')) {
    $res_eval = mysqli_query($conn, "
        SELECT ee.q1, ee.q2, ee.q3, ee.q4, ee.pemberi_evaluasi, ee.tanggal_evaluasi, m.pihak_2, p.nama_pelaksanaan
        FROM evaluasi_eksternal ee
        JOIN pelaksanaan p ON ee.id_pelaksanaan = p.id_pelaksanaan
        JOIN mou m ON p.id_mou = m.id_mou
        WHERE ee.deleted_at IS NULL
    ");

    if ($res_eval) {
        while ($row = mysqli_fetch_assoc($res_eval)) {
            $respondent_info = [
                'pihak_2' => htmlspecialchars($row['pihak_2']),
                'kegiatan' => htmlspecialchars($row['nama_pelaksanaan']),
                'pemberi' => htmlspecialchars($row['pemberi_evaluasi']),
                'tanggal' => date('d F Y', strtotime($row['tanggal_evaluasi']))
            ];

            // Q1 counting
            $ans1 = trim($row['q1'] ?? '');
            if (stripos($ans1, 'Sangat Puas') !== false) {
                $q1_sangat_puas++;
                $q1_sangat_puas_respondents[] = $respondent_info;
            } elseif (stripos($ans1, 'Cukup Puas') !== false) {
                $q1_cukup_puas++;
                $q1_cukup_puas_respondents[] = $respondent_info;
            } elseif (stripos($ans1, 'Kurang Puas') !== false) {
                $q1_kurang_puas++;
                $q1_kurang_puas_respondents[] = $respondent_info;
            } elseif (stripos($ans1, 'Puas') !== false) {
                $q1_puas++;
                $q1_puas_respondents[] = $respondent_info;
            }

            // Q2 counting
            $ans2 = trim($row['q2'] ?? '');
            if (stripos($ans2, 'Sangat Baik') !== false) {
                $q2_sangat_baik++;
                $q2_sangat_baik_respondents[] = $respondent_info;
            } elseif (stripos($ans2, 'Cukup') !== false) {
                $q2_cukup++;
                $q2_cukup_respondents[] = $respondent_info;
            } elseif (stripos($ans2, 'Kurang') !== false) {
                $q2_kurang++;
                $q2_kurang_respondents[] = $respondent_info;
            } elseif (stripos($ans2, 'Baik') !== false) {
                $q2_baik++;
                $q2_baik_respondents[] = $respondent_info;
            }

            // Q3 counting
            $ans3 = trim($row['q3'] ?? '');
            if (stripos($ans3, 'signifikan') !== false) {
                $q3_signifikan++;
                $q3_signifikan_respondents[] = $respondent_info;
            } elseif (stripos($ans3, 'cukup') !== false) {
                $q3_cukup_dampak++;
                $q3_cukup_dampak_respondents[] = $respondent_info;
            } elseif (stripos($ans3, 'Kurang') !== false || stripos($ans3, 'tidak') !== false) {
                $q3_kurang_dampak++;
                $q3_kurang_dampak_respondents[] = $respondent_info;
            }

            // Q4 listing (suggestions)
            if (!empty($row['q4'])) {
                $saran_list[] = [
                    'pihak_2' => $row['pihak_2'],
                    'kegiatan' => $row['nama_pelaksanaan'],
                    'pemberi' => $row['pemberi_evaluasi'],
                    'tanggal' => $row['tanggal_evaluasi'],
                    'saran' => $row['q4']
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Kuesioner Kepuasan - FIKOM APP</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #8a9ccc;
            --primary-soft: rgba(138, 156, 204, 0.15);
            --dark: #3a4252;
            --text-main: #333333;
            --text-muted: #5e6677;
            --bg-body: #e4e7ec;
            --bg-card: rgba(255, 255, 255, 0.45);
            --border: rgba(255, 255, 255, 0.7);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            --glass-blur: blur(16px);
            --success: #10b981;
            --danger: #ef4444;
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

        .dashboard-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .glass-panel {
            background: var(--bg-card);
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: 16px;
        }

        /* Top Navigation / Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .header .logo img {
            height: 40px;
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .btn-back {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid var(--border);
        }

        .btn-back:hover {
            color: var(--dark);
            background: rgba(255, 255, 255, 0.7);
            transform: translateX(-3px);
        }

        /* Title block */
        .title-block {
            margin-bottom: 2rem;
        }

        .title-block h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }

        .last-border-none:last-child {
            border-bottom: none !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Clickable rating bar trigger style */
        .respondent-trigger {
            padding: 8px 12px;
            margin: -4px -8px 12px -8px;
            border-radius: 10px;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .respondent-trigger:hover {
            background: rgba(255, 255, 255, 0.8);
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }

        /* Modal styling */
        .modal-content {
            background: rgba(240, 242, 245, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 16px;
        }
        .modal-header {
            border-bottom: 1px solid var(--border);
        }
        .modal-footer {
            border-top: 1px solid var(--border);
        }
        .table-modal th {
            background: rgba(255, 255, 255, 0.4) !important;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .table-modal td {
            background: transparent !important;
        }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <!-- Header -->
        <header class="header glass-panel">
            <div class="logo">
                <img src="assets/img/fikom.png" alt="Logo Fikom">
            </div>
            <div>
                <a href="evaluasi_kepuasan.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Monitoring Evaluasi
                </a>
            </div>
        </header>

        <!-- Title Block -->
        <div class="title-block">
            <h1>Hasil Analisis Kuesioner Kepuasan Kerja Sama</h1>
            <p class="text-muted mb-0">Halaman visualisasi hasil feedback kuesioner dari mitra eksternal FIKOM UNIKA. Klik opsi jawaban di bawah untuk melihat siapa saja pengisi kuesioner tersebut.</p>
        </div>

        <!-- Kuesioner Satisfaction Dashboard -->
        <div class="card glass-panel border-0 p-4 mb-4">
            <div class="row g-4">
                <!-- Pertanyaan 1 & 2 & 3 Stats -->
                <div class="col-lg-7">
                    <div class="row g-3">
                        <!-- Q1 -->
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;">
                                1. Bagaimana penilaian Bapak/Ibu secara keseluruhan terhadap jalannya program kerja sama yang telah dilakukan bersama Fakultas kami?
                            </h6>
                            <div class="p-3 rounded bg-white bg-opacity-50 border border-white">
                                <?php
                                $total_q1 = $q1_sangat_puas + $q1_puas + $q1_cukup_puas + $q1_kurang_puas;
                                $pct_q1_sp = $total_q1 > 0 ? round(($q1_sangat_puas / $total_q1) * 100) : 0;
                                $pct_q1_p = $total_q1 > 0 ? round(($q1_puas / $total_q1) * 100) : 0;
                                $pct_q1_cp = $total_q1 > 0 ? round(($q1_cukup_puas / $total_q1) * 100) : 0;
                                $pct_q1_kp = $total_q1 > 0 ? round(($q1_kurang_puas / $total_q1) * 100) : 0;
                                ?>
                                <div class="respondent-trigger" onclick="showRespondents('q1', 'sangat_puas', 'Pertanyaan 1 - Sangat Puas')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Sangat Puas <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q1_sangat_puas ?> (<?= $pct_q1_sp ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct_q1_sp ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" onclick="showRespondents('q1', 'puas', 'Pertanyaan 1 - Puas')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Puas <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q1_puas ?> (<?= $pct_q1_p ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct_q1_p ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" onclick="showRespondents('q1', 'cukup_puas', 'Pertanyaan 1 - Cukup Puas')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Cukup Puas <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q1_cukup_puas ?> (<?= $pct_q1_cp ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct_q1_cp ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" style="margin-bottom:0;" onclick="showRespondents('q1', 'kurang_puas', 'Pertanyaan 1 - Kurang Puas')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Kurang Puas <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q1_kurang_puas ?> (<?= $pct_q1_kp ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pct_q1_kp ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Q2 -->
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;">
                                2. Bagaimana penilaian Bapak/Ibu mengenai aspek komunikasi, responsivitas, dan pelayanan administrasi dari pihak Fakultas selama proses kerja sama berlangsung?
                            </h6>
                            <div class="p-3 rounded bg-white bg-opacity-50 border border-white">
                                <?php
                                $total_q2 = $q2_sangat_baik + $q2_baik + $q2_cukup + $q2_kurang;
                                $pct_q2_sb = $total_q2 > 0 ? round(($q2_sangat_baik / $total_q2) * 100) : 0;
                                $pct_q2_b = $total_q2 > 0 ? round(($q2_baik / $total_q2) * 100) : 0;
                                $pct_q2_c = $total_q2 > 0 ? round(($q2_cukup / $total_q2) * 100) : 0;
                                $pct_q2_k = $total_q2 > 0 ? round(($q2_kurang / $total_q2) * 100) : 0;
                                ?>
                                <div class="respondent-trigger" onclick="showRespondents('q2', 'sangat_baik', 'Pertanyaan 2 - Sangat Baik')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Sangat Baik (Cepat, tanggap, dan komunikatif) <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q2_sangat_baik ?> (<?= $pct_q2_sb ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct_q2_sb ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" onclick="showRespondents('q2', 'baik', 'Pertanyaan 2 - Baik')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Baik (Responsif dan lancar) <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q2_baik ?> (<?= $pct_q2_b ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct_q2_b ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" onclick="showRespondents('q2', 'cukup', 'Pertanyaan 2 - Cukup')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Cukup (Standar namun ada beberapa kendala minor) <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q2_cukup ?> (<?= $pct_q2_c ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct_q2_c ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" style="margin-bottom:0;" onclick="showRespondents('q2', 'kurang', 'Pertanyaan 2 - Kurang')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Kurang (Lambat merespons atau koordinasi kurang jelas) <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q2_kurang ?> (<?= $pct_q2_k ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pct_q2_k ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Q3 -->
                        <div class="col-12">
                            <h6 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;">
                                3. Apakah program kerja sama yang telah dijalankan memberikan dampak positif atau nilai tambah yang nyata bagi instansi/perusahaan Bapak/Ibu?
                            </h6>
                            <div class="p-3 rounded bg-white bg-opacity-50 border border-white">
                                <?php
                                $total_q3 = $q3_signifikan + $q3_cukup_dampak + $q3_kurang_dampak;
                                $pct_q3_sig = $total_q3 > 0 ? round(($q3_signifikan / $total_q3) * 100) : 0;
                                $pct_q3_cuk = $total_q3 > 0 ? round(($q3_cukup_dampak / $total_q3) * 100) : 0;
                                $pct_q3_kur = $total_q3 > 0 ? round(($q3_kurang_dampak / $total_q3) * 100) : 0;
                                ?>
                                <div class="respondent-trigger" onclick="showRespondents('q3', 'signifikan', 'Pertanyaan 3 - Dampak Signifikan')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Ya, memberikan dampak yang signifikan <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q3_signifikan ?> (<?= $pct_q3_sig ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pct_q3_sig ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" onclick="showRespondents('q3', 'cukup_dampak', 'Pertanyaan 3 - Cukup Memberikan Dampak')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Ya, cukup memberikan dampak <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q3_cukup_dampak ?> (<?= $pct_q3_cuk ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct_q3_cuk ?>%"></div>
                                    </div>
                                </div>
                                <div class="respondent-trigger" style="margin-bottom:0;" onclick="showRespondents('q3', 'kurang_dampak', 'Pertanyaan 3 - Kurang Memberikan Dampak')">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.85rem;">
                                        <span>Kurang memberikan dampak nyata <i class="fas fa-eye text-primary ms-1" style="font-size: 0.75rem; opacity: 0.7;"></i></span>
                                        <span class="fw-bold"><?= $q3_kurang_dampak ?> (<?= $pct_q3_kur ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pct_q3_kur ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 2: Q4 Saran list -->
                <div class="col-lg-5">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 0.95rem;">
                        4. Saran Singkat atau Area Peningkatan (Q4)
                    </h6>
                    <div class="p-3 rounded bg-white bg-opacity-50 border border-white overflow-auto" style="max-height: 575px;">
                        <?php if (count($saran_list) > 0): ?>
                            <?php foreach ($saran_list as $saran): ?>
                                <div class="mb-3 pb-3 border-bottom last-border-none">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-dark" style="font-size: 0.85rem;">
                                            <?= htmlspecialchars($saran['pihak_2']) ?>
                                        </strong>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <?= date('d M Y', strtotime($saran['tanggal'])) ?>
                                        </small>
                                    </div>
                                    <div class="text-muted mb-2" style="font-size: 0.75rem;">
                                        Kegiatan: <span class="text-secondary"><?= htmlspecialchars($saran['kegiatan']) ?></span> 
                                        | Oleh: <span class="text-secondary"><?= htmlspecialchars($saran['pemberi']) ?></span>
                                    </div>
                                    <div class="p-2 rounded bg-white bg-opacity-70 border" style="font-size: 0.85rem; font-style: italic; color: #495057;">
                                        "<?= nl2br(htmlspecialchars($saran['saran'])) ?>"
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-chat-left-text fs-2 d-block mb-2"></i>
                                Belum ada saran yang masuk.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Respondents List Modal -->
    <div class="modal fade" id="respondentsModal" tabindex="-1" aria-labelledby="respondentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="respondentsModalLabel" style="font-weight:700; color:var(--dark);">
                        <i class="fas fa-users me-2 text-primary"></i>Daftar Pengisi Evaluasi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.8rem; text-transform:uppercase; font-weight:600; letter-spacing:0.5px;">Kategori Jawaban</span>
                        <strong id="modal-category" style="font-size:1.1rem; color:var(--dark);">-</strong>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-modal" style="font-size:0.9rem;">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Mitra / Perusahaan</th>
                                    <th>Kegiatan Kerja Sama</th>
                                    <th>Perwakilan Pengisi</th>
                                    <th style="width: 150px;">Tanggal Mengisi</th>
                                </tr>
                            </thead>
                            <tbody id="modal-respondents-body">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background: rgba(0,0,0,0.05); border: 1px solid var(--border); color: var(--text-muted); font-weight: 600; border-radius: 8px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const respondentsData = {
            q1: {
                sangat_puas: <?= json_encode($q1_sangat_puas_respondents) ?>,
                puas: <?= json_encode($q1_puas_respondents) ?>,
                cukup_puas: <?= json_encode($q1_cukup_puas_respondents) ?>,
                kurang_puas: <?= json_encode($q1_kurang_puas_respondents) ?>
            },
            q2: {
                sangat_baik: <?= json_encode($q2_sangat_baik_respondents) ?>,
                baik: <?= json_encode($q2_baik_respondents) ?>,
                cukup: <?= json_encode($q2_cukup_respondents) ?>,
                kurang: <?= json_encode($q2_kurang_respondents) ?>
            },
            q3: {
                signifikan: <?= json_encode($q3_signifikan_respondents) ?>,
                cukup_dampak: <?= json_encode($q3_cukup_dampak_respondents) ?>,
                kurang_dampak: <?= json_encode($q3_kurang_dampak_respondents) ?>
            }
        };

        function showRespondents(questionKey, optionKey, titleLabel) {
            document.getElementById('modal-category').innerText = titleLabel;
            const tableBody = document.getElementById('modal-respondents-body');
            tableBody.innerHTML = '';

            const list = respondentsData[questionKey][optionKey] || [];

            if (list.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Belum ada mitra yang memilih jawaban ini.</td></tr>';
            } else {
                list.forEach((item, index) => {
                    tableBody.innerHTML += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td><strong style="color: var(--dark);">${item.pihak_2}</strong></td>
                            <td>${item.kegiatan}</td>
                            <td><span class="text-muted">${item.pemberi}</span></td>
                            <td>${item.tanggal}</td>
                        </tr>
                    `;
                });
            }

            const respondentsModal = new bootstrap.Modal(document.getElementById('respondentsModal'));
            respondentsModal.show();
        }
    </script>
</body>
</html>
