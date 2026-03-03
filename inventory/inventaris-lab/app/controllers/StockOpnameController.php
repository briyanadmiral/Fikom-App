<?php
class StockOpnameController extends Controller {
    public function __construct() {
        // Fitur ini hanya untuk admin
        if (!isset($_SESSION['app_user']) || $_SESSION['app_user']['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    // Halaman utama, menampilkan riwayat stock opname
    public function index() {
        $data['judul'] = 'Riwayat Stock Opname';
        $data['user'] = $_SESSION['app_user'];
        
        // Panggil model untuk mengambil data riwayat
        $data['riwayat'] = $this->model('StockOpname_model')->getRiwayatOpnameByProdi($_SESSION['app_user']['id_prodi']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/stock_opname/index', $data);
        $this->view('templates/footer');
    }

    // Halaman untuk memulai proses stock opname
    public function mulai() {
        $data['judul'] = 'Mulai Stock Opname';
        $data['user'] = $_SESSION['app_user'];
        
        // Ambil semua data barang untuk prodi admin
        $data['barang'] = $this->model('Barang_model')->getAllBarangByProdi($_SESSION['app_user']['id_prodi']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/stock_opname/form', $data);
        $this->view('templates/footer');
    }

    public function simpan() {
        // Siapkan data header untuk tabel stock_opname
        $data_header = [
            'id_prodi' => $_SESSION['app_user']['id_prodi'],
            'tanggal_opname' => date('Y-m-d'),
            'dilakukan_oleh' => $_SESSION['app_user']['email']
        ];

        // Siapkan data detail untuk setiap barang
        $data_detail = [];
        foreach ($_POST['jumlah_sistem'] as $id_barang => $jumlah_sistem) {
            $jumlah_fisik = $_POST['jumlah_fisik'][$id_barang];
            $data_detail[] = [
                'id_barang' => $id_barang,
                'jumlah_sistem' => $jumlah_sistem,
                'jumlah_fisik' => $jumlah_fisik,
                'selisih' => $jumlah_fisik - $jumlah_sistem,
                'catatan' => $_POST['catatan'][$id_barang]
            ];
        }
        
        // Panggil model untuk menyimpan data
        $id_opname_baru = $this->model('StockOpname_model')->simpanHasilOpname($data_header, $data_detail);

        $id_opname_baru = $this->model('StockOpname_model')->simpanHasilOpname($data_header, $data_detail);
        if ($id_opname_baru > 0) {
            $this->buatLog("Menyimpan hasil stock opname baru (ID: " . $id_opname_baru . ")"); // LOG
            Flasher::setFlash('Stock Opname', 'berhasil disimpan.', 'success'); 
        } else {
            Flasher::setFlash('Stock Opname', 'gagal disimpan.', 'danger');
            header('Location: ' . BASE_URL . '/stockopname');
        }
        exit;
    }

    public function detail($id_opname) {
        $data['judul'] = 'Detail Hasil Stock Opname';
        $data['user'] = $_SESSION['app_user'];
        
        $opnameModel = $this->model('StockOpname_model');
        $data['header'] = $opnameModel->getOpnameHeaderById($id_opname);
        $data['detail'] = $opnameModel->getOpnameDetailById($id_opname);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/stock_opname/detail', $data); // View baru
        $this->view('templates/footer');
    }

    public function sesuaikanStok($id_opname) {
        // Ambil detail opname sebelum diproses
        $detail_opname = $this->model('StockOpname_model')->getOpnameDetailById($id_opname);

        if ($this->model('StockOpname_model')->prosesPenyesuaianStok($id_opname) > 0) {
            // Log Aktivitas: Mencatat bahwa proses penyesuaian telah dilakukan
            $this->buatLog("Menyesuaikan stok sistem dari hasil opname ID: " . $id_opname);

            // Log Stok: Mencatat setiap perubahan stok per barang
            foreach ($detail_opname as $item) {
                if ($item['selisih'] != 0) { // Hanya catat jika ada perubahan
                    $barang_setelah_sesuai = $this->model('Barang_model')->getBarangById($item['id_barang']);
                    $stok_akhir = $barang_setelah_sesuai['jumlah_tersedia'];
                    $this->buatLogStok($item['id_barang'], 'Stock Opname', $item['selisih'], $stok_akhir, 'Penyesuaian dari Opname ID: ' . $id_opname);
                }
            }
            
            Flasher::setFlash('Stok Sistem', 'berhasil disesuaikan.', 'success');
        } else {
            Flasher::setFlash('Stok Sistem', 'gagal disesuaikan.', 'danger');
        }
        header('Location: ' . BASE_URL . '/stockopname');
        exit;
    }
}