<?php
class Jenis_barang_model {
    private $table = 'jenis_barang';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllJenis() {
        $this->db->query('SELECT * FROM ' . $this->table . ' ORDER BY nama_jenis ASC');
        return $this->db->resultSet();
    }
    public function tambahDataJenis($data) {
        $query = "INSERT INTO jenis_barang (nama_jenis, kode_jenis) VALUES (:nama_jenis, :kode_jenis)";
        $this->db->query($query);
        $this->db->bind('nama_jenis', htmlspecialchars($data['nama_jenis']));
        $this->db->bind('kode_jenis', htmlspecialchars($data['kode_jenis']));
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusDataJenis($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_jenis = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Tambahkan method baru ini
    public function getJenisMapping() {
        $allJenis = $this->getAllJenis();
        $mapping = [];
        foreach ($allJenis as $jenis) {
            $mapping[strtoupper($jenis['kode_jenis'])] = $jenis['id_jenis'];
        }
        return $mapping;
    }
}