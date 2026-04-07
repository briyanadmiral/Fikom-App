<?php

class AuthController extends Controller {
    
public function index() {
        
        // 1. CEK SESSION DARI MAIN APP (GOOGLE LOGIN)
        if (!isset($_SESSION['user_email'])) {
            header('Location: http://localhost/fikomapp/login.php');
            exit;
        }

        // Ambil data dari Session Utama
        $email = $_SESSION['user_email']; 
        $nama  = $_SESSION['user_name']; 
        $role_utama = $_SESSION['role'] ?? 'user'; // 'mahasiswa', 'dosen', 'superadmin'

        // ------------------------------------------------------------------
        // LOGIKA PEMBENTUKAN 'app_user'
        // ------------------------------------------------------------------

        $userModel = $this->model('User_model');
        $registeredUser = $userModel->getUserByEmail($email);

        if ($registeredUser) {
            // SKENARIO 1: ADMIN / DOSEN TERDAFTAR DI DB INVENTORY LOKAL
            $_SESSION['app_user'] = [
                'id_user'    => $registeredUser['id_user'],
                'email'      => $registeredUser['email'],
                'nama'       => $registeredUser['nama'],
                'role'       => $registeredUser['role'], // 'admin' atau 'user'
                'id_prodi'   => $registeredUser['id_prodi'],
                'nama_prodi' => $registeredUser['nama_prodi'] ?? 'Tidak Diketahui'
            ];
        } else {
            // SKENARIO 2: TIDAK ADA DI DB INVENTORY (Mahasiswa ATAU Dosen Jalur VIP)
            $id_prodi = null;
            $nama_prodi = null;
            
            // A. Jika dia Dosen/Superadmin (Jalur VIP dari fikomapp)
            if ($role_utama == 'dosen' || $role_utama == 'superadmin') {
                $role_inventory = 'user'; // Default sebagai peminjam

                // Cek apakah dia masuk sebagai Admin SI
                if (isset($_SESSION['admin_siega']) && $_SESSION['admin_siega'] === true) {
                    $id_prodi = 1;
                    $nama_prodi = 'Sistem Informasi';
                    $role_inventory = 'admin'; // <--- INI KUNCI KEKUASAANNYA
                } 
                // Cek apakah dia masuk sebagai Admin TI
                elseif (isset($_SESSION['admin_ti']) && $_SESSION['admin_ti'] === true) {
                    $id_prodi = 2;
                    $nama_prodi = 'Teknik Informatika';
                    $role_inventory = 'admin'; // <--- INI KUNCI KEKUASAANNYA
                } 
                // Cek jika dia cuma user/dosen biasa
                elseif (isset($_SESSION['users_siega']) && $_SESSION['users_siega'] === true) {
                    $id_prodi = 1;
                    $nama_prodi = 'Sistem Informasi';
                } 
                elseif (isset($_SESSION['users_ti']) && $_SESSION['users_ti'] === true) {
                    $id_prodi = 2;
                    $nama_prodi = 'Teknik Informatika';
                }
            }
            // B. Jika dia Mahasiswa
            else {
                // Ambil NIM dari email (sebelum @)
                $parts = explode('@', $email);
                $nim_full = $parts[0]; 
                
                // Cek apakah ada titik (.) di NIM?
                if (strpos($nim_full, '.') !== false) {
                    $nim_code = substr($nim_full, 3, 2); 
                } else {
                    $nim_code = substr($nim_full, 2, 2); 
                }

                // Cek Kode Prodi SI
                if (in_array($nim_code, ['n1', 'n2', 'g4', 'n4'])) {
                    $id_prodi = 1; 
                    $nama_prodi = 'Sistem Informasi';
                } 
                // Cek Kode Prodi TI
                elseif (in_array($nim_code, ['k1', 'k2', 'k3', 'k4', 'k5'])) {
                    $id_prodi = 2; 
                    $nama_prodi = 'Teknik Informatika';
                }
            }

            // Jika setelah dicek ternyata prodi tetap tidak diketahui
            if (is_null($id_prodi)) {
                $kode_tampil = isset($nim_code) ? $nim_code : 'Unknown';
                echo "<script>
                        alert('Akses Ditolak. Data Prodi ($kode_tampil) tidak dapat diidentifikasi.'); 
                        window.location='http://localhost/fikomapp/index.php';
                      </script>";
                exit;
            }

            // Buat session user sementara
            $_SESSION['app_user'] = [
                'id_user'    => null, 
                'email'      => $email,
                'nama'       => $nama,
                'role'       => isset($role_inventory) ? $role_inventory : 'user', // Gunakan variabel tadi
                'id_prodi'   => $id_prodi,
                'nama_prodi' => $nama_prodi
            ];
        }

        // ------------------------------------------------------------------
        // REDIRECT KE DASHBOARD
        // ------------------------------------------------------------------
        if ($_SESSION['app_user']['role'] === 'admin') {
            header('Location: ' . BASE_URL . '/admin/index');
            exit;
        } else {
            header('Location: ' . BASE_URL . '/user/index');
            exit;
        }
    }
    
    public function logout() {
        // Mulai session biar bisa akses $_SESSION
        if (!session_id()) session_start();

        // 1. HAPUS SESSION SPESIFIK INVENTORY
        unset($_SESSION['admin_siega']);
        unset($_SESSION['admin_ti']);
        unset($_SESSION['users_siega']);
        unset($_SESSION['users_ti']);

        // 2. HAPUS SESSION INTERNAL MVC
        unset($_SESSION['app_user']);
        unset($_SESSION['inv_validated']);

        // 3. REDIRECT KEMBALI KE DASHBOARD UTAMA FIKOMAPP
        header('Location: http://localhost/fikomapp/index.php'); 
        exit;
    }
}