<?php

class LogController extends Controller {

    public function __construct() {
        // Memastikan hanya admin yang bisa mengakses seluruh fitur di controller ini
        if (!isset($_SESSION['app_user']) || $_SESSION['app_user']['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    /**
     * Method default, mengarahkan ke halaman log aktivitas untuk menghindari error.
     */
    public function index() {
        header('Location: ' . BASE_URL . '/log/aktivitas');
        exit;
    }

    /**
     * Menampilkan halaman Log Aktivitas Umum.
     */
    public function aktivitas() {
        $data['judul'] = 'Log Aktivitas';
        $data['user'] = $_SESSION['app_user'];
        $data['logs'] = $this->model('Log_model')->getAllAktivitasLogs();

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/log/index', $data);
        $this->view('templates/footer');
    }

    /**
     * Menampilkan halaman Log Perubahan Stok.
     */
    public function stok() {
        $data['judul'] = 'Log Perubahan Stok';
        $data['user'] = $_SESSION['app_user'];
        $data['logs'] = $this->model('Log_model')->getAllStokLogs();

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/log/stok', $data);
        $this->view('templates/footer');
    }
}