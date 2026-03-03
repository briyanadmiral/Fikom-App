<?php

// Gunakan 'use' statement untuk memanggil class dari library eksternal
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class BarangController extends Controller {

    public function __construct() {
        // Middleware untuk memastikan hanya admin yang bisa akses
        if (!isset($_SESSION['app_user']) || $_SESSION['app_user']['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    /**
     * Menampilkan halaman utama daftar barang.
     */
    public function index() {
        $data['judul'] = 'Manajemen Data Barang';
        $data['user'] = $_SESSION['app_user'];
        $data['barang'] = $this->model('Barang_model')->getAllBarangByProdi($_SESSION['app_user']['id_prodi']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/barang/index', $data);
        $this->view('templates/footer');
    }

    /**
     * Menampilkan form untuk menambah barang baru.
     */
    public function tambah() {
        $data['judul'] = 'Tambah Barang';
        $data['user'] = $_SESSION['app_user'];
        $data['jenis_barang'] = $this->model('Jenis_barang_model')->getAllJenis();

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/barang/tambah', $data);
        $this->view('templates/footer');
    }

    /**
     * Memproses dan menyimpan data barang baru.
     */
    public function store() {
        $namaFileFoto = null;
        if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_barang'];
            $allowedTypes = ['image/jpeg', 'image/png'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 2097152) { // 2MB
                $namaFileFoto = uniqid('brg_') . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                move_uploaded_file($file['tmp_name'], 'assets/uploads/barang/' . $namaFileFoto);
            }
        }
        $dataToSave = $_POST;
    $dataToSave['foto_barang'] = $namaFileFoto;

    try {
        $id_barang_baru = $this->model('Barang_model')->tambahDataBarang($dataToSave);
        if ($id_barang_baru) {
            $this->buatLog("Menambah data barang baru: " . $_POST['nama_barang']);
            $this->buatLogStok($id_barang_baru, 'Barang Baru', (int)$_POST['jumlah_total'], (int)$_POST['jumlah_total'], 'Stok awal');
            Flasher::setFlash('Berhasil', 'ditambahkan', 'success');
            header('Location: ' . BASE_URL . '/barang');
            exit;
        } else {
            throw new Exception("Gagal menyimpan data ke database.");
        }
    } catch (PDOException $e) {
        // Tangkap error spesifik dari database
        if ($e->getCode() == 23000) { // 23000 adalah kode untuk integrity constraint violation
            Flasher::setFlash('Gagal', 'ditambahkan. Kode Inventaris sudah ada.', 'danger');
        } else {
            Flasher::setFlash('Gagal', 'ditambahkan karena terjadi error database: ' . $e->getMessage(), 'danger');
        }
        // Redirect kembali ke form tambah
        header('Location: ' . BASE_URL . '/barang/tambah');
        exit;
    }
}

    /**
     * Menampilkan form untuk mengedit barang.
     */
    public function edit($id) {
        $data['judul'] = 'Edit Barang';
        $data['user'] = $_SESSION['app_user'];
        $data['barang'] = $this->model('Barang_model')->getBarangById($id);
        $data['jenis_barang'] = $this->model('Jenis_barang_model')->getAllJenis();

        if (!$data['barang']) {
            Flasher::setFlash('Gagal', 'data barang tidak ditemukan.', 'danger');
            header('Location: ' . BASE_URL . '/barang');
            exit;
        }

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/barang/edit', $data);
        $this->view('templates/footer');
    }

    /**
     * Memproses dan menyimpan perubahan data barang.
     */
public function update() {
    // Ambil data barang sebelum diubah (opsional, bisa dihapus jika tidak ada perbandingan lain)
    // $barang_lama = $this->model('Barang_model')->getBarangById($_POST['id_barang']);
    
    // Logika untuk upload foto baru (jika ada)
    $namaFileFoto = $_POST['foto_lama'];
    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_barang'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= 2097152) {
            if (!empty($namaFileFoto) && file_exists('assets/uploads/barang/' . $namaFileFoto)) {
                unlink('assets/uploads/barang/' . $namaFileFoto);
            }
            $namaFileFoto = uniqid('brg_') . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            move_uploaded_file($file['tmp_name'], 'assets/uploads/barang/' . $namaFileFoto);
        }
    }
    $dataToUpdate = $_POST;
    $dataToUpdate['foto_barang'] = $namaFileFoto;

    try {
        if ($this->model('Barang_model')->updateDataBarang($dataToUpdate) >= 0) {
            $this->buatLog("Mengedit detail barang: " . $_POST['nama_barang']);
            Flasher::setFlash('Berhasil', 'diupdate', 'success');
            header('Location: ' . BASE_URL . '/barang');
            exit;
        }
    } catch (PDOException $e) {
        // Tangkap error spesifik dari database
        if ($e->getCode() == 23000) {
            Flasher::setFlash('Gagal', 'diupdate. Kode Inventaris "' . $_POST['kode_inventaris'] . '" sudah digunakan oleh barang lain.', 'danger');
        } else {
            Flasher::setFlash('Gagal', 'diupdate karena terjadi error database: ' . $e->getMessage(), 'danger');
        }
        // Redirect kembali ke form edit yang sama
        header('Location: ' . BASE_URL . '/barang/edit/' . $_POST['id_barang']);
        exit;
    }
}

    /**
     * Memproses penghapusan barang (soft delete).
     */
    public function destroy($id) {
        $barang = $this->model('Barang_model')->getBarangById($id);
        if ($this->model('Barang_model')->softDelete($id) > 0) {
            $this->buatLog("Menghapus (soft delete) barang: " . $barang['nama_barang']);
            Flasher::setFlash('Berhasil', 'dipindahkan ke sampah', 'success');
        } else {
            Flasher::setFlash('Gagal', 'dihapus', 'danger');
        }
        header('Location: ' . BASE_URL . '/barang');
        exit;
    }

    /**
     * Menampilkan halaman cetak QR code untuk thermal printer.
     */
    public function cetakQR($id) {
        $data['barang'] = $this->model('Barang_model')->getBarangById($id);
        if (!$data['barang']) { die('Barang tidak ditemukan'); }
        $this->view('admin/barang/qr_print_template', $data);
    }
    
    /**
     * Memproses perubahan stok manual.
     */
    public function ubahStok() {
        $barang = $this->model('Barang_model')->getBarangById($_POST['id_barang']);
        $jumlah_lama = $barang['jumlah_total'];
        $jumlah_baru = $_POST['jumlah_total_baru'];
        $selisih = $jumlah_baru - $jumlah_lama;
        $stok_tersedia_baru = $barang['jumlah_tersedia'] + $selisih;

        if ($this->model('Barang_model')->updateStokBarang($_POST['id_barang'], $jumlah_baru, $selisih) > 0) {
            $this->buatLogStok($_POST['id_barang'], 'Ubah Stok Total', $selisih, $stok_tersedia_baru, $_POST['keterangan']);
            Flasher::setFlash('Stok barang', 'berhasil diubah.', 'success');
        } else {
            Flasher::setFlash('Stok barang', 'gagal diubah.', 'danger');
        }
        header('Location: ' . BASE_URL . '/barang');
        exit;
    }

public function uploadExcel() {
    require_once '../vendor/autoload.php';

    if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
        Flasher::setFlash('Gagal', 'Tidak ada file yang diunggah atau terjadi error.', 'danger');
        header('Location: ' . BASE_URL . '/barang');
        exit;
    }

    $file_mimes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    if (!in_array($_FILES['excelFile']['type'], $file_mimes)) {
        Flasher::setFlash('Gagal', 'File harus berformat .xlsx', 'danger');
        header('Location: ' . BASE_URL . '/barang');
        exit;
    }

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $spreadsheet = $reader->load($_FILES['excelFile']['tmp_name']);
    $sheetData = $spreadsheet->getActiveSheet()->toArray();

    $jenisMapping = $this->model('Jenis_barang_model')->getJenisMapping();
    $suksesCount = 0;
    $gagalCount = 0;
    $pesanGagal = [];

    // Loop mulai dari baris ke-2 untuk melewati header
    for ($i = 1; $i < count($sheetData); $i++) {
        $row = $sheetData[$i];
        
        // SESUAIKAN URUTAN PEMBACAAN KOLOM
        $kode_jenis         = strtoupper($row[0]);
        $bulan_perolehan    = $row[1];
        $tahun_perolehan    = $row[2];
        $nama_barang        = $row[3];
        $jumlah_total       = $row[4];
        $kondisi_barang     = $row[5];
        $deskripsi          = $row[6];

        if (empty($nama_barang) || empty($kode_jenis) || empty($jumlah_total) || empty($kondisi_barang)) {
            $gagalCount++;
            $pesanGagal[] = "Baris " . ($i + 1) . ": Data wajib tidak lengkap.";
            continue;
        }
        if (!isset($jenisMapping[$kode_jenis])) {
            $gagalCount++;
            $pesanGagal[] = "Baris " . ($i + 1) . ": Kode Jenis '" . $kode_jenis . "' tidak ditemukan.";
            continue;
        }

        // Persiapan Data untuk Disimpan
        $dataToSave = [
            'nama_barang'       => $nama_barang,
            'id_jenis'          => $jenisMapping[$kode_jenis],
            'jumlah_total'      => $jumlah_total,
            'deskripsi'         => $deskripsi,
            'status_kondisi'    => $kondisi_barang,
            'foto_barang'       => null,
            'kode_inventaris'   => $this->model('Barang_model')->generateKodeInventaris($jenisMapping[$kode_jenis], $bulan_perolehan, $tahun_perolehan)
        ];

        // Simpan ke database
        $id_barang_baru = $this->model('Barang_model')->tambahDataBarang($dataToSave);
        if ($id_barang_baru) {
            $this->buatLog("Menambah barang via Excel: " . $nama_barang);
            $this->buatLogStok($id_barang_baru, 'Barang Baru (Excel)', (int)$jumlah_total, (int)$jumlah_total, 'Stok awal dari import');
            $suksesCount++;
        } else {
            $gagalCount++;
            $pesanGagal[] = "Baris " . ($i + 1) . ": Gagal menyimpan ke database (mungkin kode inventaris duplikat).";
        }
    }

    Flasher::setFlash("Import Selesai", "$suksesCount data berhasil diimpor, $gagalCount data gagal.", 'info');
    if(!empty($pesanGagal)) $_SESSION['import_errors'] = $pesanGagal;

    header('Location: ' . BASE_URL . '/barang');
    exit;
}

    public function downloadTemplate() {
        require_once '../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // --- Sheet 1: Template Utama ---
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import');
        
        // Buat header kolom sesuai permintaan
        $sheet->setCellValue('A1', 'Kode Jenis (Wajib)');
        $sheet->setCellValue('B1', 'Bulan Perolehan (Wajib, angka 1-12)');
        $sheet->setCellValue('C1', 'Tahun Perolehan (Wajib, cth: 2024)');
        $sheet->setCellValue('D1', 'Nama Barang (Wajib)');
        $sheet->setCellValue('E1', 'Jumlah Total (Wajib)');
        $sheet->setCellValue('F1', 'Kondisi Barang (Wajib)');
        $sheet->setCellValue('G1', 'Deskripsi (Opsional)');
        
        // Beri komentar/petunjuk pada kolom Kondisi
        $sheet->getComment('F1')->getText()->createTextRun('Isi dengan: Baik, Rusak Ringan, atau Rusak Berat');

        // --- Sheet 2: Petunjuk Pengisian ---
        $petunjukSheet = $spreadsheet->createSheet();
        $petunjukSheet->setTitle('Petunjuk Kode Jenis');
        $petunjukSheet->setCellValue('A1', 'Daftar Kode Jenis yang Valid');
        $petunjukSheet->getStyle('A1')->getFont()->setBold(true);
        
        $jenisBarang = $this->model('Jenis_barang_model')->getAllJenis();
        $row = 2;
        $petunjukSheet->setCellValue('A'.$row, 'Kode');
        $petunjukSheet->setCellValue('B'.$row, 'Nama Jenis');
        foreach($jenisBarang as $jenis) {
            $row++;
            $petunjukSheet->setCellValue('A'.$row, $jenis['kode_jenis']);
            $petunjukSheet->setCellValue('B'.$row, $jenis['nama_jenis']);
        }

        // Kembali ke sheet pertama saat file dibuka
        $spreadsheet->setActiveSheetIndex(0);

        // Proses download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template-import-barang.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $filename .'"');
        $writer->save('php://output');
        exit;
    }
}