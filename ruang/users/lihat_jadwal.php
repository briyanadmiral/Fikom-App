<?php
// mahasiswa/lihat_jadwal.php - Halaman Kalender Penuh untuk Mahasiswa
require_once '../config/database.php';

startSession();

// Check users session
if (!checkSessionRole(['users'])) {
    header("Location: ../login.php");
    exit;
}

$user_info = getUserInfo();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Jadwal - FIKOM</title>
    
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.10/main.min.css' rel='stylesheet' />
    
    <style>
        body { background-color: #f4f7f6; background-image: none; }
        #calendar-container { padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-top: 20px; }
        .fc { color: #333; }
        .fc-col-header-cell-cushion { color: #1a73e8; text-decoration: none; }
        .legend-calendar { display: flex; justify-content: center; gap: 20px; margin-top: 15px; flex-wrap: wrap; }
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .legend-color { width: 20px; height: 20px; border-radius: 4px; }
        .room-filter { margin-top: 20px; font-size: 1.1rem; text-align: center; }
        .room-filter label { font-weight: bold; margin-right: 10px; }
        .room-filter select { padding: 8px 12px; font-size: 1rem; border-radius: 5px; border: 1px solid #ccc; }
        .fc-timegrid-slot-lane.fc-non-business { background-color: #fff; }

        /* [BARU] CSS untuk Modal Detail */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); padding-top: 60px; }
        .modal-content { background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #ddd; }
        .modal-header h2 { margin: 0; color: #333; }
        .close-btn { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-btn:hover, .close-btn:focus { color: black; text-decoration: none; }
        #modal-body p { color: #333; font-size: 1.1rem; line-height: 1.6; }
        #modal-body strong { color: #1a73e8; }
    </style>
</head>
<body>
    <div class="student-container">
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

        <main class="main-content">
            <header class="top-bar">
                <h1>Kalender Jadwal Ruangan</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo htmlspecialchars($user_info['nama'] ?? 'Pengguna'); ?></span>
                    <div class="user-avatar">👤</div>
                </div>
            </header>
            <div class="room-filter">
                <label for="roomSelect">Tampilkan Jadwal Untuk:</label>
                <select id="roomSelect">
                    <option value="all">Semua Ruangan</option>
                </select>
            </div>
            
            <div class="tabs" style="margin-top: 1rem; margin-bottom: 1.5rem;">
                <button class="tab-btn active" id="btn-calendar-view" onclick="switchView('calendar')"><i class="bi bi-calendar3 me-2"></i> Mode Kalender</button>
                <button class="tab-btn" id="btn-table-view" onclick="switchView('table')"><i class="bi bi-table me-2"></i> Mode Tabel</button>
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

            <div id="table-container" style="display: none;">
                <div class="rooms-table-wrapper">
                    <table class="rooms-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Agenda/Kegiatan</th>
                                <th>Jenis</th>
                                <th>Waktu</th>
                                <th>Status / Hari</th>
                            </tr>
                        </thead>
                        <tbody id="schedule-table-body">
                            <!-- Dinamis via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="event-detail-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Agenda</h2>
                <span class="close-btn" onclick="closeDetailModal()">&times;</span>
            </div>
            <div id="modal-body" style="padding: 20px;">
                <p><strong>Kegiatan:</strong> <span id="modal-title"></span></p>
                <p><strong>Tanggal:</strong> <span id="modal-date"></span></p>
                <p><strong>Jam:</strong> <span id="modal-time"></span></p>
                <p><strong>Status:</strong> <span id="modal-status"></span></p>
            </div>
        </div>
    </div>

    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.10/main.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.10/index.global.min.js'></script>
    <script src="../assets/js/script.js?v=<?= time() ?>"></script> 

    <script>
      // [BARU] Fungsi untuk menutup modal
      function closeDetailModal() {
          document.getElementById('event-detail-modal').style.display = 'none';
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
          .catch(error => {
            console.error('Gagal mengambil daftar ruangan:', error);
            initializeCalendar(); 
          });
          
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
                .then(data => { 
                    successCallback(data); 
                    populateTable(data);
                })
                .catch(error => { failureCallback(error); });
            },
            
            slotMinTime: '06:00:00',
            slotMaxTime: '21:00:00',
            allDaySlot: false, 
            
            businessHours: {
              daysOfWeek: [ 1, 2, 3, 4, 5, 6 ],
              startTime: '06:00',
              endTime: '21:00'
            },
            
            height: 'auto',
            nowIndicator: true,

            // [BARU] Tambahkan event handler ini
            eventClick: function(clickInfo) {
                clickInfo.jsEvent.preventDefault(); 

                var event = clickInfo.event;
                
                var startTime = event.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace(/\./g, ':');
                var endTime = event.end ? event.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace(/\./g, ':') : 'N/A';
                var date = event.start.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                
                document.getElementById('modal-title').textContent = event.title;
                document.getElementById('modal-date').textContent = date;
                document.getElementById('modal-time').textContent = startTime + ' - ' + endTime;

                var statusText = 'Mata Kuliah Tetap';
                if (event.extendedProps.status) {
                    if (event.extendedProps.status === 'approved') {
                        statusText = 'Disetujui';
                    } else if (event.extendedProps.status === 'pending') {
                        statusText = 'Pending';
                    } else {
                        statusText = event.extendedProps.status;
                    }
                }
                document.getElementById('modal-status').textContent = statusText;

                document.getElementById('event-detail-modal').style.display = 'block';
            }
          });
          calendar.render();
        }

        roomSelect.addEventListener('change', function() {
          if (calendar) {
            calendar.refetchEvents();
          }
        });
      });

      function switchView(view) {
          var btnCalendar = document.getElementById('btn-calendar-view');
          var btnTable = document.getElementById('btn-table-view');
          var calendarCont = document.getElementById('calendar-container');
          var tableCont = document.getElementById('table-container');
          
          if (view === 'calendar') {
              btnCalendar.classList.add('active');
              btnTable.classList.remove('active');
              calendarCont.style.display = 'block';
              tableCont.style.display = 'none';
              if (calendar) {
                  calendar.updateSize();
              }
          } else {
              btnCalendar.classList.remove('active');
              btnTable.classList.add('active');
              calendarCont.style.display = 'none';
              tableCont.style.display = 'block';
          }
      }

      function populateTable(events) {
          var tbody = document.getElementById('schedule-table-body');
          tbody.innerHTML = '';
          
          if (!events || events.length === 0) {
              tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 30px;">📭 Tidak ada jadwal untuk ruangan ini.</td></tr>';
              return;
          }
          
          events.forEach((event, index) => {
              var tr = document.createElement('tr');
              
              // No
              var tdNo = document.createElement('td');
              tdNo.textContent = index + 1;
              tr.appendChild(tdNo);
              
              // Agenda/Kegiatan
              var tdTitle = document.createElement('td');
              tdTitle.innerHTML = '<strong>' + escapeHtml(event.title) + '</strong>';
              tr.appendChild(tdTitle);
              
              // Jenis
              var tdType = document.createElement('td');
              var isMatkul = event.id.startsWith('matkul_');
              tdType.innerHTML = isMatkul ? '<span class="facility-tag" style="background: rgba(55, 136, 216, 0.2); color: #1c5fa1;">Mata Kuliah</span>' : '<span class="facility-tag" style="background: rgba(76, 175, 80, 0.2); color: #2e7d32;">Peminjaman</span>';
              tr.appendChild(tdType);
              
              // Waktu
              var tdTime = document.createElement('td');
              if (isMatkul) {
                  var period = '';
                  if (event.startRecur && event.endRecur) {
                      var startD = formatDateString(event.startRecur);
                      var endD = event.endRecur;
                      try {
                          var dateObj = new Date(event.endRecur);
                          dateObj.setDate(dateObj.getDate() - 1);
                          var yyyy = dateObj.getFullYear();
                          var mm = String(dateObj.getMonth() + 1).padStart(2, '0');
                          var dd = String(dateObj.getDate()).padStart(2, '0');
                          endD = yyyy + '-' + mm + '-' + dd;
                      } catch(e){}
                      period = '<br><span style="font-size:0.8rem; color:#666;">Periode: ' + startD + ' s/d ' + formatDateString(endD) + '</span>';
                  }
                  tdTime.innerHTML = event.startTime.substring(0, 5) + ' - ' + event.endTime.substring(0, 5) + period;
              } else {
                  var parts = event.start.split(' ');
                  var dateStr = formatDateString(parts[0]);
                  var timeStr = parts[1].substring(0, 5) + ' - ' + event.end.split(' ')[1].substring(0, 5);
                  tdTime.innerHTML = '<strong>' + dateStr + '</strong><br><span style="font-size:0.8rem; color:#666;">' + timeStr + '</span>';
              }
              tr.appendChild(tdTime);
              
              // Status / Hari
              var tdStatus = document.createElement('td');
              if (isMatkul) {
                  var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                  var dayName = event.daysOfWeek && event.daysOfWeek.length > 0 ? days[event.daysOfWeek[0]] : '-';
                  tdStatus.innerHTML = '<span class="status-badge" style="background: rgba(55, 136, 216, 0.1); color: #1c5fa1; border: 1px solid rgba(55, 136, 216, 0.3);">' + dayName + '</span>';
              } else {
                  var statusClass = event.status === 'approved' ? 'approved' : 'pending';
                  var statusText = event.status === 'approved' ? 'Disetujui' : 'Pending';
                  tdStatus.innerHTML = '<span class="status-badge ' + statusClass + '">' + statusText + '</span>';
              }
              tr.appendChild(tdStatus);
              
              tbody.appendChild(tr);
          });
      }
      
      function escapeHtml(text) {
          return text
              .replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;");
      }
      
      function formatDateString(str) {
          if (!str) return '-';
          var parts = str.split('-');
          if (parts.length !== 3) return str;
          return parts[2] + '/' + parts[1] + '/' + parts[0];
      }
    </script>
</body>
</html>