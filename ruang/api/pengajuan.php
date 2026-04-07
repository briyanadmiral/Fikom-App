<?php
// api/pengajuan.php - Fixed API untuk pengajuan peminjaman
header('Content-Type: application/json');
require_once '../config/database.php';

startSession();

// Check authentication - Allow mahasiswa access for some actions
$user_roles = checkSessionRole();
if (!$user_roles) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized access'], 401);
}

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            handlePost($db, $input);
            break;
        case 'PUT':
            handlePut($db, $input);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

function handleGet($db) {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            getBookings($db);
            break;
        case 'detail':
            getBookingDetail($db, $_GET['id'] ?? null);
            break;
        case 'user_bookings':
            getUserBookings($db, $_GET['user_id'] ?? null);
            break;
        case 'pending':
            getPendingBookings($db);
            break;
        case 'check_conflict':
            checkConflict($db, $_GET['room_id'] ?? null, $_GET['date'] ?? null, $_GET['time_start'] ?? null, $_GET['time_end'] ?? null);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}

function getBookings($db) {
    $user_info = getUserInfo();
    
    if ($user_info['is_admin']) {
        $query = "SELECT pp.*, u.nama, u.email, r.nama_ruangan 
                  FROM pengajuan_peminjaman pp 
                  JOIN users u ON pp.user_id = u.id 
                  JOIN ruangan r ON pp.ruangan_id = r.id 
                  ORDER BY pp.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
    } else {
        $query = "SELECT pp.*, r.nama_ruangan 
                  FROM pengajuan_peminjaman pp 
                  JOIN ruangan r ON pp.ruangan_id = r.id 
                  WHERE pp.user_id = ? 
                  ORDER BY pp.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
    }
    
    $bookings = $stmt->fetchAll();
    jsonResponse(['success' => true, 'data' => $bookings]);
}

function getUserBookings($db, $user_id) {
    $user_info = getUserInfo();
    
    // Allow mahasiswa to see their own bookings
    if (!$user_info['is_admin'] && (!$user_id || $user_id != $_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'] ?? 0;
    }
    
    $target_user_id = $user_id ?? $_SESSION['user_id'];
    
    $query = "SELECT pp.*, r.nama_ruangan, r.kode_ruangan
              FROM pengajuan_peminjaman pp 
              JOIN ruangan r ON pp.ruangan_id = r.id 
              WHERE pp.user_id = ? 
              ORDER BY pp.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$target_user_id]);
    $bookings = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'data' => $bookings]);
}

function getPendingBookings($db) {
    $user_info = getUserInfo();
    if (!$user_info['is_admin']) {
        jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
    }
    
    $query = "SELECT pp.*, u.nama, u.email, r.nama_ruangan, r.kode_ruangan
              FROM pengajuan_peminjaman pp 
              JOIN users u ON pp.user_id = u.id 
              JOIN ruangan r ON pp.ruangan_id = r.id 
              WHERE pp.status = 'pending' 
              ORDER BY pp.created_at ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $bookings = $stmt->fetchAll();
    
    jsonResponse(['success' => true, 'data' => $bookings]);
}

function handlePost($db, $input) {
    // Allow mahasiswa and dosen to create bookings
    $user_info = getUserInfo();
    if (!$user_info['is_mahasiswa'] && !$user_info['is_dosen']) {
        jsonResponse(['success' => false, 'message' => 'Student or lecturer access required'], 403);
    }
    
    if (!$input) {
        jsonResponse(['success' => false, 'message' => 'Invalid input'], 400);
    }
    
    $action = $input['action'] ?? 'create';
    
    switch ($action) {
        case 'create':
            createBooking($db, $input);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}

function createBooking($db, $input) {
    $required_fields = ['ruangan_id', 'keperluan', 'tanggal_pinjam', 'jam_mulai', 'jam_selesai'];
    
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            jsonResponse(['success' => false, 'message' => "Field $field is required"], 400);
        }
    }
    
    // Check for conflicts
    $conflict_query = "SELECT COUNT(*) as conflicts FROM pengajuan_peminjaman 
                       WHERE ruangan_id = ? AND tanggal_pinjam = ? 
                       AND status IN ('approved', 'pending')
                       AND ((jam_mulai < ? AND jam_selesai > ?) 
                            OR (jam_mulai < ? AND jam_selesai > ?)
                            OR (jam_mulai >= ? AND jam_selesai <= ?))";
    
    $stmt = $db->prepare($conflict_query);
    $stmt->execute([
        $input['ruangan_id'], $input['tanggal_pinjam'],
        $input['jam_selesai'], $input['jam_mulai'],
        $input['jam_mulai'], $input['jam_selesai'],
        $input['jam_mulai'], $input['jam_selesai']
    ]);
    
    if ($stmt->fetch()['conflicts'] > 0) {
        jsonResponse(['success' => false, 'message' => 'Time slot conflicts with existing booking'], 400);
    }
    
    try {
        $query = "INSERT INTO pengajuan_peminjaman 
                  (user_id, ruangan_id, keperluan, deskripsi, tanggal_pinjam, jam_mulai, jam_selesai, jumlah_peserta) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_SESSION['user_id'],
            $input['ruangan_id'],
            $input['keperluan'],
            $input['deskripsi'] ?? null,
            $input['tanggal_pinjam'],
            $input['jam_mulai'],
            $input['jam_selesai'],
            $input['jumlah_peserta'] ?? null
        ]);
        
        $booking_id = $db->lastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Create Booking', "Created booking request for room ID: {$input['ruangan_id']}", $input['ruangan_id']);
        
        jsonResponse(['success' => true, 'message' => 'Booking request created successfully', 'booking_id' => $booking_id]);
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

function handlePut($db, $input) {
    // Only admin can update booking status
    $user_info = getUserInfo();
    if (!$user_info['is_admin']) {
        jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
    }
    
    if (!$input || !isset($input['id'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid input or missing ID'], 400);
    }
    
    $action = $input['action'] ?? 'update_status';
    
    switch ($action) {
        case 'update_status':
            updateBookingStatus($db, $input);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}

function updateBookingStatus($db, $input) {
    if (!isset($input['status']) || !in_array($input['status'], ['approved', 'rejected'])) {
        jsonResponse(['success' => false, 'message' => 'Valid status (approved/rejected) required'], 400);
    }
    
    try {
        $query = "UPDATE pengajuan_peminjaman 
                  SET status = ?, keterangan_admin = ?, approved_by = ?, approved_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $input['status'],
            $input['keterangan_admin'] ?? null,
            $_SESSION['user_id'],
            $input['id']
        ]);
        
        if ($stmt->rowCount() > 0) {
            logActivity($_SESSION['user_id'], 'Update Booking Status', "Updated booking ID {$input['id']} to {$input['status']}", null);
            jsonResponse(['success' => true, 'message' => 'Booking status updated successfully']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Booking not found or no changes made'], 404);
        }
        
    } catch (PDOException $e) {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}
?>