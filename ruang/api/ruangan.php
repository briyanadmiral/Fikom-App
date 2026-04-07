<?php
// api/ruangan.php - API untuk manajemen ruangan (VERSI SUDAH DIPERBAIKI)
header('Content-Type: application/json');
require_once '../config/database.php';

startSession();

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Asumsi jsonResponse dan checkSessionRole ada di database.php
// Jika jsonResponse tidak otomatis 'exit', kita harus tambahkan 'exit'
// Mari kita asumsikan jsonResponse tidak exit.

try {
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            if (!checkSessionRole(['admin'])) {
                jsonResponse(['success' => false, 'message' => 'Unauthorized access'], 401);
                exit; // <-- Tambahkan exit
            }
            handlePost($db, $input);
            break;
        case 'PUT':
            if (!checkSessionRole(['admin'])) {
                jsonResponse(['success' => false, 'message' => 'Unauthorized access'], 401);
                exit; // <-- Tambahkan exit
            }
            handlePut($db, $input);
            break;
        case 'DELETE':
            if (!checkSessionRole(['admin'])) {
                jsonResponse(['success' => false, 'message' => 'Unauthorized access'], 401);
                exit; // <-- Tambahkan exit
            }
            handleDelete($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            exit; // <-- Tambahkan exit
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
    exit; // <-- Tambahkan exit
}

function handleGet($db) {
    $action = $_GET['action'] ?? 'list';

    // Aksi 'list' bersifat PUBLIK
    if ($action == 'list') {
        getRooms($db);
        return; // Selesai, langsung keluar
    }

    // --- Dari titik ini, semua aksi GET memerlukan login ADMIN ---
    if (!checkSessionRole(['admin'])) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized access for this action'], 401);
        exit; // <-- Tambahkan exit
    }
    
    switch ($action) {
        case 'detail':
            getRoomDetail($db, $_GET['id'] ?? null);
            break;
        case 'schedule':
            getRoomSchedule($db, $_GET['room_id'] ?? null, $_GET['date'] ?? date('Y-m-d'));
            break;
        case 'availability':
            checkAvailability($db, $_GET['room_id'] ?? null, $_GET['date'] ?? null, $_GET['time_start'] ?? null, $_GET['time_end'] ?? null);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
            exit; // <-- Tambahkan exit
    }
}

function getRooms($db) {
    // Query ringan untuk dropdown publik
    $query = "SELECT r.id, r.nama_ruangan 
              FROM ruangan r 
              WHERE r.status = 'active'
              ORDER BY r.nama_ruangan";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $rooms = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'data' => $rooms]);
    exit; // <-- Tambahkan exit
}

function getRoomDetail($db, $room_id) {
    if (!$room_id) {
        jsonResponse(['success' => false, 'message' => 'Room ID required'], 400);
        exit; // <-- Tambahkan exit
    }
    
    $query = "SELECT r.*, COUNT(fr.id) as total_facilities
              FROM ruangan r 
              LEFT JOIN fasilitas_ruangan fr ON r.id = fr.ruangan_id 
              WHERE r.id = ? AND r.status = 'active'
              GROUP BY r.id";
    $stmt = $db->prepare($query);
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        jsonResponse(['success' => false, 'message' => 'Room not found'], 404);
        exit; // <-- Tambahkan exit
    }
    
    // Get facilities
    $facilities_query = "SELECT * FROM fasilitas_ruangan WHERE ruangan_id = ?";
    $facilities_stmt = $db->prepare($facilities_query);
    $facilities_stmt->execute([$room_id]);
    $room['facilities'] = $facilities_stmt->fetchAll();
    
    // INI DIA PERBAIKANNYA
    jsonResponse(['success' => true, 'data' => $room]);
    exit; // <-- Tambahkan exit
} // <-- Brace ini sekarang aman

// FUNGSI INI SEKARANG AKAN BEKERJA
function getRoomSchedule($db, $room_id, $date) {
    if (!$room_id) {
        jsonResponse(['success' => false, 'message' => 'Room ID required'], 400);
        exit; // <-- Tambahkan exit
    }
    
    // Get day of week
    $day_of_week = date('l', strtotime($date));
    $hari_indo = [
        'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
        'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'
    ];
    $hari = $hari_indo[$day_of_week];
    
    $schedule = [];
    for ($hour = 6; $hour <= 20; $hour++) {
        $time = sprintf('%02d:00', $hour);
        $schedule[$time] = ['status' => 'available', 'activity' => '', 'detail' => ''];
    }
    
    // Get regular class schedules
    $matkul_query = "SELECT * FROM jadwal_matkul 
                     WHERE ruangan_id = ? AND hari = ? 
                     ORDER BY jam_mulai";
    $stmt = $db->prepare($matkul_query);
    $stmt->execute([$room_id, $hari]);
    $matkuls = $stmt->fetchAll();
    
    foreach ($matkuls as $matkul) {
        $start_hour = (int)substr($matkul['jam_mulai'], 0, 2);
        $end_hour = (int)substr($matkul['jam_selesai'], 0, 2);
        for ($h = $start_hour; $h < $end_hour; $h++) {
            $time_key = sprintf('%02d:00', $h);
            if (isset($schedule[$time_key])) {
                $schedule[$time_key] = [
                    'status' => 'occupied',
                    'activity' => $matkul['nama_matkul'],
                    'detail' => ($matkul['kelas'] ?? '') . ' - ' . ($matkul['dosen'] ?? '')
                ];
            }
        }
    }
    
    // Get approved bookings
    $booking_query = "SELECT pp.*, u.nama as peminjam_nama 
                      FROM pengajuan_peminjaman pp 
                      JOIN users u ON pp.user_id = u.id
                      WHERE pp.ruangan_id = ? AND pp.tanggal_pinjam = ? 
                      AND pp.status = 'approved'
                      ORDER BY pp.jam_mulai";
    $stmt = $db->prepare($booking_query);
    $stmt->execute([$room_id, $date]);
    $bookings = $stmt->fetchAll();
    
    foreach ($bookings as $booking) {
        $start_hour = (int)substr($booking['jam_mulai'], 0, 2);
        $end_hour = (int)substr($booking['jam_selesai'], 0, 2);
        for ($h = $start_hour; $h < $end_hour; $h++) {
            $time_key = sprintf('%02d:00', $h);
            if (isset($schedule[$time_key])) {
                $schedule[$time_key] = [
                    'status' => 'occupied',
                    'activity' => $booking['keperluan'],
                    'detail' => 'Dipinjam: ' . $booking['peminjam_nama']
                ];
            }
        }
    }
    
    // Get pending bookings
    $pending_query = "SELECT pp.*, u.nama as peminjam_nama 
                      FROM pengajuan_peminjaman pp 
                      JOIN users u ON pp.user_id = u.id
                      WHERE pp.ruangan_id = ? AND pp.tanggal_pinjam = ? 
                      AND pp.status = 'pending'
                      ORDER BY pp.jam_mulai";
    $stmt = $db->prepare($pending_query);
    $stmt->execute([$room_id, $date]);
    $pending_bookings = $stmt->fetchAll();
    
    foreach ($pending_bookings as $booking) {
        $start_hour = (int)substr($booking['jam_mulai'], 0, 2);
        $end_hour = (int)substr($booking['jam_selesai'], 0, 2);
        for ($h = $start_hour; $h < $end_hour; $h++) {
            $time_key = sprintf('%02d:00', $h);
            if (isset($schedule[$time_key]) && $schedule[$time_key]['status'] === 'available') {
                $schedule[$time_key] = [
                    'status' => 'pending',
                    'activity' => $booking['keperluan'],
                    'detail' => 'Pending: ' . $booking['peminjam_nama']
                ];
            }
        }
    }
    
    jsonResponse(['success' => true, 'data' => $schedule]);
    exit; // <-- Tambahkan exit
}

function checkAvailability($db, $room_id, $date, $time_start, $time_end) {
    if (!$room_id || !$date || !$time_start || !$time_end) {
        jsonResponse(['success' => false, 'message' => 'Missing required parameters'], 400);
        exit; // <-- Tambahkan exit
    }
    
    $day_of_week = date('l', strtotime($date));
    $hari_indo = [
        'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
        'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu'
    ];
    $hari = $hari_indo[$day_of_week];
    
    $matkul_query = "SELECT COUNT(*) as conflicts FROM jadwal_matkul 
                     WHERE ruangan_id = ? AND hari = ? 
                     AND ((jam_mulai < ? AND jam_selesai > ?) 
                         OR (jam_mulai < ? AND jam_selesai > ?)
                         OR (jam_mulai >= ? AND jam_selesai <= ?))";
    $stmt = $db->prepare($matkul_query);
    $stmt->execute([$room_id, $hari, $time_end, $time_start, $time_start, $time_end, $time_start, $time_end]);
    $matkul_conflicts = $stmt->fetch()['conflicts'];
    
    $booking_query = "SELECT COUNT(*) as conflicts FROM pengajuan_peminjaman 
                      WHERE ruangan_id = ? AND tanggal_pinjam = ? 
                      AND status IN ('approved', 'pending')
                      AND ((jam_mulai < ? AND jam_selesai > ?) 
                           OR (jam_mulai < ? AND jam_selesai > ?)
                           OR (jam_mulai >= ? AND jam_selesai <= ?))";
    $stmt = $db->prepare($booking_query);
    $stmt->execute([$room_id, $date, $time_end, $time_start, $time_start, $time_end, $time_start, $time_end]);
    $booking_conflicts = $stmt->fetch()['conflicts'];
    
    $available = ($matkul_conflicts == 0 && $booking_conflicts == 0);
    
    jsonResponse([
        'success' => true, 
        'available' => $available,
        'conflicts' => [
            'matkul' => $matkul_conflicts > 0,
            'bookings' => $booking_conflicts > 0
        ]
    ]);
    exit; // <-- Tambahkan exit
}

function handlePost($db, $input) {
    if (!$input) {
        jsonResponse(['success' => false, 'message' => 'Invalid input'], 400);
        exit; // <-- Tambahkan exit
    }
    
    $action = $input['action'] ?? 'create';
    
    switch ($action) {
        case 'create':
            createRoom($db, $input);
            break;
        case 'add_facility':
            addFacility($db, $input);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
            exit; // <-- Tambahkan exit
    }
}

function createRoom($db, $input) {
    $required_fields = ['kode_ruangan', 'nama_ruangan', 'kapasitas'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            jsonResponse(['success' => false, 'message' => "Field $field is required"], 400);
            exit; // <-- Tambahkan exit
        }
    }
    
    try {
        $db->beginTransaction();
        
        $query = "INSERT INTO ruangan (kode_ruangan, nama_ruangan, kapasitas, lokasi, deskripsi) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $input['kode_ruangan'], $input['nama_ruangan'], $input['kapasitas'],
            $input['lokasi'] ?? null, $input['deskripsi'] ?? null
        ]);
        $room_id = $db->lastInsertId();
        
        if (isset($input['facilities']) && is_array($input['facilities'])) {
            $facility_query = "INSERT INTO fasilitas_ruangan (ruangan_id, nama_fasilitas, kondisi) VALUES (?, ?, ?)";
            $facility_stmt = $db->prepare($facility_query);
            foreach ($input['facilities'] as $facility) {
                $facility_stmt->execute([$room_id, $facility['nama'], $facility['kondisi'] ?? 'baik']);
            }
        }
        
        $db->commit();
        
        $user_info = getUserInfo();
        logActivity($user_info['user_id'] ?? 1, 'Create Room', "Created room: {$input['nama_ruangan']}", $room_id);
        
        jsonResponse(['success' => true, 'message' => 'Room created successfully', 'room_id' => $room_id]);
        exit; // <-- Tambahkan exit
        
    } catch (PDOException $e) {
        $db->rollback();
        if ($e->getCode() == 23000) {
            jsonResponse(['success' => false, 'message' => 'Room code already exists'], 400);
        } else {
            jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
        exit; // <-- Tambahkan exit
    }
}

function addFacility($db, $input) {
    if (!isset($input['ruangan_id']) || !isset($input['nama_fasilitas'])) {
        jsonResponse(['success' => false, 'message' => 'Room ID and facility name are required'], 400);
        exit; // <-- Tambahkan exit
    }
    
    $query = "INSERT INTO fasilitas_ruangan (ruangan_id, nama_fasilitas, kondisi) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$input['ruangan_id'], $input['nama_fasilitas'], $input['kondisi'] ?? 'baik']);
    
    jsonResponse(['success' => true, 'message' => 'Facility added successfully']);
    exit; // <-- Tambahkan exit
}

function handlePut($db, $input) {
    if (!$input || !isset($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid input or missing ID'], 400);
        exit; // <-- Tambahkan exit
    }
    
    $fields_to_update = [];
    $params = [];
    $allowed_fields = ['kode_ruangan', 'nama_ruangan', 'kapasitas', 'lokasi', 'deskripsi', 'status'];
    
    foreach ($allowed_fields as $field) {
        if (isset($input[$field])) {
            $fields_to_update[] = "$field = ?";
            $params[] = $input[$field];
        }
    }
    
    if (empty($fields_to_update)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
        exit; // <-- Tambahkan exit
    }
    
    $params[] = $input['id'];
    
    $query = "UPDATE ruangan SET " . implode(', ', $fields_to_update) . " WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        $user_info = getUserInfo();
        logActivity($user_info['user_id'] ?? 1, 'Update Room', "Updated room ID: {$input['id']}", $input['id']);
        jsonResponse(['success' => true, 'message' => 'Room updated successfully']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Room not found or no changes made'], 404);
    }
    exit; // <-- Tambahkan exit
}

function handleDelete($db) {
    $room_id = $_GET['id'] ?? null;
    if (!$room_id) {
        jsonResponse(['success' => false, 'message' => 'Room ID required'], 400);
        exit; // <-- Tambahkan exit
    }
    
    $query = "UPDATE ruangan SET status = 'inactive' WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$room_id]);
    
    if ($stmt->rowCount() > 0) {
        $user_info = getUserInfo();
        logActivity($user_info['user_id'] ?? 1, 'Delete Room', "Deleted room ID: $room_id", $room_id);
        jsonResponse(['success' => true, 'message' => 'Room deleted successfully']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Room not found'], 404);
    }
    exit; // <-- Tambahkan exit
}
?>