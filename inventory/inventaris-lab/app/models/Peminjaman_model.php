<?php
class Peminjaman_model {
    private $table = 'peminjaman';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function ajukanPeminjaman($data) {
        $id_barang = $data['id_barang'];
        $jumlah_pinjam = (int)$data['jumlah'];

        try {
            $this->db->beginTransaction();
            $this->db->query("UPDATE barang SET jumlah_tersedia = jumlah_tersedia - :jumlah WHERE id_barang = :id_barang AND jumlah_tersedia >= :jumlah");
            $this->db->bind('jumlah', $jumlah_pinjam);
            $this->db->bind('id_barang', $id_barang);
            $this->db->execute();

            if ($this->db->rowCount() == 0) {
                $this->db->rollBack();
                return 0;
            }

            $query = "INSERT INTO peminjaman (id_barang, email_peminjam, no_telp_peminjam, jumlah, tgl_pinjam, tgl_kembali, status) 
                      VALUES (:id_barang, :email_peminjam, :no_telp_peminjam, :jumlah, :tgl_pinjam, :tgl_kembali, 'Diajukan')";

            $this->db->query($query);
            $this->db->bind('id_barang', $id_barang);
            $this->db->bind('email_peminjam', $_SESSION['app_user']['email']);
            $this->db->bind('no_telp_peminjam', htmlspecialchars($data['no_telp']));
            $this->db->bind('jumlah', $jumlah_pinjam);
            $this->db->bind('tgl_pinjam', $data['tgl_pinjam']);
            $this->db->bind('tgl_kembali', $data['tgl_kembali']);
            $this->db->execute();
            $this->db->commit();
            return $this->db->rowCount();
        } catch (Exception $e) {
            $this->db->rollBack();
            return 0;
        }
    }

    public function getPeminjamanByStatus($status, $id_prodi) {
        $this->db->query("
            SELECT peminjaman.*, barang.nama_barang 
            FROM " . $this->table . "
            JOIN barang ON peminjaman.id_barang = barang.id_barang
            WHERE peminjaman.status = :status AND barang.id_prodi = :id_prodi
            ORDER BY peminjaman.tgl_pinjam ASC
        ");
        $this->db->bind('status', $status);
        $this->db->bind('id_prodi', $id_prodi);
        return $this->db->resultSet();
    }

    public function updateStatusPeminjaman($id, $status) {
        $this->db->query("UPDATE " . $this->table . " SET status = :status WHERE id_peminjaman = :id");
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function setujuiPeminjaman($data) {
    $id_peminjaman = $data['id_peminjaman'];
    $catatan = $data['catatan_pinjam'];
    $id_admin = $_SESSION['app_user']['id_user']; // Ambil ID admin dari session

    $query = "UPDATE " . $this->table . " SET 
                status = 'Disetujui', 
                catatan_pinjam = :catatan,
                approved_by = :id_admin 
              WHERE id_peminjaman = :id";
              
    $this->db->query($query);
    $this->db->bind('catatan', $catatan);
    $this->db->bind('id_admin', $id_admin);
    $this->db->bind('id', $id_peminjaman);
    $this->db->execute();
    
    return $this->db->rowCount();
}

    // METHOD BARU untuk menangani penolakan
    public function tolakPeminjaman($id_peminjaman) {
        $this->db->query("SELECT id_barang, jumlah FROM " . $this->table . " WHERE id_peminjaman = :id");
        $this->db->bind('id', $id_peminjaman);
        $peminjaman = $this->db->single();
        if (!$peminjaman) return 0;
        $this->db->query("UPDATE barang SET jumlah_tersedia = jumlah_tersedia + :jumlah WHERE id_barang = :id_barang");
        $this->db->bind('jumlah', $peminjaman['jumlah']);
        $this->db->bind('id_barang', $peminjaman['id_barang']);
        $this->db->execute();
        return $this->updateStatusPeminjaman($id_peminjaman, 'Ditolak');
    }

    public function getPeminjamanByEmail($email) {
        $this->db->query("
            SELECT peminjaman.*, barang.nama_barang, barang.kode_inventaris 
            FROM " . $this->table . "
            JOIN barang ON peminjaman.id_barang = barang.id_barang
            WHERE peminjaman.email_peminjam = :email
            ORDER BY peminjaman.tgl_pinjam DESC
        ");
        $this->db->bind('email', $email);
        return $this->db->resultSet();
    }

    public function prosesPengembalian($data) {
        $id_peminjaman = $data['id_peminjaman'];
        $catatan = $data['catatan_kembali'];

        // Langkah 1: Ambil data peminjaman untuk mendapatkan id_barang dan jumlah yang dipinjam.
        // Ini penting untuk tahu barang mana dan berapa banyak stok yang harus dikembalikan.
        $this->db->query("SELECT id_barang, jumlah FROM " . $this->table . " WHERE id_peminjaman = :id");
        $this->db->bind('id', $id_peminjaman);
        $peminjaman = $this->db->single();

        // Jika data peminjaman tidak ditemukan, hentikan proses untuk keamanan.
        if (!$peminjaman) {
            return 0;
        }
        
        // Langkah 2: Tambah kembali stok yang tersedia di tabel 'barang'.
        $this->db->query("UPDATE barang SET jumlah_tersedia = jumlah_tersedia + :jumlah WHERE id_barang = :id_barang");
        $this->db->bind('jumlah', $peminjaman['jumlah']);
        $this->db->bind('id_barang', $peminjaman['id_barang']);
        $this->db->execute();

        // Langkah 3: Ubah status peminjaman menjadi 'Selesai', catat tanggal kembali aktual, dan simpan catatan.
        $this->db->query("UPDATE " . $this->table . " SET status = 'Selesai', tgl_kembali_aktual = :tgl, catatan_kembali = :catatan WHERE id_peminjaman = :id");
        $this->db->bind('tgl', date('Y-m-d H:i:s'));
        $this->db->bind('catatan', $catatan);
        $this->db->bind('id', $id_peminjaman);
        $this->db->execute();

        // Kembalikan jumlah baris yang terpengaruh dari query terakhir sebagai tanda keberhasilan.
        return $this->db->rowCount();
    }

    // METHOD BARU untuk menghitung jumlah berdasarkan status
    public function getJumlahPeminjamanByStatus($status, $id_prodi) {
        $this->db->query("
            SELECT COUNT(p.id_peminjaman) as total 
            FROM peminjaman p 
            JOIN barang b ON p.id_barang = b.id_barang 
            WHERE p.status = :status AND b.id_prodi = :id_prodi
        ");
        $this->db->bind('status', $status);
        $this->db->bind('id_prodi', $id_prodi);
        return $this->db->single()['total'];
    }

    // METHOD BARU untuk menghitung yang terlambat
    public function getJumlahTerlambatByProdi($id_prodi) {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT COUNT(p.id_peminjaman) as total 
            FROM peminjaman p 
            JOIN barang b ON p.id_barang = b.id_barang 
            WHERE p.status = 'Disetujui' AND p.tgl_kembali < :today AND b.id_prodi = :id_prodi
        ");
        $this->db->bind('today', $today);
        $this->db->bind('id_prodi', $id_prodi);
        return $this->db->single()['total'];
    }

    // METHOD BARU untuk menghitung data spesifik user berdasarkan status
    public function getJumlahPeminjamanByEmailAndStatus($email, $status) {
        $this->db->query("SELECT COUNT(id_peminjaman) as total FROM " . $this->table . " WHERE email_peminjam = :email AND status = :status");
        $this->db->bind('email', $email);
        $this->db->bind('status', $status);
        return $this->db->single()['total'];
    }

    // METHOD BARU untuk mengambil data peminjaman aktif milik user
    public function getPeminjamanAktifByEmail($email) {
        $this->db->query("
            SELECT peminjaman.*, barang.nama_barang 
            FROM " . $this->table . "
            JOIN barang ON peminjaman.id_barang = barang.id_barang
            WHERE peminjaman.email_peminjam = :email AND peminjaman.status = 'Disetujui'
            ORDER BY peminjaman.tgl_kembali ASC
        ");
        $this->db->bind('email', $email);
        return $this->db->resultSet();
    }

    public function updateTanggalKembali($data) {
        $query = "UPDATE " . $this->table . " SET tgl_kembali = :tgl_kembali WHERE id_peminjaman = :id";
        $this->db->query($query);
        $this->db->bind('tgl_kembali', $data['tgl_kembali_baru']);
        $this->db->bind('id', $data['id_peminjaman']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getLaporanPeminjamanByDateRange($id_prodi, $tgl_mulai, $tgl_akhir) {
        $this->db->query("
            SELECT p.*, b.nama_barang, b.kode_inventaris
            FROM peminjaman p
            JOIN barang b ON p.id_barang = b.id_barang
            WHERE b.id_prodi = :id_prodi AND p.tgl_pinjam BETWEEN :tgl_mulai AND :tgl_akhir
            ORDER BY p.tgl_pinjam ASC
        ");
        $this->db->bind('id_prodi', $id_prodi);
        $this->db->bind('tgl_mulai', $tgl_mulai);
        $this->db->bind('tgl_akhir', $tgl_akhir . ' 23:59:59'); // Sertakan sampai akhir hari
        return $this->db->resultSet();
    }

    // METHOD BARU: Untuk Statistik Barang Terpopuler
    public function getTopBarangDipinjam($id_prodi, $bulan, $tahun) {
        $this->db->query("
            SELECT b.nama_barang, COUNT(p.id_peminjaman) as total_dipinjam
            FROM peminjaman p
            JOIN barang b ON p.id_barang = b.id_barang
            WHERE b.id_prodi = :id_prodi 
              AND MONTH(p.tgl_pinjam) = :bulan 
              AND YEAR(p.tgl_pinjam) = :tahun
            GROUP BY p.id_barang
            ORDER BY total_dipinjam DESC
            LIMIT 5
        ");
        $this->db->bind('id_prodi', $id_prodi);
        $this->db->bind('bulan', $bulan);
        $this->db->bind('tahun', $tahun);
        return $this->db->resultSet();
    }

    public function getAllPeminjamanByProdi($id_prodi) {
        $this->db->query("
            SELECT peminjaman.*, barang.nama_barang 
            FROM " . $this->table . "
            JOIN barang ON peminjaman.id_barang = barang.id_barang
            WHERE barang.id_prodi = :id_prodi
            ORDER BY peminjaman.tgl_pinjam DESC
        ");
        $this->db->bind('id_prodi', $id_prodi);
        return $this->db->resultSet();
    }

    // app/models/Peminjaman_model.php
    public function getPeminjamanById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id_peminjaman=:id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    // Tambahkan method baru ini
public function getTahunPeminjaman() {
    $this->db->query("SELECT DISTINCT YEAR(tgl_pinjam) as tahun FROM peminjaman ORDER BY tahun DESC");
    return $this->db->resultSet();
}

// Tambahkan method baru ini
public function getBulanTahunPeminjaman() {
    $this->db->query("SELECT DISTINCT YEAR(tgl_pinjam) as tahun, MONTH(tgl_pinjam) as bulan FROM peminjaman ORDER BY tahun DESC, bulan DESC");
    return $this->db->resultSet();
}
}