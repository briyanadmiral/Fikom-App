<?php
// config/database.php - Database Configuration
// Konfigurasi koneksi database untuk Sentralisasi Ruangan FIKOM

class Database {
    private $host = 'localhost';
    private $db_name = 'sentralisasi_ruangan_fikom';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Fungsi helper untuk mengecek koneksi database
function testDatabaseConnection() {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful!";
        return true;
    } else {
        echo "❌ Database connection failed!";
        return false;
    }
}

// Session management functions
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    syncUserSession();
}

function syncUserSession() {
    // 1. Cek apakah ada session dari GIS Login utama (login.php pusat)
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_email'])) {
        $email = $_SESSION['user_email'];
        
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            try {
                // Cari user di tabel lokal
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                $orig_role = $_SESSION['role'] ?? 'mahasiswa';
                
                // Mapping role GIS ke Enum Database: 'admin', 'dosen', 'mahasiswa'
                $db_role = 'mahasiswa';
                if ($orig_role === 'superadmin' || $orig_role === 'admin') {
                    $db_role = 'admin';
                } elseif ($orig_role === 'dosen') {
                    $db_role = 'dosen';
                }

                if (!$user) {
                    // Jika tidak ada di tabel modul ruangan, insert (Auto-Registrasi)
                    $insert_query = "INSERT INTO users (email, nama, role, nim_nip, jurusan, status) VALUES (?, ?, ?, ?, ?, 'active')";
                    $stmt_insert = $db->prepare($insert_query);
                    $stmt_insert->execute([
                        $email,
                        $_SESSION['user_name'] ?? 'Guest',
                        $db_role,
                        $_SESSION['nim'] ?? $_SESSION['nip'] ?? '-', // nim_nip
                        $_SESSION['program'] ?? $_SESSION['jurusan'] ?? '-', // jurusan
                    ]);
                    $userId = $db->lastInsertId();
                } else {
                    $userId = $user['id'];
                }
                
                // Set session lokal modul ruangan agar kompatibel (FORCE OVERWRITE)
                $_SESSION['user_id'] = (int)$userId;
                $_SESSION['nama'] = $_SESSION['user_name'] ?? ($user['nama'] ?? 'Pengguna');
                $_SESSION['email'] = $email;
                $_SESSION[$db_role === 'admin' ? 'admin' : 'users'] = true; 
                
            } catch (PDOException $e) {
                // Error log silently
                error_log("Sync user error: " . $e->getMessage());
                // Fallback jika sync gagal tapi user_id masih berupa email
                if (!is_numeric($_SESSION['user_id'] ?? '')) {
                    $_SESSION['user_id'] = 0; // Prevent email being used as integer
                }
            }
        }
    }
}

function checkSessionRole($required_roles = []) {
    startSession();
    
    if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
        $user_roles[] = 'admin';
    }
    if (isset($_SESSION['users']) && $_SESSION['users'] === true) {
        $user_roles[] = 'users';
    }
    
    if (empty($user_roles)) {
        return false; // No active session
    }
    
    if (empty($required_roles)) {
        return $user_roles; // Return all active roles
    }
    
    // Check if user has any of the required roles
    foreach ($required_roles as $role) {
        if (in_array($role, $user_roles)) {
            return true;
        }
    }
    
    return false;
}

function getUserInfo() {
    startSession();
    return [
        'is_admin' => (isset($_SESSION['admin']) && $_SESSION['admin'] === true) || ($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['role'] ?? '') === 'superadmin',
        'is_users' => (isset($_SESSION['users']) && $_SESSION['users'] === true) || ($_SESSION['role'] ?? '') === 'mahasiswa' || ($_SESSION['role'] ?? '') === 'dosen' || ($_SESSION['role'] ?? '') === 'user',
        'user_id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? $_SESSION['user_email'] ?? null,
        'nama' => $_SESSION['nama'] ?? $_SESSION['user_name'] ?? 'Pengguna',
        'role' => $_SESSION['role'] ?? null
    ];
}

function logActivity($user_id, $aktivitas, $detail = null, $ruangan_id = null) {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        try {
            $query = "INSERT INTO log_aktivitas 
                     (user_id, ruangan_id, aktivitas, detail, ip_address, user_agent) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                (int)$user_id,
                $ruangan_id,
                $aktivitas,
                $detail,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch(PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
}

// Response helper functions
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirectTo($url) {
    header("Location: $url");
    exit;
}
?>