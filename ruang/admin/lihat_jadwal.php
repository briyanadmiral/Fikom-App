<?php
// admin/lihat_jadwal.php - Halaman Kalender Penuh untuk Admin
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

// --- LOGIKA PROSES TAMBAH MANUAL ---
if (isset($_POST["add_manual"])) {
    try {
        $sql = "INSERT INTO jadwal_matkul 
                (ruangan_id, kode_matkul, nama_matkul, dosen, kelas, hari, tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, semester, tahun_ajaran) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        
        $stmt->execute([
            $_POST['ruangan_id'], 
            $_POST['kode_matkul'], 
            $_POST['nama_matkul'], 
            $_POST['dosen'], 
            $_POST['kelas'],
            strtolower($_POST['hari']), 
            $_POST['tanggal_mulai'], 
            $_POST['tanggal_selesai'],
            $_POST['jam_mulai'], 
            $_POST['jam_selesai'], 
            $_POST['semester'],
            $_POST['tahun_ajaran']
        ]);
        echo "<script>alert('Jadwal berhasil ditambahkan secara manual!'); window.location='lihat_jadwal.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal menambah jadwal: " . $e->getMessage() . "');</script>";
    }
}

// Ambil daftar ruangan untuk dropdown form manual
$ruangan_list = [];
try {
    $stmt_ruangan = $db->query("SELECT id, nama_ruangan FROM ruangan WHERE status = 'active'");
    $ruangan_list = $stmt_ruangan->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Jadwal - Admin FIKOM</title>
    
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.10/main.min.css' rel='stylesheet' />
    
    <style>
        #calendar-container { padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-top: 20px; }
        .fc { color: #333; }
        .fc-col-header-cell-cushion { color: #1a73e8; text-decoration: none; }
        .legend-calendar { display: flex; justify-content: center; gap: 20px; margin-top: 15px; flex-wrap: wrap; }
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .legend-color { width: 20px; height: 20px; border-radius: 4px; }
        
        /* Area Kontrol & Tombol */
        .controls-area { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 15px; }
        .room-filter label { font-weight: bold; margin-right: 10px; }
        .room-filter select { padding: 8px 12px; font-size: 1rem; border-radius: 5px; border: 1px solid #ccc; }
        .action-buttons { display: flex; gap: 10px; }
        .btn-import { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; text-align: center; }
        .btn-manual { background-color: #1a73e8; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-import:hover { background-color: #45a049; }
        .btn-manual:hover { background-color: #1557b0; }
        
        .fc-timegrid-slot-lane.fc-non-business { background-color: #fff; }

        /* CSS Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); padding-top: 40px; }
        .modal-content { background-color: #fefefe; margin: 0 auto 5% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 600px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ddd; margin-bottom: 15px; }
        .modal-header h2 { margin: 0; color: #333; font-size: 1.2rem; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: black; }
        
        /* CSS Form Manual dengan Grid */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 0.9rem; font-weight: bold; color: #555; }
        .form-group input, .form-group select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 0.95rem; }
        .btn-submit-form { width: 100%; background: #1a73e8; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; font-size: 1rem; cursor: pointer; margin-top: 10px; }
        .btn-submit-form:hover { background: #1557b0; }
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

                <hr class="mx-3 opacity-25">

                <a href="../logout.php" class="nav-item logout">
                    <i class="bi bi-box-arrow-left me-2"></i> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <h1>Kalender Jadwal Ruangan</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($user_info['nama'] ?? 'Admin'); ?></span>
                    <div class="user-avatar">👨‍💼</div>
                </div>
            </header>
            
            <div class="controls-area">
                <div class="room-filter">
                    <label for="roomSelect">Tampilkan Jadwal:</label>
                    <select id="roomSelect">
                        <option value="all">Semua Ruangan</option>
                    </select>
                </div>
                <div class="action-buttons">
                    <button class="btn-manual" onclick="openManualModal()">📝 Tambah Manual</button>
                    <button class="btn-import" onclick="window.location.href='import_matkul.php'">📁 Import Excel</button>
                </div>
            </div>
            
            <div id="calendar-container">
                <div id='calendar'></div> 
                <div class="legend-calendar">
                    <h3>Keterangan:</h3>
                    <div class="legend-item"><div class="legend-color" style="background: #3788d8;"></div> <span>Mata Kuliah Tetap</span></div>
                    <div class="legend-item"><div class="legend-color" style="background: #4CAF50;"></div> <span>Peminjaman (Disetujui)</span></div>
                    <div class="legend-item"><div class="legend-color" style="background: #ff9f0a;"></div> <span>Peminjaman (Pending)</span></div>
                </div>
            </div>
        </main>
    </div>

    <div id="event-detail-modal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2>Detail Agenda</h2>
                <span class="close-btn" onclick="closeDetailModal()">&times;</span>
            </div>
            <div id="modal-body">
                <p><strong>Kegiatan:</strong> <span id="modal-title"></span></p>
                <p><strong>Tanggal:</strong> <span id="modal-date"></span></p>
                <p><strong>Jam:</strong> <span id="modal-time"></span></p>
            </div>
        </div>
    </div>

    <div id="manual-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📝 Tambah Jadwal Manual</h2>
                <span class="close-btn" onclick="closeManualModal()">&times;</span>
            </div>
            <form action="" method="post">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Pilih Ruangan *</label>
                        <select name="ruangan_id" required>
                            <option value="">-- Pilih Ruangan --</option>
                            <?php foreach($ruangan_list as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nama_ruangan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kode Mata Kuliah</label>
                        <input type="text" name="kode_matkul" placeholder="Contoh: IF201" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Nama Mata Kuliah *</label>
                        <input type="text" name="nama_matkul" placeholder="Contoh: Pemrograman Web" required>
                    </div>
                    <div class="form-group">
                        <label>Dosen Pengampu</label>
                        <input type="text" name="dosen" placeholder="Nama Dosen" required>
                    </div>
                    <div class="form-group">
                        <label>Kelas *</label>
                        <input type="text" name="kelas" placeholder="Contoh: IF-A" required>
                    </div>
                    <div class="form-group">
                        <label>Hari *</label>
                        <select name="hari" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Semester *</label>
                        <input type="text" name="semester" placeholder="Contoh: Genap" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Mulai *</label>
                        <input type="time" name="jam_mulai" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai *</label>
                        <input type="time" name="jam_selesai" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mulai Periode *</label>
                        <input type="date" name="tanggal_mulai" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai Periode *</label>
                        <input type="date" name="tanggal_selesai" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" placeholder="Contoh: 2024/2025" required>
                    </div>
                </div>
                <button type="submit" name="add_manual" class="btn-submit-form">💾 Simpan Jadwal</button>
            </form>
        </div>
    </div>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.10/main.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.10/index.global.min.js'></script>
    <script src="../assets/js/script.js"></script> 

    <script>
      // Fungsi Buka-Tutup Modal
      function closeDetailModal() { document.getElementById('event-detail-modal').style.display = 'none'; }
      function openManualModal() { document.getElementById('manual-modal').style.display = 'block'; }
      function closeManualModal() { document.getElementById('manual-modal').style.display = 'none'; }
      
      // Menutup modal jika klik di luar modal area
      window.onclick = function(event) {
          if (event.target == document.getElementById('event-detail-modal')) closeDetailModal();
          if (event.target == document.getElementById('manual-modal')) closeManualModal();
      }

      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var roomSelect = document.getElementById('roomSelect');
        var calendar; 

        fetch('../api/ruangan.php?action=list') 
          .then(response => response.json())
          .then(result => {
            if (result.success && result.data) {
              result.data.forEach(room => {
                var option = new Option(room.nama_ruangan, room.id);
                roomSelect.add(option);
              });
            }
            initializeCalendar();
          })
          .catch(error => { initializeCalendar(); });
          
        function initializeCalendar() {
          calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'id',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
              var selectedRoomId = roomSelect.value;
              fetch('../api/get_jadwal.php?room_id=' + selectedRoomId) 
                .then(response => response.json())
                .then(data => { successCallback(data); })
                .catch(error => { failureCallback(error); });
            },
            slotMinTime: '06:00:00',
            slotMaxTime: '21:00:00',
            allDaySlot: false, 
            businessHours: { daysOfWeek: [ 1, 2, 3, 4, 5, 6 ], startTime: '06:00', endTime: '21:00' },
            height: 'auto',
            nowIndicator: true,

            eventClick: function(clickInfo) {
                clickInfo.jsEvent.preventDefault(); 
                var event = clickInfo.event;
                
                var startTime = event.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace(/\./g, ':');
                var endTime = event.end ? event.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace(/\./g, ':') : 'N/A';
                var date = event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                
                document.getElementById('modal-title').textContent = event.title; 
                document.getElementById('modal-date').textContent = date;
                document.getElementById('modal-time').textContent = startTime + ' - ' + endTime;

                document.getElementById('event-detail-modal').style.display = 'block';
            }
          });
          calendar.render();
        }

        roomSelect.addEventListener('change', function() {
          if (calendar) { calendar.refetchEvents(); }
        });
      });
    </script>
</body>
</html>