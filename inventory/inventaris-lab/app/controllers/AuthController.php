<?php

class AuthController extends Controller {
    
    public function index() {
        
        // 1. CEK SESSION DARI MAIN APP (GOOGLE LOGIN)
        if (!isset($_SESSION['user_email'])) {
            // Jika tidak ada session dari main app, tendang ke login utama
            header('Location: http://localhost/fikomapp/login.php');
            exit;
        }

        // Ambil data dari Session Utama
        $email = $_SESSION['user_email']; 
        $nama  = $_SESSION['user_name']; 

        // ------------------------------------------------------------------
        // LOGIKA PEMBENTUKAN 'app_user'
        // ------------------------------------------------------------------

        $userModel = $this->model('User_model');
        $registeredUser = $userModel->getUserByEmail($email);

        if ($registeredUser) {
            // SKENARIO 1: ADMIN / DOSEN / TENDIK (Terdaftar di DB Inventory)
            $_SESSION['app_user'] = [
                'id_user'    => $registeredUser['id_user'],
                'email'      => $registeredUser['email'],
                'nama'       => $registeredUser['nama'],
                'role'       => $registeredUser['role'], // 'admin' atau 'user'
                'id_prodi'   => $registeredUser['id_prodi'],
                'nama_prodi' => $registeredUser['nama_prodi']
            ];
        } else {
            // SKENARIO 2: MAHASISWA (Tidak terdaftar di DB Inventory)
            $id_prodi = null;
            $nama_prodi = null;
            
            // Ambil NIM dari email (sebelum @)
            $parts = explode('@', $email);
            $nim_full = $parts[0]; 
            
            // --- [PERBAIKAN LOGIC NIM DI SINI] ---
            // Cek apakah ada titik (.) di NIM?
            if (strpos($nim_full, '.') !== false) {
                // Format Lama: 23.k1.0015 (Ambil mulai index 3)
                $nim_code = substr($nim_full, 3, 2); 
            } else {
                // Format Baru (Punya Kamu): 23g40011 (Ambil mulai index 2)
                $nim_code = substr($nim_full, 2, 2); 
            }
            // -------------------------------------

            // Cek Kode Prodi SI (termasuk g4)
            if (in_array($nim_code, ['n1', 'n2', 'g4', 'n4'])) {
                $id_prodi = 1; 
                $nama_prodi = 'Sistem Informasi';
            } 
            // Cek Kode Prodi TI
            elseif (in_array($nim_code, ['k1', 'k2', 'k3', 'k4', 'k5'])) {
                $id_prodi = 2; 
                $nama_prodi = 'Teknik Informatika';
            }

            // Jika kode prodi tidak dikenali
            if (is_null($id_prodi)) {
                echo "<script>
                        alert('Akses Ditolak. Kode Prodi ($nim_code) tidak dikenali sistem Inventory.'); 
                        window.location='http://localhost/fikomapp/index.php';
                      </script>";
                exit;
            }

            // Buat session user mahasiswa sementara
            $_SESSION['app_user'] = [
                'email'      => $email,
                'nama'       => $nama,
                'role'       => 'user', 
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