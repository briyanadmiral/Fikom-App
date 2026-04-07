<?php
// mahasiswa/pengajuan.php - Form Pengajuan & Riwayat (Updated - Optional PDF)
require_once '../config/database.php';

startSession();

// Check users session
if (!checkSessionRole(['users'])) {
    header("Location: ../login.php");
    exit;
}

$user_info = getUserInfo();
$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    try {
        // Validate required fields
        $required_fields = ['ruangan_id', 'keperluan', 'tanggal_pinjam', 'jam_mulai', 'jam_selesai'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field $field harus diisi");
            }
        }

        // Check if room is available
        $check_query = "SELECT COUNT(*) as conflicts FROM pengajuan_peminjaman 
                       WHERE ruangan_id = ? AND tanggal_pinjam = ? 
                       AND status IN ('approved', 'pending')
                       AND ((jam_mulai < ? AND jam_selesai > ?) 
                            OR (jam_mulai < ? AND jam_selesai > ?)
                            OR (jam_mulai >= ? AND jam_selesai <= ?))";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([
            $_POST['ruangan_id'], $_POST['tanggal_pinjam'],
            $_POST['jam_selesai'], $_POST['jam_mulai'],
            $_POST['jam_mulai'], $_POST['jam_selesai'],
            $_POST['jam_mulai'], $_POST['jam_selesai']
        ]);
        $conflicts = $check_stmt->fetch()['conflicts'];

        if ($conflicts > 0) {
            throw new Exception("Waktu yang dipilih bentrok dengan pengajuan lain!");
        }

        // Handle file upload (OPTIONAL)
        $surat_filename = null;
        if (isset($_FILES['surat_peminjaman']) && $_FILES['surat_peminjaman']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = pathinfo($_FILES['surat_peminjaman']['name'], PATHINFO_EXTENSION);
            if (strtolower($file_extension) !== 'pdf') {
                throw new Exception("File harus berformat PDF!");
            }

            if ($_FILES['surat_peminjaman']['size'] > 5 * 1024 * 1024) {
                throw new Exception("Ukuran file maksimal 5MB!");
            }

            $surat_filename = 'surat_' . time() . '_' . uniqid() . '.pdf';
            $upload_path = $upload_dir . $surat_filename;

            if (!move_uploaded_file($_FILES['surat_peminjaman']['tmp_name'], $upload_path)) {
                throw new Exception("Gagal upload file!");
            }
        }

        // Insert booking request
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $jumlahPeserta = !empty($_POST['jumlah_peserta']) ? (int)$_POST['jumlah_peserta'] : null;

        if ($userId <= 0) {
            throw new Exception("Sesi user tidak valid. Silakan login kembali.");
        }

        $insert_query = "INSERT INTO pengajuan_peminjaman 
                        (user_id, ruangan_id, keperluan, deskripsi, tanggal_pinjam, jam_mulai, jam_selesai, jumlah_peserta, surat_peminjaman) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insert_query);
        $stmt->execute([
            $userId,
            $_POST['ruangan_id'],
            $_POST['keperluan'],
            $_POST['deskripsi'] ?? null,
            $_POST['tanggal_pinjam'],
            $_POST['jam_mulai'],
            $_POST['jam_selesai'],
            $jumlahPeserta,
            $surat_filename
        ]);

        logActivity($_SESSION['user_id'], 'Submit Booking', "Submitted booking request for room ID: " . $_POST['ruangan_id'], $_POST['ruangan_id']);
        $message = 'Pengajuan berhasil dikirim! Menunggu persetujuan admin.';

    } catch (Exception $e) {
        $error = "Gagal mengirim pengajuan (User ID: " . ($userId ?? 'null') . "): " . $e->getMessage();
    }
}

// Get rooms list
$rooms = [];
try {
    $stmt = $db->query("SELECT * FROM ruangan WHERE status = 'active' ORDER BY nama_ruangan");
    $rooms = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading rooms: ' . $e->getMessage();
}

// Get user's booking history
$bookings = [];
try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $db->prepare("SELECT pp.*, r.nama_ruangan FROM pengajuan_peminjaman pp JOIN ruangan r ON pp.ruangan_id = r.id WHERE pp.user_id = ? ORDER BY pp.created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
        $bookings = $stmt->fetchAll();
    }
} catch (Exception $e) {
    $error = 'Error loading bookings: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Peminjaman - FIKOM</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="student-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon"><i class="bi bi-person-circle"></i></div>
                    <span>User Panel</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
                
                <a href="lihat_jadwal.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'lihat_jadwal.php') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3 me-2"></i> Lihat Jadwal
                </a>

                <a href="pengajuan.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'pengajuan.php') ? 'active' : '' ?>">
                    <i class="bi bi-pencil-square me-2"></i> Pengajuan & Riwayat
                </a>

                <hr class="mx-3 opacity-25">

                <a href="../logout.php" class="nav-item logout">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h1>Pengajuan Peminjaman Ruangan</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user_info['nama'] ?? 'Pengguna'); ?></span>
                    <div class="user-avatar">👤</div>
                </div>
            </header>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('new-booking')">➕ Pengajuan Baru</button>
                <button class="tab-btn" onclick="showTab('history')">📋 Riwayat (<?php echo count($bookings); ?>)</button>
            </div>

            <!-- Tab: Pengajuan Baru -->
            <div id="new-booking-tab" class="tab-content active">
                <div class="section-card">
                    <h2>📝 Form Pengajuan Peminjaman</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Ruangan: <span class="required">*</span></label>
                                <select name="ruangan_id" required>
                                    <option value="">Pilih Ruangan</option>
                                    <?php foreach ($rooms as $room): ?>
                                        <option value="<?php echo $room['id']; ?>">
                                            <?php echo htmlspecialchars($room['kode_ruangan'] . ' - ' . $room['nama_ruangan'] . ' (' . $room['kapasitas'] . ' orang)'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Keperluan: <span class="required">*</span></label>
                                <input type="text" name="keperluan" required placeholder="Contoh: Rapat OSIS">
                            </div>

                            <div class="form-group">
                                <label>Tanggal: <span class="required">*</span></label>
                                <input type="date" name="tanggal_pinjam" required min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label>Jumlah Peserta:</label>
                                <input type="number" name="jumlah_peserta" placeholder="Perkiraan jumlah peserta">
                            </div>

                            <div class="form-group">
                                <label>Jam Mulai: <span class="required">*</span></label>
                                <select name="jam_mulai" required>
                                    <?php for ($i = 6; $i <= 20; $i++): ?>
                                        <option value="<?php echo sprintf('%02d:00', $i); ?>"><?php echo sprintf('%02d:00', $i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Jam Selesai: <span class="required">*</span></label>
                                <select name="jam_selesai" required>
                                    <?php for ($i = 7; $i <= 21; $i++): ?>
                                        <option value="<?php echo sprintf('%02d:00', $i); ?>"><?php echo sprintf('%02d:00', $i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi Kegiatan:</label>
                            <textarea name="deskripsi" rows="3" placeholder="Jelaskan detail kegiatan yang akan dilakukan..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Upload Surat Peminjaman (PDF) - Opsional:</label>
                            <input type="file" name="surat_peminjaman" accept=".pdf">
                            <small class="form-help">Format: PDF, Maksimal 5MB. Upload surat untuk mempercepat persetujuan.</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="submit_booking" class="btn btn-primary">📤 Kirim Pengajuan</button>
                            <button type="button" class="btn btn-secondary" onclick="checkSchedule()">📅 Cek Jadwal Ruangan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tab: Riwayat -->
            <div id="history-tab" class="tab-content">
                <div class="section-card">
                    <h2>📋 Riwayat Pengajuan</h2>
                    
                    <?php if (empty($bookings)): ?>
                        <div class="empty-state">
                            <p>📝 Belum ada pengajuan yang dibuat</p>
                            <button class="btn btn-primary" onclick="showTab('new-booking')">Buat Pengajuan Baru</button>
                        </div>
                    <?php else: ?>
                        <div class="bookings-list">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card">
                                    <div class="booking-header">
                                        <h3><?php echo htmlspecialchars($booking['keperluan']); ?></h3>
                                        <span class="status-badge <?php echo $booking['status']; ?>">
                                            <?php 
                                            echo $booking['status'] === 'pending' ? '⏳ Pending' : 
                                                 ($booking['status'] === 'approved' ? '✅ Disetujui' : '❌ Ditolak');
                                            ?>
                                        </span>
                                    </div>
                                    <div class="booking-details">
                                        <div class="detail-grid">
                                            <div><strong>Ruangan:</strong> <?php echo htmlspecialchars($booking['nama_ruangan']); ?></div>
                                            <div><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($booking['tanggal_pinjam'])); ?></div>
                                            <div><strong>Waktu:</strong> <?php echo substr($booking['jam_mulai'], 0, 5) . ' - ' . substr($booking['jam_selesai'], 0, 5); ?></div>
                                            <div><strong>Peserta:</strong> <?php echo $booking['jumlah_peserta'] ?? '-'; ?> orang</div>
                                        </div>
                                        <?php if ($booking['deskripsi']): ?>
                                            <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($booking['deskripsi']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($booking['keterangan_admin']): ?>
                                            <p><strong>Keterangan Admin:</strong> <?php echo htmlspecialchars($booking['keterangan_admin']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($booking['surat_peminjaman']): ?>
                                            <p><strong>Surat:</strong> <a href="../assets/uploads/<?php echo htmlspecialchars($booking['surat_peminjaman']); ?>" target="_blank" class="file-link">📄 Lihat Surat</a></p>
                                        <?php else: ?>
                                            <p><strong>Surat:</strong> <span class="text-muted">Tidak ada file</span></p>
                                        <?php endif; ?>
                                        <small class="booking-date">Diajukan: <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        function checkSchedule() {
            const roomSelect = document.querySelector('[name="ruangan_id"]');
            const dateInput = document.querySelector('[name="tanggal_pinjam"]');
            
            if (!roomSelect.value || !dateInput.value) {
                alert('Pilih ruangan dan tanggal terlebih dahulu');
                return;
            }
            
            window.open(`../index.php?room=${roomSelect.value}&date=${dateInput.value}`, '_blank');
        }

        // Auto-adjust end time when start time changes
        document.querySelector('[name="jam_mulai"]').addEventListener('change', function() {
            const startHour = parseInt(this.value.split(':')[0]);
            const endSelect = document.querySelector('[name="jam_selesai"]');
            const minEndHour = startHour + 1;
            
            // Update end time options
            for (let option of endSelect.options) {
                const optionHour = parseInt(option.value.split(':')[0]);
                option.disabled = optionHour <= startHour;
            }
            
            // Set minimum valid end time
            if (parseInt(endSelect.value.split(':')[0]) <= startHour) {
                endSelect.value = String(minEndHour).padStart(2, '0') + ':00';
            }
        });
    </script>
</body>
</html>