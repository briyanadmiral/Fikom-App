<?php
// config/database.php - Database Configuration
// Konfigurasi koneksi database untuk Sentralisasi Ruangan FIKOM

class Database
{
    private $host     = 'localhost';
    private $db_name  = 'fike8938_fikom_ruang';
    private $username = 'fike8938_fikom_app';
    private $password = 'fikom#12345';
    public  $conn;

    public function getConnection()
    {
        $this->conn = null;

        if (!class_exists('PDO')) {
            error_log("[Ruang DB] PDO extension is not enabled on this server.");
            return null;
        }

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );
        } catch (Exception $e) {
            error_log("[Ruang DB] Connection error: " . $e->getMessage()
                . " | host=" . $this->host
                . " | db=" . $this->db_name
                . " | user=" . $this->username);
        }

        return $this->conn;
    }
}

// Fungsi helper untuk mengecek koneksi database
function testDatabaseConnection()
{
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
function startSession()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    syncUserSession();
}

function syncUserSession()
{
    // Cek apakah ada session dari login utama (login.php pusat)
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_email'])) {
        $email     = $_SESSION['user_email'];
        $orig_role = $_SESSION['role'] ?? 'mahasiswa';

        // Mapping role GIS ke Enum Database: 'admin', 'dosen', 'mahasiswa'
        $db_role = 'mahasiswa';
        if ($orig_role === 'superadmin' || $orig_role === 'admin') {
            $db_role = 'admin';
        } elseif ($orig_role === 'dosen') {
            $db_role = 'dosen';
        }

        // Set session role SEBELUM DB operations
        // Memastikan user bisa akses meski DB sedang bermasalah
        $_SESSION['email'] = $email;
        $_SESSION['nama']  = $_SESSION['user_name'] ?? 'Pengguna';
        if ($db_role === 'admin') {
            $_SESSION['admin'] = true;
        } else {
            $_SESSION['users'] = true;
        }

        // Sync ke database ruang (untuk user_id dan auto-registrasi)
        $database = new Database();
        $db = $database->getConnection();

        if ($db) {
            try {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user) {
                    // Auto-registrasi jika belum ada
                    $stmt_insert = $db->prepare(
                        "INSERT INTO users (email, nama, role, nim_nip, jurusan, status) VALUES (?, ?, ?, ?, ?, 'active')"
                    );
                    $stmt_insert->execute([
                        $email,
                        $_SESSION['user_name'] ?? 'Guest',
                        $db_role,
                        $_SESSION['nim'] ?? $_SESSION['nip'] ?? '-',
                        $_SESSION['program'] ?? $_SESSION['jurusan'] ?? '-',
                    ]);
                    $userId = $db->lastInsertId();
                } else {
                    $userId = $user['id'];
                    // Update role jika berubah
                    if ($user['role'] !== $db_role) {
                        $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$db_role, $userId]);
                    }
                }

                $_SESSION['user_id'] = (int) $userId;

            } catch (PDOException $e) {
                error_log("[Ruang] Sync user error: " . $e->getMessage());
                if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
                    $_SESSION['user_id'] = 0;
                }
            }
        } else {
            // DB tidak tersedia, gunakan fallback user_id
            if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
                $_SESSION['user_id'] = 0;
            }
        }
    }
}

function checkSessionRole($required_roles = [])
{
    startSession();

    $user_roles = [];
    if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
        $user_roles[] = 'admin';
    }
    if (isset($_SESSION['users']) && $_SESSION['users'] === true) {
        $user_roles[] = 'users';
    }

    if (empty($user_roles)) {
        return false;
    }

    if (empty($required_roles)) {
        return $user_roles;
    }

    foreach ($required_roles as $role) {
        if (in_array($role, $user_roles)) {
            return true;
        }
    }

    return false;
}

function getUserInfo()
{
    startSession();
    return [
        'is_admin' => (isset($_SESSION['admin']) && $_SESSION['admin'] === true)
                      || ($_SESSION['role'] ?? '') === 'admin'
                      || ($_SESSION['role'] ?? '') === 'superadmin',
        'is_users' => (isset($_SESSION['users']) && $_SESSION['users'] === true)
                      || in_array($_SESSION['role'] ?? '', ['mahasiswa', 'dosen', 'user']),
        'user_id'  => $_SESSION['user_id'] ?? null,
        'email'    => $_SESSION['email'] ?? $_SESSION['user_email'] ?? null,
        'nama'     => $_SESSION['nama'] ?? $_SESSION['user_name'] ?? 'Pengguna',
        'role'     => $_SESSION['role'] ?? null
    ];
}

function logActivity($user_id, $aktivitas, $detail = null, $ruangan_id = null)
{
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        try {
            $stmt = $db->prepare(
                "INSERT INTO log_aktivitas (user_id, ruangan_id, aktivitas, detail, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                (int) $user_id,
                $ruangan_id,
                $aktivitas,
                $detail,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
}

function jsonResponse($data, $status_code = 200)
{
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirectTo($url)
{
    header("Location: $url");
    exit;
}