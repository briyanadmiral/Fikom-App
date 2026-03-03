<?php

class Barang_model {
    private $table = 'barang';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Mengambil semua data barang berdasarkan program studi admin yang login.
     * @param int $id_prodi ID program studi dari admin.
     * @return array Data barang.
     */
    // Ganti isi method getAllBarangByProdi
    public function getAllBarangByProdi($id_prodi) {
        $this->db->query("
            SELECT barang.*, jenis_barang.nama_jenis 
            FROM " . $this->table . "
            JOIN jenis_barang ON barang.id_jenis = jenis_barang.id_jenis
            WHERE barang.id_prodi = :id_prodi AND barang.deleted_at IS NULL
        "); // <-- Perubahan di sini
        $this->db->bind('id_prodi', $id_prodi);
        return $this->db->resultSet();
    }

// BUAT METHOD BARU
public function softDelete($id) {
        $query = "UPDATE " . $this->table . " SET deleted_at = :deleted_at WHERE id_barang = :id";
        $this->db->query($query);
        $this->db->bind('deleted_at', date('Y-m-d H:i:s'));
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function generateKodeInventaris($id_jenis, $bulan, $tahun) {
        // 1. Dapatkan kode jenis dari ID-nya
        $this->db->query("SELECT kode_jenis FROM jenis_barang WHERE id_jenis = :id");
        $this->db->bind(':id', $id_jenis);
        $kodeJenis = $this->db->single()['kode_jenis'];

        // 2. Dapatkan kode prodi dari session
        $prodiPrefix = ($_SESSION['app_user']['id_prodi'] == 1) ? 'SI' : 'TI';

        // 3. Ubah bulan menjadi romawi
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bulanRomawi = $romanMonths[$bulan - 1];
        
        // 4. Buat prefix kode untuk query nomor urut
        $prefix = $kodeJenis . '/' . $prodiPrefix . '/' . $bulanRomawi . '/' . $tahun . '/';
        $this->db->query("SELECT MAX(kode_inventaris) as max_code FROM " . $this->table . " WHERE kode_inventaris LIKE :prefix");
        $this->db->bind(':prefix', $prefix . '%');
        $result = $this->db->single();
        
        $lastNum = (int) substr($result['max_code'], -3);
        $newNum = $lastNum + 1;
        
        return $prefix . sprintf('%03d', $newNum);
    }

    public function tambahDataBarang($data) {
        // Tambahkan kolom foto_barang ke query
        $query = "INSERT INTO barang (kode_inventaris, nama_barang, id_jenis, id_prodi, deskripsi, jumlah_total, jumlah_tersedia, status_kondisi, foto_barang) 
                VALUES (:kode_inventaris, :nama_barang, :id_jenis, :id_prodi, :deskripsi, :jumlah_total, :jumlah_tersedia, :status_kondisi, :foto_barang)";

        $this->db->query($query);
        $this->db->bind('kode_inventaris', $data['kode_inventaris']);
        $this->db->bind('nama_barang', htmlspecialchars($data['nama_barang']));
        $this->db->bind('id_jenis', $data['id_jenis']);
        $this->db->bind('id_prodi', $_SESSION['app_user']['id_prodi']);
        $this->db->bind('deskripsi', htmlspecialchars($data['deskripsi']));
        $this->db->bind('jumlah_total', (int)$data['jumlah_total']);
        $this->db->bind('jumlah_tersedia', (int)$data['jumlah_total']);
        $this->db->bind('status_kondisi', htmlspecialchars($data['status_kondisi']));
        // Bind nama file foto
        $this->db->bind('foto_barang', $data['foto_barang']); 

        $this->db->execute();
    if ($this->db->rowCount() > 0) {
        return $this->db->lastInsertId(); // Kembalikan ID
    }
    return false;
    }

    // app/controllers/BarangController.php

// ... (method destroy) ...

    public function edit($id) {
        $data['judul'] = 'Edit Barang';
        $data['user'] = $_SESSION['app_user'];
        
        $data['barang'] = $this->model('Barang_model')->getBarangById($id);
        $data['jenis_barang'] = $this->model('Jenis_barang_model')->getAllJenis();

        $this->view('templates/header', $data);
        $this->view('templates/sidebar', $data);
        $this->view('admin/barang/edit', $data); // View baru: edit.php
        $this->view('templates/footer');
    }

    public function update() {
        // Logika update mirip dengan store, dengan beberapa tambahan
        $namaFileFoto = $_POST['foto_lama']; // Ambil nama foto lama

        // Cek jika ada upload foto baru
        if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] === UPLOAD_ERR_OK) {
            // Logika validasi dan upload seperti di method store()
            $file = $_FILES['foto_barang'];
            $allowedTypes = ['image/jpeg', 'image/png'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                // Hapus foto lama jika ada
                if (!empty($namaFileFoto) && file_exists('assets/uploads/barang/' . $namaFileFoto)) {
                    unlink('assets/uploads/barang/' . $namaFileFoto);
                }
                // Simpan foto baru
                $namaFileFoto = uniqid('brg_') . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                move_uploaded_file($file['tmp_name'], 'assets/uploads/barang/' . $namaFileFoto);
            }
        }

        $dataToUpdate = $_POST;
        $dataToUpdate['foto_barang'] = $namaFileFoto;

        if ($this->model('Barang_model')->updateDataBarang($dataToUpdate) > 0) {
            Flasher::setFlash('Berhasil', 'diupdate', 'success');
        } else {
            Flasher::setFlash('Gagal', 'diupdate', 'danger');
        }
        header('Location: ' . BASE_URL . '/barang');
        exit;
    }

    // app/models/Barang_model.php

// ... (method hardDelete) ...

     // FUNGSI UNTUK MENGAMBIL 1 BARANG BERDASARKAN ID
    public function getBarangById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id_barang = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }
    
    // FUNGSI UNTUK MENGUPDATE DATA
    public function updateDataBarang($data) {
    $query = "UPDATE barang SET 
                kode_inventaris = :kode_inventaris, -- TAMBAHKAN INI
                nama_barang = :nama_barang,
                id_jenis = :id_jenis,
                status_kondisi = :status_kondisi,
                foto_barang = :foto_barang
              WHERE id_barang = :id_barang";
    
    $this->db->query($query);
    $this->db->bind('kode_inventaris', $data['kode_inventaris']); // TAMBAHKAN INI
    $this->db->bind('nama_barang', htmlspecialchars($data['nama_barang']));
    $this->db->bind('id_jenis', $data['id_jenis']);
    $this->db->bind('status_kondisi', htmlspecialchars($data['status_kondisi']));
    $this->db->bind('foto_barang', $data['foto_barang']);
    $this->db->bind('id_barang', $data['id_barang']);

    $this->db->execute();
    return $this->db->rowCount();
}

    // Ganti method ini
    public function getBarangTersediaByProdi($id_prodi) {
        // HAPUS KONDISI 'AND jumlah_tersedia > 0'
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id_prodi = :id_prodi');
        $this->db->bind('id_prodi', $id_prodi);
        return $this->db->resultSet();
    }

    // METHOD BARU
    public function getTotalBarangByProdi($id_prodi) {
        $this->db->query('SELECT COUNT(id_barang) as total FROM ' . $this->table . ' WHERE id_prodi = :id_prodi');
        $this->db->bind('id_prodi', $id_prodi);
        return $this->db->single()['total'];
    }

    // app/models/Barang_model.php
    public function updateStokBarang($id_barang, $jumlah_baru, $selisih) {
        // Sesuaikan jumlah total dan jumlah tersedia berdasarkan selisih
        $query = "UPDATE barang SET jumlah_total = :jumlah_baru, jumlah_tersedia = jumlah_tersedia + :selisih WHERE id_barang = :id_barang";
        
        $this->db->query($query);
        $this->db->bind('jumlah_baru', (int)$jumlah_baru);
        $this->db->bind('selisih', (int)$selisih);
        $this->db->bind('id_barang', $id_barang);

        $this->db->execute();
        return $this->db->rowCount();
    }
}