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

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/dashboard', $data);
        $this->view('templates/footer');
    }
}