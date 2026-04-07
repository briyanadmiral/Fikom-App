<?php
// api/aktivitas.php - API for Activity Logs
header('Content-Type: application/json');
require_once '../config/database.php';

startSession();

// Check authentication
$user_info = getUserInfo();
if (!$user_info['user_id']) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized access'], 401);
}

$database = new Database();
$db = $database->getConnection();

try {
    $action = $_GET['action'] ?? 'recent';
    
    if ($action === 'recent') {
        // Only admin can see all activities (or as per requirements)
        // Here we restrict to admin for dashboard purposes
        if (!$user_info['is_admin']) {
            jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
        }

        $query = "SELECT l.*, u.nama as user_name 
                  FROM log_aktivitas l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  ORDER BY l.created_at DESC 
                  LIMIT 10";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $activities = $stmt->fetchAll();
        
        // Format time for display
        foreach ($activities as &$activity) {
            $activity['time_formatted'] = date('H:i', strtotime($activity['created_at']));
            $activity['date_formatted'] = date('d/m/Y', strtotime($activity['created_at']));
        }
        
        jsonResponse(['success' => true, 'data' => $activities]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
?>
