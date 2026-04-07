<?php
// demo_login.php - Demo login untuk testing sistem
// File ini hanya untuk development/testing

header('Content-Type: application/json');
require_once 'config/database.php';

startSession();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['role'])) {
    jsonResponse(['success' => false, 'message' => 'Role required'], 400);
}

$role = $input['role'];

// Validasi role
if (!in_array($role, ['admin', 'users', 'mahasiswa'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid role'], 400);
}

try {
    // Reset semua session
    $_SESSION['admin'] = false;
    $_SESSION['users'] = false;
    $_SESSION['mahasiswa'] = false;
    
    // Set session berdasarkan role yang dipilih
    $_SESSION[$role] = true;
    
    // Set additional user info (demo data)
    $demo_users = [
        'admin' => [
            'user_id' => 1,
            'email' => 'admin@fikom.univ.ac.id',
            'nama' => 'Administrator FIKOM'
        ],
        'users' => [
            'user_id' => 4,
            'email' => 'mahasiswa1@student.univ.ac.id',
            'nama' => 'Ahmad Rizki'
        ],
        'mahasiswa' => [
            'user_id' => 4,
            'email' => 'mahasiswa1@student.univ.ac.id',
            'nama' => 'Ahmad Rizki'
        ]
    ];
    
    if (isset($demo_users[$role])) {
        $_SESSION['user_id'] = $demo_users[$role]['user_id'];
        $_SESSION['email'] = $demo_users[$role]['email'];
        $_SESSION['nama'] = $demo_users[$role]['nama'];
        $_SESSION['role'] = $role;
    }
    
    // Log aktivitas
    logActivity(
        $_SESSION['user_id'], 
        'Demo Login', 
        "Demo login as {$role}",
        null
    );
    
    $redirect_url = $role . '/dashboard.php';
    if ($role === 'mahasiswa') {
        $redirect_url = 'users/dashboard.php';
    }

    jsonResponse([
        'success' => true,
        'message' => "Demo login as {$role} successful",
        'role' => $role,
        'redirect_url' => $redirect_url
    ]);
    
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Login failed: ' . $e->getMessage()
    ], 500);
}
?>