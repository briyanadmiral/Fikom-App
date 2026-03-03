<?php
session_start();

// 1. Cek Login Google dari Main App
if(!isset($_SESSION['logged_in'])){
    header('Location: login.php'); exit;
}

// 2. Koneksi Khusus ke Database Inventory (db_inventaris_lab)
// Kita butuh ini untuk cek apakah Dosen tersebut adalah Admin Lab atau bukan
$conn = mysqli_connect('localhost', 'root', '', 'fikomapp');

// Ambil data dari Session Main App
$email       = $_SESSION['user_email'];
$role_global = $_SESSION['role']; // 'mahasiswa', 'dosen', 'superadmin'
$program     = $_SESSION['program'] ?? '-'; // 'siega', 'informatika'

/* ---------------------------------------------------
   3. RESET SESSION INVENTORY (Sesuai Request)
   --------------------------------------------------- */
$_SESSION['users_siega'] = false;
$_SESSION['users_ti']    = false;
$_SESSION['admin_siega'] = false;
$_SESSION['admin_ti']    = false;

/* ---------------------------------------------------
   4. LOGIKA PENGISIAN SESSION (Revisi Pak Andre)
   --------------------------------------------------- */

// --- SKENARIO A: MAHASISWA ---
// Mahasiswa otomatis jadi 'users' berdasarkan prodi mereka
if($role_global == 'mahasiswa') {
    if($program == 'siega') {
        $_SESSION['users_siega'] = true;
    } 
    elseif($program == 'informatika') {
        $_SESSION['users_ti'] = true;
    }
}

// --- SKENARIO B: DOSEN / SUPERADMIN ---
// Cek ke tabel 'users' di db_inventaris_lab
elseif($role_global == 'dosen' || $role_global == 'superadmin') {
    
    // Cek apakah email ini terdaftar di database inventory?
    $query  = "SELECT role, id_dosen FROM dosen WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $data   = mysqli_fetch_assoc($result);

    if($data) {
        // Jika terdaftar, cek apakah dia ADMIN atau USER biasa
        $role_db  = $data['role'];     // 'admin' atau 'user'
        $id_prodi = $data['id_prodi']; // 1 = SI (Siega), 2 = TI

        if($role_db == 'admin') {
            // Jika Admin
            if($id_prodi == 1) $_SESSION['admin_siega'] = true;
            if($id_prodi == 2) $_SESSION['admin_ti']    = true;
            
            // Biasanya admin juga otomatis bisa pinjam (users)
            if($id_prodi == 1) $_SESSION['users_siega'] = true;
            if($id_prodi == 2) $_SESSION['users_ti']    = true;

        } else {
            // Jika Dosen User Biasa
            if($id_prodi == 1) $_SESSION['users_siega'] = true;
            if($id_prodi == 2) $_SESSION['users_ti']    = true;
        }
    } else {
        // Jika Dosen tidak terdaftar di DB Inventory, 
        // Kita beri akses 'users' default (peminjam) berdasarkan logika prodi (jika ada)
        // Atau biarkan false semua (tidak bisa akses). 
        // Di sini saya set false agar aman. Dosen wajib didaftarkan di DB Inventory dulu.
    }
}

/* ---------------------------------------------------
   5. VALIDASI & REDIRECT
   --------------------------------------------------- */
// Cek apakah minimal punya satu akses?
if($_SESSION['users_siega'] || $_SESSION['users_ti'] || $_SESSION['admin_siega'] || $_SESSION['admin_ti']) {
    
    // Stempel Validasi Tambahan (Opsional, biar aman)
    $_SESSION['inv_validated'] = true;

    // Redirect ke Public Inventory
    header("Location: inventory/inventaris-lab/public/");
    exit;
} else {
    echo "<script>
            alert('Akses Ditolak: Anda tidak memiliki akses ke Sistem Inventory (Prodi/Role tidak sesuai).');
            window.location='index.php';
          </script>";
}
?>