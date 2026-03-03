<?php

// Gunakan 'use' statement untuk memanggil class dari library eksternal
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class LaporanController extends Controller {

    public function __construct() {
        // Memastikan hanya admin yang bisa mengakses controller ini
        if (!isset($_SESSION['app_user']) || $_SESSION['app_user']['role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/logout');
            exit;
        }
    }

    /**
     * Method default, mengarahkan ke laporan stok untuk menghindari error.
     */
    public function index() {
        header('Location: ' . BASE_URL . '/laporan/stok');
        exit;
    }

    /**
     * Menampilkan halaman Laporan Stok Barang.
     */
    public function stok() {
        $data['judul'] = 'Laporan Stok Barang';
        $data['user'] = $_SESSION['app_user'];
        $data['barang'] = $this->model('Barang_model')->getAllBarangByProdi($_SESSION['app_user']['id_prodi']);

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/laporan/stok', $data);
        $this->view('templates/footer');
    }

public function peminjaman() {
    $data['judul'] = 'Laporan Peminjaman & Statistik';
    $data['user'] = $_SESSION['app_user'];
    $id_prodi = $_SESSION['app_user']['id_prodi'];

    // Mengambil daftar TAHUN dan BULAN yang tersedia dari database
    $data['periode_tersedia'] = $this->model('Peminjaman_model')->getBulanTahunPeminjaman();

    $data['laporan_peminjaman'] = null;
    $data['statistik_barang'] = null;
    $data['filter_peminjaman'] = $_POST;
    $data['filter_statistik'] = $_POST;

    if (isset($_POST['submit_peminjaman'])) {
        $data['laporan_peminjaman'] = $this->model('Peminjaman_model')->getLaporanPeminjamanByDateRange($id_prodi, $_POST['tgl_mulai'], $_POST['tgl_akhir']);
    }

    if (isset($_POST['submit_statistik'])) {
        $data['statistik_barang'] = $this->model('Peminjaman_model')->getTopBarangDipinjam($id_prodi, $_POST['bulan'], $_POST['tahun']);
    }

    $this->view('templates/header', $data);
    $this->view('templates/sidebar', $data);
    $this->view('admin/laporan/peminjaman', $data);
    $this->view('templates/footer');
}

    /**
     * Memproses export Laporan Stok ke format Excel (.xlsx).
     */
    public function exportExcel() {
        require_once '../vendor/autoload.php';

        $barang = $this->model('Barang_model')->getAllBarangByProdi($_SESSION['app_user']['id_prodi']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Laporan Stok Barang');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Kode Inventaris');
        $sheet->setCellValue('C3', 'Nama Barang');
        $sheet->setCellValue('D3', 'Jenis');
        $sheet->setCellValue('E3', 'Total');
        $sheet->setCellValue('F3', 'Tersedia');
        $sheet->setCellValue('G3', 'Kondisi');

        $row = 4;
        $no = 1;
        foreach($barang as $item) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $item['kode_inventaris']);
            $sheet->setCellValue('C'.$row, $item['nama_barang']);
            $sheet->setCellValue('D'.$row, $item['nama_jenis']);
            $sheet->setCellValue('E'.$row, $item['jumlah_total']);
            $sheet->setCellValue('F'.$row, $item['jumlah_tersedia']);
            $sheet->setCellValue('G'.$row, $item['status_kondisi']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan-stok-barang-' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $filename .'"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Memproses export Laporan Stok ke format PDF.
     */
    public function exportPdf() {
        require_once '../vendor/autoload.php';
        $data['barang'] = $this->model('Barang_model')->getAllBarangByProdi($_SESSION['app_user']['id_prodi']);
        $data['prodi'] = $_SESSION['app_user']['nama_prodi'];
        
        ob_start();
        $this->view('admin/laporan/pdf_template', $data);
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $dompdf->stream('laporan-stok-barang-' . date('Ymd') . '.pdf', ["Attachment" => false]);
    }
}