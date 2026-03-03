<?php

class UserController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['app_user'])) {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    public function index() {
        $data['judul'] = 'Dashboard';
        $data['user'] = $_SESSION['app_user'];
        $email = $_SESSION['app_user']['email'];

        // Panggil model untuk data ringkasan
        $data['total_dipinjam'] = $this->model('Peminjaman_model')->getJumlahPeminjamanByEmailAndStatus($email, 'Disetujui');
        $data['total_diajukan'] = $this->model('Peminjaman_model')->getJumlahPeminjamanByEmailAndStatus($email, 'Diajukan');
        
        // Ambil data peminjaman yang sedang aktif (status 'Disetujui')
        $data['peminjaman_aktif'] = $this->model('Peminjaman_model')->getPeminjamanAktifByEmail($email);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('user/dashboard', $data);
        $this->view('templates/footer');
    }

    public function lihatBarang() {
        $data['judul'] = 'Daftar Barang Tersedia';
        $data['user'] = $_SESSION['app_user'];
        
        // Ambil barang yang tersedia untuk prodi user yang sedang login
        $data['barang'] = $this->model('Barang_model')->getBarangTersediaByProdi($_SESSION['app_user']['id_prodi']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('user/barang/index', $data); // View baru untuk user
        $this->view('templates/footer');
    }
}