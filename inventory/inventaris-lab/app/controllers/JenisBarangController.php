<?php

class JenisBarangController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['app_user']) || $_SESSION['app_user']['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    public function index() {
        $data['judul'] = 'Manajemen Jenis Barang';
        $data['user'] = $_SESSION['app_user'];
        $data['jenis_barang'] = $this->model('Jenis_barang_model')->getAllJenis();

        // PERBAIKAN ADA DI 3 BARIS INI:
        // Mengganti tanda titik (.) menjadi panah (->)
        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/jenis_barang/index', $data);
        $this->view('templates/footer');
    }

    public function store() {
        if ($this->model('Jenis_barang_model')->tambahDataJenis($_POST) > 0) {
            Flasher::setFlash('Jenis Barang', 'berhasil ditambahkan', 'success');
        } else {
            Flasher::setFlash('Jenis Barang', 'gagal ditambahkan', 'danger');
        }
        header('Location: ' . BASE_URL . '/jenisbarang');
        exit;
    }

    public function destroy($id) {
        if ($this->model('Jenis_barang_model')->hapusDataJenis($id) > 0) {
            Flasher::setFlash('Jenis Barang', 'berhasil dihapus', 'success');
        } else {
            Flasher::setFlash('Jenis Barang', 'gagal dihapus', 'danger');
        }
        header('Location: ' . BASE_URL . '/jenisbarang');
        exit;
    }
}