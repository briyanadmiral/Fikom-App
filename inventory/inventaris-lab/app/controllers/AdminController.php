<?php

class AdminController extends Controller {

    public function __construct() {
        // Cek apakah pengguna adalah admin, jika tidak, redirect
        if (!isset($_SESSION['app_user']) || $_SESSION['app_user']['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    public function index() {
        $data['judul'] = 'Dashboard Admin';
        $data['user'] = $_SESSION['app_user'];
        $id_prodi = $_SESSION['app_user']['id_prodi'];

        // Panggil model untuk data ringkasan
        $data['total_barang'] = $this->model('Barang_model')->getTotalBarangByProdi($id_prodi);
        $data['total_dipinjam'] = $this->model('Peminjaman_model')->getJumlahPeminjamanByStatus('Disetujui', $id_prodi);
        $data['total_diajukan'] = $this->model('Peminjaman_model')->getJumlahPeminjamanByStatus('Diajukan', $id_prodi);
        $data['total_terlambat'] = $this->model('Peminjaman_model')->getJumlahTerlambatByProdi($id_prodi);
        
        // Ambil 5 pengajuan terbaru
        $data['pengajuan_terbaru'] = $this->model('Peminjaman_model')->getPeminjamanByStatus('Diajukan', $id_prodi);

        // Menghitung jumlah user yang berstatus 'pending'
        $data['total_pending'] = count($this->model('User_model')->getPendingUsers());

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/dashboard', $data);
        $this->view('templates/footer');
    }

    public function verifikasi_user() {
        $data['judul'] = 'Student Management';
        $data['user'] = $_SESSION['app_user'];

        $data['pending'] = $this->model('User_model')->getPendingUsers();
        $data['approved'] = $this->model('User_model')->getApprovedUsers();

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/verifikasi_user', $data);
        $this->view('templates/footer');
    }

    public function setujui_user($id_user) {
        if (empty($id_user)) {
            header('Location: ' . BASE_URL . '/admin/verifikasi_user');
            exit;
        }

        if ($this->model('User_model')->approveUser($id_user) > 0) {
            if (class_exists('Flasher')) {
                Flasher::setFlash('Mahasiswa', 'berhasil diverifikasi.', 'success');
            }
        } else {
            if (class_exists('Flasher')) {
                Flasher::setFlash('Mahasiswa', 'gagal diverifikasi.', 'danger');
            }
        }
        
        header('Location: ' . BASE_URL . '/admin/verifikasi_user');
        exit;
    }

    public function tolak_user($id_user) {
        if (empty($id_user)) {
            header('Location: ' . BASE_URL . '/admin/verifikasi_user');
            exit;
        }

        if ($this->model('User_model')->tolakUser($id_user) > 0) {
            if (class_exists('Flasher')) {
                Flasher::setFlash('Pengajuan akses mahasiswa', 'berhasil ditolak dan dihapus.', 'success');
            }
        } else {
            if (class_exists('Flasher')) {
                Flasher::setFlash('Pengajuan akses mahasiswa', 'gagal ditolak.', 'danger');
            }
        }
        
        header('Location: ' . BASE_URL . '/admin/verifikasi_user');
        exit;
    }
}