<?php
header('Content-Type: application/json');
require_once '../config/database.php'; 

$database = new Database();
$db = $database->getConnection();

$events = [];

// 1. Ambil filter ID ruangan dari URL
$room_id = $_GET['room_id'] ?? 'all';
$where_clause = ""; 

if ($room_id != 'all' && is_numeric($room_id)) {
    $where_clause = " AND r.id = " . intval($room_id); 
}

try {
    // --- BAGIAN 1: PENGAJUAN PEMINJAMAN (Approved & Pending) ---
    // (Bagian ini tidak ada yang diubah, sudah aman)
    
    $sql_pinjam = "SELECT 
                        p.id, p.keperluan, p.tanggal_pinjam, 
                        p.jam_mulai, p.jam_selesai, p.status, 
                        r.nama_ruangan 
                   FROM 
                        pengajuan_peminjaman p
                   JOIN 
                        ruangan r ON p.ruangan_id = r.id
                   WHERE 
                        (p.status = 'approved' OR p.status = 'pending') $where_clause"; 
    
    $stmt_pinjam = $db->prepare($sql_pinjam);
    $stmt_pinjam->execute();
    $bookings = $stmt_pinjam->fetchAll();

    foreach ($bookings as $booking) {
        $color = ($booking['status'] == 'approved') ? '#4CAF50' : '#ff9f0a'; 

        $events[] = [
            'id'    => 'book_' . $booking['id'],
            'title' => $booking['keperluan'] . ' (' . $booking['nama_ruangan'] . ')', 
            'start' => $booking['tanggal_pinjam'] . ' ' . $booking['jam_mulai'], 
            'end'   => $booking['tanggal_pinjam'] . ' ' . $booking['jam_selesai'], 
            'color' => $color,
            'allDay'=> false 
        ];
    }

    // --- BAGIAN 2: JADWAL MATA KULIAH (Berulang sesuai tanggal mulai & selesai) ---
    $hariMap = [
        'senin' => 1, 'selasa' => 2, 'rabu' => 3, 
        'kamis' => 4, 'jumat' => 5, 'sabtu' => 6, 'minggu' => 0
    ];

    // [DIUBAH] Tambahkan j.tanggal_mulai dan j.tanggal_selesai di SELECT
    $sql_matkul = "SELECT 
                        j.id, j.nama_matkul, j.hari, 
                        j.jam_mulai, j.jam_selesai, j.tanggal_mulai, j.tanggal_selesai,
                        r.nama_ruangan 
                   FROM 
                        jadwal_matkul j
                   JOIN 
                        ruangan r ON j.ruangan_id = r.id
                   WHERE 
                        1=1 $where_clause"; 

    $stmt_matkul = $db->prepare($sql_matkul);
    $stmt_matkul->execute();
    $matkuls = $stmt_matkul->fetchAll();

    foreach ($matkuls as $matkul) {
        $hari_key = strtolower(trim($matkul['hari']));
        if (isset($hariMap[$hari_key])) {
            
            // [BARU] Menambahkan +1 hari agar FullCalendar memasukkan hari terakhir ke dalam rentang
            $end_recur = null;
            if (!empty($matkul['tanggal_selesai'])) {
                $end_recur = date('Y-m-d', strtotime($matkul['tanggal_selesai'] . ' +1 day'));
            }

            $events[] = [
                'id'        => 'matkul_' . $matkul['id'],
                'title'     => $matkul['nama_matkul'] . ' (' . $matkul['nama_ruangan'] . ')',
                'daysOfWeek'=> [ $hariMap[$hari_key] ], 
                'startTime' => $matkul['jam_mulai'],
                'endTime'   => $matkul['jam_selesai'], 
                'startRecur'=> $matkul['tanggal_mulai'], // [BARU] Menentukan tanggal mulai kelas
                'endRecur'  => $end_recur,               // [BARU] Menentukan tanggal selesai kelas (+1 hari)
                'color'     => '#3788d8' 
            ];
        }
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Kembalikan semua data (booking + matkul) sebagai JSON
echo json_encode($events);
?>