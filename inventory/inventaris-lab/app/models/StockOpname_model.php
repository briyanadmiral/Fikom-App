<?php
class StockOpname_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function simpanHasilOpname($data_header, $data_detail) {
        // 1. Simpan data header ke tabel stock_opname
        $this->db->query("INSERT INTO stock_opname (id_prodi, tanggal_opname, dilakukan_oleh, status) VALUES (:id_prodi, :tanggal_opname, :dilakukan_oleh, 'Selesai')");
        $this->db->bind('id_prodi', $data_header['id_prodi']);
        $this->db->bind('tanggal_opname', $data_header['tanggal_opname']);
        $this->db->bind('dilakukan_oleh', $data_header['dilakukan_oleh']);
        $this->db->execute();

        // Ambil ID dari sesi opname yang baru saja disimpan
        $id_opname_baru = $this->db->lastInsertId();

        // 2. Simpan setiap item ke tabel stock_opname_detail
        foreach ($data_detail as $item) {
            $this->db->query("INSERT INTO stock_opname_detail (id_opname, id_barang, jumlah_sistem, jumlah_fisik, selisih, catatan) VALUES (:id_opname, :id_barang, :jumlah_sistem, :jumlah_fisik, :selisih, :catatan)");
            $this->db->bind('id_opname', $id_opname_baru);
            $this->db->bind('id_barang', $item['id_barang']);
            $this->db->bind('jumlah_sistem', $item['jumlah_sistem']);
            $this->db->bind('jumlah_fisik', $item['jumlah_fisik']);
            $this->db->bind('selisih', $item['selisih']);
            $this->db->bind('catatan', $item['catatan']);
            $this->db->execute();
        }

        return $id_opname_baru;
    }

    public function getRiwayatOpnameByProdi($id_prodi) {
        $this->db->query("SELECT * FROM stock_opname WHERE id_prodi = :id_prodi ORDER BY tanggal_opname DESC");
            $this->db->bind('id_prodi', $id_prodi);
            return $this->db->resultSet();
        }

        public function getOpnameHeaderById($id_opname) {
        $this->db->query("SELECT * FROM stock_opname WHERE id_opname = :id");
        $this->db->bind('id', $id_opname);
        return $this->db->single();
    }

    public function getOpnameDetailById($id_opname) {
        $this->db->query("
            SELECT sod.*, b.nama_barang, b.kode_inventaris 
            FROM stock_opname_detail sod
            JOIN barang b ON sod.id_barang = b.id_barang
            WHERE sod.id_opname = :id
        ");
        $this->db->bind('id', $id_opname);
        return $this->db->resultSet();
    }

    // METHOD BARU UNTUK MEMPROSES PENYESUAIAN STOK
    public function prosesPenyesuaianStok($id_opname) {
        // Ambil semua item detail dari sesi opname ini
        $detail_items = $this->getOpnameDetailById($id_opname);

        if (empty($detail_items)) {
            return 0; // Tidak ada item untuk diproses
        }

        // Mulai transaksi database untuk menjaga integritas data
        try {
            $this->db->beginTransaction();

            // Loop setiap item dan update stoknya di tabel 'barang'
            foreach ($detail_items as $item) {
                // Logika penyesuaian:
                // jumlah_total diatur sama dengan jumlah fisik.
                // jumlah_tersedia disesuaikan berdasarkan selisih. Ini penting agar barang yang sedang dipinjam tetap terhitung.
                $query = "UPDATE barang SET jumlah_total = :jumlah_fisik, jumlah_tersedia = jumlah_tersedia + :selisih WHERE id_barang = :id_barang";
                
                $this->db->query($query);
                $this->db->bind('jumlah_fisik', $item['jumlah_fisik']);
                $this->db->bind('selisih', $item['selisih']);
                $this->db->bind('id_barang', $item['id_barang']);
                $this->db->execute();
            }

            // Setelah semua item diupdate, ubah status opname menjadi 'Disesuaikan'
            $this->db->query("UPDATE stock_opname SET status = 'Disesuaikan' WHERE id_opname = :id_opname");
            $this->db->bind('id_opname', $id_opname);
            $this->db->execute();

            // Jika semua berhasil, commit transaksi
            $this->db->commit();
            return 1; // Mengembalikan 1 sebagai tanda sukses

        } catch (Exception $e) {
            // Jika ada error di tengah jalan, batalkan semua perubahan
            $this->db->rollBack();
            return 0; // Mengembalikan 0 sebagai tanda gagal
        }
    }
}