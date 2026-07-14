<?php
// admin/kelola-ruangan.php - Kelola Ruangan & Approve Pengajuan
require_once '../config/database.php';

startSession();

// Check admin session
if (!checkSessionRole(['admin'])) {
    header("Location: ../login.php");
    exit;
}

$user_info = getUserInfo();
$database = new Database();
$db = $database->getConnection();

// Handle actions
$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$db) {
        $error = 'Koneksi database tidak tersedia.';
    } else {
        if ($action === 'add_room') {
            try {
                // ... (Logika 'add_room' Anda) ...
                $kode_ruangan = substr($_POST['nama_ruangan'], 0, 20);
                $check_stmt = $db->prepare("SELECT COUNT(*) FROM ruangan WHERE kode_ruangan = ?");
                $check_stmt->execute([$kode_ruangan]);
                if ($check_stmt->fetchColumn() > 0) {
                    $kode_ruangan = substr($_POST['nama_ruangan'], 0, 15) . '-' . rand(100, 999);
                }

                $stmt = $db->prepare("INSERT INTO ruangan (kode_ruangan, nama_ruangan, kapasitas, lokasi, deskripsi) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $kode_ruangan,
                    $_POST['nama_ruangan'],
                    $_POST['kapasitas'],
                    $_POST['lokasi'],
                    $_POST['deskripsi']
                ]);
                $room_id = $db->lastInsertId();
                if (!empty($_POST['facilities'])) {
                    $facilities = explode(',', $_POST['facilities']);
                    $stmt = $db->prepare("INSERT INTO fasilitas_ruangan (ruangan_id, nama_fasilitas) VALUES (?, ?)");
                    foreach ($facilities as $facility) {
                        $stmt->execute([$room_id, trim($facility)]);
                    }
                }
                logActivity($_SESSION['user_id'], 'Add Room', "Added room: " . $_POST['nama_ruangan'], $room_id);
                $message = 'Ruangan berhasil ditambahkan!';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
        
        elseif ($action === 'edit_room') {
            try {
                // ... (Logika 'edit_room' Anda) ...
                $room_id = $_POST['room_id'];
                $nama = $_POST['nama_ruangan'];
                $kapasitas = $_POST['kapasitas'];
                $lokasi = $_POST['lokasi'];
                $deskripsi = $_POST['deskripsi'];
                $facilities_string = $_POST['facilities'];

                $kode_ruangan = substr($nama, 0, 20);
                $check_stmt = $db->prepare("SELECT COUNT(*) FROM ruangan WHERE kode_ruangan = ? AND id != ?");
                $check_stmt->execute([$kode_ruangan, $room_id]);
                if ($check_stmt->fetchColumn() > 0) {
                    $kode_ruangan = substr($nama, 0, 15) . '-' . rand(100, 999);
                }

                $stmt = $db->prepare("UPDATE ruangan SET 
                                        kode_ruangan = ?, nama_ruangan = ?, kapasitas = ?, 
                                        lokasi = ?, deskripsi = ?
                                      WHERE id = ?");
                $stmt->execute([$kode_ruangan, $nama, $kapasitas, $lokasi, $deskripsi, $room_id]);

                $stmt_del = $db->prepare("DELETE FROM fasilitas_ruangan WHERE ruangan_id = ?");
                $stmt_del->execute([$room_id]);

                if (!empty($facilities_string)) {
                    $facilities = explode(',', $facilities_string);
                    $stmt_ins = $db->prepare("INSERT INTO fasilitas_ruangan (ruangan_id, nama_fasilitas) VALUES (?, ?)");
                    foreach ($facilities as $facility) {
                        if (!empty(trim($facility))) {
                            $stmt_ins->execute([$room_id, trim($facility)]);
                        }
                    }
                }
                logActivity($_SESSION['user_id'], 'Edit Room', "Edited room: " . $nama, $room_id);
                $message = 'Ruangan berhasil diperbarui!';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
        
        elseif ($action === 'approve_booking') {
            try {
                // ... (Logika 'approve_booking' Anda) ...
                $booking_id = $_POST['booking_id'];
                $status = $_POST['status'];
                $keterangan = $_POST['keterangan'] ?? '';
                $stmt = $db->prepare("UPDATE pengajuan_peminjaman SET status = ?, keterangan_admin = ?, approved_by = ?, approved_at = NOW(), updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $keterangan, $_SESSION['user_id'], $booking_id]);
                logActivity($_SESSION['user_id'], 'Approve Booking', "Booking ID $booking_id: $status", null);
                $message = 'Status pengajuan berhasil diupdate!';
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
}

// Get rooms
$rooms = [];
try {
    if ($db) {
        // Query untuk mengambil ruangan + fasilitas
        $stmt = $db->query("SELECT r.*, 
                                COUNT(fr.id) as facility_count,
                                GROUP_CONCAT(fr.nama_fasilitas SEPARATOR ', ') as facilities_string
                             FROM ruangan r 
                             LEFT JOIN fasilitas_ruangan fr ON r.id = fr.ruangan_id 
                             WHERE r.status = 'active' 
                             GROUP BY r.id 
                             ORDER BY r.nama_ruangan");
        $rooms = $stmt->fetchAll();
    } else {
        $error = 'Koneksi database tidak tersedia.';
    }
} catch (Exception $e) {
    $error = 'Error loading rooms: ' . $e->getMessage();
}

// Get pending bookings
$pending_bookings = [];
try {
    if ($db) {
        // Query untuk mengambil pengajuan pending
        $stmt = $db->query("SELECT pp.*, u.nama, u.email, r.nama_ruangan FROM pengajuan_peminjaman pp JOIN users u ON pp.user_id = u.id JOIN ruangan r ON pp.ruangan_id = r.id WHERE pp.status = 'pending' ORDER BY pp.updated_at DESC, pp.created_at DESC");
        $pending_bookings = $stmt->fetchAll();
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
    <title>Kelola Ruangan - Admin FIKOM</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); padding-top: 60px; }
        .modal-content { background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 700px; border-radius: 8px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ddd; }
        .modal-header h2 { margin: 0; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover, .close-btn:focus { color: black; text-decoration: none; }
        
        .rooms-table-wrapper {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.7);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
            overflow-x: auto;
            margin-top: 1rem;
        }
        .rooms-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.95rem;
        }
        .rooms-table th {
            background: rgba(255, 255, 255, 0.5);
            color: #333;
            font-weight: 600;
            padding: 15px;
            border-bottom: 2px solid rgba(255,255,255,0.7);
        }
        .rooms-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.3);
            color: #333;
            vertical-align: middle;
        }
        .rooms-table tr:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .rooms-table .actions-cell {
            white-space: nowrap;
        }
        .facility-tag {
            display: inline-block;
            background: rgba(138, 156, 204, 0.2);
            color: #3b4e78;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon"><i class="bi bi-building-gear"></i></div>
                    <span>Ruang FIKOM</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
                
                <a href="kelola-ruangan.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'kelola-ruangan.php') ? 'active' : '' ?>">
                    <i class="bi bi-building me-2"></i> Kelola Ruangan
                </a>
                
                <a href="lihat_jadwal.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'lihat_jadwal.php') ? 'active' : '' ?>">
                    <i class="bi bi-calendar3 me-2"></i> Lihat Jadwal
                </a>
                
                <a href="riwayat.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'riwayat.php') ? 'active' : '' ?>">
                    <i class="bi bi-clock-history me-2"></i> Riwayat Pengajuan
                </a>
                
                <a href="laporan.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) == 'laporan.php') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Laporan Peminjaman
                </a>

                <hr class="mx-3 opacity-25">

                <a href="../logout.php" class="nav-item logout">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Kelola Ruangan & Pengajuan</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user_info['nama'] ?? 'Admin'); ?></span>
                    <div class="user-avatar">👨‍💼</div>
                </div>
            </header>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="tabs">
                <button class="tab-btn active" onclick="showTab('rooms')">🏛️ Kelola Ruangan</button>
                <button class="tab-btn" onclick="showTab('approvals')">✅ Approve Pengajuan (<?php echo count($pending_bookings); ?>)</button>
            </div>

            <div id="rooms-tab" class="tab-content active">
                <div class="section-card">
                    <div class="section-header">
                        <h2>➕ Tambah Ruangan Baru</h2>
                        <button class="btn btn-primary" onclick="toggleForm()">Tambah Ruangan</button>
                    </div>
                    <form id="add-room-form" method="POST" action="?action=add_room" style="display: none;">
                        <div class="form-grid">
                            <div class="form-group"><label>Nama Ruangan:</label><input type="text" name="nama_ruangan" required placeholder="Ruangan 5.4"></div>
                            <div class="form-group"><label>Kapasitas:</label><input type="number" name="kapasitas" required placeholder="40"></div>
                            <div class="form-group"><label>Lokasi:</label><input type="text" name="lokasi" placeholder="Lantai 5 Gedung A"></div>
                        </div>
                        <div class="form-group"><label>Deskripsi:</label><textarea name="deskripsi" rows="3" placeholder="Deskripsi ruangan..."></textarea></div>
                        <div class="form-group"><label>Fasilitas (pisahkan dengan koma):</label><input type="text" name="facilities" placeholder="Proyektor, AC, Whiteboard"></div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Simpan Ruangan</button>
                            <button type="button" class="btn btn-secondary" onclick="toggleForm()">Batal</button>
                        </div>
                    </form>
                </div>

                <div class="section-card">
                    <h2>📋 Daftar Ruangan</h2>
                    <div class="rooms-table-wrapper">
                        <table class="rooms-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Ruangan</th>
                                    <th>Kapasitas</th>
                                    <th>Lokasi</th>
                                    <th>Fasilitas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($rooms as $room): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($room['nama_ruangan']); ?></strong><br>
                                            <span style="font-size: 0.8rem; color: #666;"><?php echo htmlspecialchars($room['kode_ruangan']); ?></span>
                                        </td>
                                        <td><?php echo $room['kapasitas']; ?> orang</td>
                                        <td><?php echo htmlspecialchars($room['lokasi'] ?? '-'); ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($room['facilities_string'])) {
                                                $facs = explode(',', $room['facilities_string']);
                                                foreach ($facs as $fac) {
                                                    if (!empty(trim($fac))) {
                                                        echo '<span class="facility-tag">' . htmlspecialchars(trim($fac)) . '</span>';
                                                    }
                                                }
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="actions-cell">
                                            <button class="btn btn-small btn-info" onclick="viewSchedule(<?php echo $room['id']; ?>)">📅 Jadwal</button>
                                            <button class="btn btn-small btn-warning" 
                                                onclick="editRoom(
                                                    <?php echo $room['id']; ?>,
                                                    '<?php echo htmlspecialchars($room['kode_ruangan'], ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($room['nama_ruangan'], ENT_QUOTES); ?>',
                                                    '<?php echo $room['kapasitas']; ?>',
                                                    '<?php echo htmlspecialchars($room['lokasi'] ?? '', ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($room['deskripsi'] ?? '', ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars($room['facilities_string'] ?? '', ENT_QUOTES); ?>'
                                                )">✏️ Edit</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="approvals-tab" class="tab-content">
                <div class="section-card">
                    <h2>⏳ Pengajuan Menunggu Persetujuan</h2>
                    
                    <?php if (empty($pending_bookings)): ?>
                        <div class="empty-state">
                            <p>✅ Tidak ada pengajuan yang menunggu persetujuan</p>
                        </div>
                    <?php else: ?>
                        <div class="bookings-list">
                            <?php foreach ($pending_bookings as $booking): ?>
                                <div class="booking-card">
                                    <div class="booking-header">
                                        <h3><?php echo htmlspecialchars($booking['keperluan']); ?></h3>
                                        <span class="status-badge pending">Pending</span>
                                    </div>
                                    <div class="booking-details">
                                        <div class="detail-grid">
                                            <div><strong>Pemohon:</strong> <?php echo htmlspecialchars($booking['nama']); ?></div>
                                            <div><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></div>
                                            <div><strong>WhatsApp:</strong> <?php echo !empty($booking['no_wa']) ? htmlspecialchars($booking['no_wa']) : '-'; ?></div>
                                            <div><strong>Ruangan:</strong> <?php echo htmlspecialchars($booking['nama_ruangan']); ?></div>
                                            <div><strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($booking['tanggal_pinjam'])); ?></div>
                                            <div><strong>Waktu:</strong> <?php echo substr($booking['jam_mulai'], 0, 5) . ' - ' . substr($booking['jam_selesai'], 0, 5); ?></div>
                                            <div><strong>Peserta:</strong> <?php echo $booking['jumlah_peserta'] ?? '-'; ?> orang</div>
                                        </div>
                                        <?php if ($booking['deskripsi']): ?>
                                            <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($booking['deskripsi']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($booking['surat_peminjaman']): ?>
                                            <p><strong>Surat:</strong> <a href="../assets/uploads/<?php echo htmlspecialchars($booking['surat_peminjaman']); ?>" target="_blank" class="file-link">📄 Lihat Surat</a></p>
                                        <?php endif; ?>
                                    </div>
                                    <form method="POST" action="?action=approve_booking" class="approval-form">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <div class="form-group">
                                            <label>Keterangan Admin:</label>
                                            <textarea name="keterangan" rows="2" placeholder="Keterangan (opsional)"></textarea>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" name="status" value="approved" class="btn btn-success">✅ Setujui</button>
                                            <button type="submit" name="status" value="rejected" class="btn btn-danger">❌ Tolak</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <div id="edit-room-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Ruangan</h2>
                <span class="close-btn" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="edit-room-form" method="POST" action="?action=edit_room">
                <input type="hidden" id="edit_room_id" name="room_id">
                <div class="form-grid">
                    <div class="form-group"><label>Nama Ruangan:</label><input type="text" id="edit_nama_ruangan" name="nama_ruangan" required></div>
                    <div class="form-group"><label>Kapasitas:</label><input type="number" id="edit_kapasitas" name="kapasitas" required></div>
                </div>
                <div class="form-grid">
                    <div class="form-group"><label>Lokasi:</label><input type="text" id="edit_lokasi" name="lokasi"></div>
                </div>
                <div class="form-group"><label>Deskripsi:</label><textarea id="edit_deskripsi" name="deskripsi" rows="3"></textarea></div>
                <div class="form-group"><label>Fasilitas (pisahkan dengan koma):</label><input type="text" id="edit_facilities" name="facilities"></div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>


    <script src="../assets/js/script.js?v=<?= time() ?>"></script>
    <script>
        // (JavaScript Anda tidak berubah)
        function toggleForm() {
            const form = document.getElementById('add-room-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function viewSchedule(roomId) {
            const today = new Date().toISOString().split('T')[0];
            window.open(`lihat_jadwal.php?room=${roomId}&date=${today}`, '_blank');
        }

        function closeEditModal() {
            document.getElementById('edit-room-modal').style.display = 'none';
        }

        function editRoom(id, kode, nama, kapasitas, lokasi, deskripsi, facilities) {
            document.getElementById('edit_room_id').value = id;
            document.getElementById('edit_nama_ruangan').value = nama;
            document.getElementById('edit_kapasitas').value = kapasitas;
            document.getElementById('edit_lokasi').value = lokasi;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_facilities').value = facilities;
            document.getElementById('edit-room-modal').style.display = 'block';
        }
        
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabId + '-tab').classList.add('active');
            document.querySelector(`.tab-btn[onclick="showTab('${tabId}')"]`).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            
            if (tab === 'approvals') {
                showTab('approvals');
            } else {
                showTab('rooms');
            }
        });
    </script>
</body>
</html>