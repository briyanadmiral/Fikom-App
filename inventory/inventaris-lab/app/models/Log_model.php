<?php
class Log_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Mengambil semua data log aktivitas umum.
     * Diurutkan dari yang terbaru (DESC).
     */
    public function getAllAktivitasLogs() {
        // Query diurutkan berdasarkan 'waktu' secara menurun
        $this->db->query('SELECT * FROM log_aktivitas ORDER BY waktu DESC');
        return $this->db->resultSet();
    }
    
    /**
     * Mengambil semua data log perubahan stok.
     * Diurutkan dari yang terbaru (DESC).
     */
    public function getAllStokLogs() {
        $this->db->query("
            SELECT ls.*, b.nama_barang 
            FROM log_stok ls
            JOIN barang b ON ls.id_barang = b.id_barang
            ORDER BY ls.waktu DESC
        "); // <-- Query diurutkan berdasarkan 'waktu' secara menurun
        return $this->db->resultSet();
    }
}