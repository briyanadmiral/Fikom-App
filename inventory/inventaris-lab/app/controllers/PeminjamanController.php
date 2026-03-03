<?php
class PeminjamanController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['app_user'])) {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    public function index() {
        // Halaman ini khusus untuk admin
        if ($_SESSION['app_user']['role'] !== 'admin') {
            header('Location: ' . BASE_URL);
            exit;
        }

        $data['judul'] = 'Persetujuan Peminjaman';
        $data['user'] = $_SESSION['app_user'];
        
        // Ambil data peminjaman yang statusnya 'Diajukan' untuk prodi admin
        $data['peminjaman'] = $this->model('Peminjaman_model')->getPeminjamanByStatus('Diajukan', $_SESSION['app_user']['id_prodi']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/peminjaman/index', $data); // View baru
        $this->view('templates/footer');
    }

    public function ajukan() {
        $id_barang = $_POST['id_barang'];
        $jumlah_pinjam = (int)$_POST['jumlah'];
        
        if ($this->model('Peminjaman_model')->ajukanPeminjaman($_POST) > 0) {
            $barang_setelah_pinjam = $this->model('Barang_model')->getBarangById($id_barang);
            $stok_akhir = $barang_setelah_pinjam['jumlah_tersedia'];
            
            // HANYA panggil buatLogStok
            $this->buatLogStok($id_barang, 'Peminjaman Diajukan', -$jumlah_pinjam, $stok_akhir, 'Stok dipesan oleh ' . $_SESSION['app_user']['email']);
            
            Flasher::setFlash('Pengajuan Peminjaman', 'berhasil dikirim.', 'success');
        } else {
            Flasher::setFlash('Pengajuan Peminjaman', 'gagal dikirim. Stok mungkin tidak mencukupi.', 'danger');
        }
        header('Location: ' . BASE_URL . '/user/lihatBarang');
        exit;
    }


    public function setujui() { // Hapus parameter $id_peminjaman
        if ($_SESSION['app_user']['role'] !== 'admin') { header('Location: ' . BASE_URL); exit; }

        // Proses data dari $_POST
        if ($this->model('Peminjaman_model')->setujuiPeminjaman($_POST) > 0) {
            $this->buatLog("Menyetujui peminjaman ID: " . $_POST['id_peminjaman']);
            Flasher::setFlash('Pengajuan', 'berhasil disetujui.', 'success');
        } else {
            Flasher::setFlash('Pengajuan', 'gagal disetujui. Stok mungkin tidak mencukupi.', 'danger');
        }
        header('Location: ' . BASE_URL . '/peminjaman');
        exit;
    }

    public function tolak($id_peminjaman) {
    $peminjaman = $this->model('Peminjaman_model')->getPeminjamanById($id_peminjaman);
    if ($peminjaman) {
        if ($this->model('Peminjaman_model')->tolakPeminjaman($id_peminjaman) > 0) {
            $barang_setelah_tolak = $this->model('Barang_model')->getBarangById($peminjaman['id_barang']);
            $stok_akhir = $barang_setelah_tolak['jumlah_tersedia'];
            
            // HANYA panggil buatLogStok
            $this->buatLogStok($peminjaman['id_barang'], 'Peminjaman Ditolak', +$peminjaman['jumlah'], $stok_akhir, 'Stok dikembalikan dari pesanan ' . $peminjaman['email_peminjam']);
            
            Flasher::setFlash('Pengajuan', 'telah ditolak.', 'info');
        } else {
            Flasher::setFlash('Pengajuan', 'gagal diproses.', 'danger');
        }
    header('Location: ' . BASE_URL . '/peminjaman');
        exit;
    }
}
    // METHOD BARU UNTUK HALAMAN USER
    public function saya() {
        // Halaman ini khusus untuk user
        if ($_SESSION['app_user']['role'] !== 'user') {
            header('Location: ' . BASE_URL);
            exit;
        }

        $data['judul'] = 'Riwayat Peminjaman Saya';
        $data['user'] = $_SESSION['app_user'];
        
        // Ambil data peminjaman berdasarkan email user yang login
        $data['peminjaman'] = $this->model('Peminjaman_model')->getPeminjamanByEmail($_SESSION['app_user']['email']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('user/peminjaman/saya', $data); // View baru
        $this->view('templates/footer');
    }

    public function riwayat() {
        if ($_SESSION['app_user']['role'] !== 'admin') { header('Location: ' . BASE_URL); exit; }

        $data['judul'] = 'Riwayat Peminjaman';
        $data['user'] = $_SESSION['app_user'];
        $data['peminjaman'] = $this->model('Peminjaman_model')->getAllPeminjamanByProdi($_SESSION['app_user']['id_prodi']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/peminjaman/riwayat', $data); // View baru
        $this->view('templates/footer');
    }

    // METHOD BARU UNTUK ADMIN KONFIRMASI PENGEMBALIAN
   public function konfirmasiKembali() { // Hapus parameter
    if ($_SESSION['app_user']['role'] !== 'admin') { header('Location: ' . BASE_URL); exit; }
    $id_peminjaman = $_POST['id_peminjaman']; // Ambil dari POST
    $peminjaman = $this->model('Peminjaman_model')->getPeminjamanById($id_peminjaman);
    if ($peminjaman) {
        if ($this->model('Peminjaman_model')->prosesPengembalian($_POST) > 0) {
            $barang_setelah_kembali = $this->model('Barang_model')->getBarangById($peminjaman['id_barang']);
            $stok_akhir = $barang_setelah_kembali['jumlah_tersedia'];

            // HANYA panggil buatLogStok
            $this->buatLogStok($peminjaman['id_barang'], 'Pengembalian Dikonfirmasi', +$peminjaman['jumlah'], $stok_akhir, 'Barang kembali dari ' . $peminjaman['email_peminjam']);

            Flasher::setFlash('Barang', 'telah berhasil dikembalikan.', 'success');
        } else {
            Flasher::setFlash('Barang', 'gagal diproses.', 'danger');
        }
            header('Location: ' . BASE_URL . '/peminjaman/riwayat');
        exit;
    }
}

    public function updateTanggal() {
        if ($_SESSION['app_user']['role'] !== 'admin') { header('Location: ' . BASE_URL); exit; }

        if ($this->model('Peminjaman_model')->updateTanggalKembali($_POST) > 0) {
            Flasher::setFlash('Tanggal Pengembalian', 'berhasil diubah.', 'success');
        } else {
            Flasher::setFlash('Tanggal Pengembalian', 'gagal diubah.', 'danger');
        }
        header('Location: ' . BASE_URL . '/peminjaman');
        exit;
    }
}