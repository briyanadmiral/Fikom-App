<?php

class User_model {
    private $table = 'users';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getUserByEmail($email) {
        $this->db->query('SELECT users.*, program_studi.nama_prodi FROM ' . $this->table . ' JOIN program_studi ON users.id_prodi = program_studi.id_prodi WHERE email=:email');
        $this->db->bind('email', $email);
        return $this->db->single();
    }

    public function getPendingUsers() {
        $this->db->query('SELECT users.*, program_studi.nama_prodi FROM ' . $this->table . ' JOIN program_studi ON users.id_prodi = program_studi.id_prodi WHERE status_inventaris = :status');
        $this->db->bind('status', 'pending');
        return $this->db->resultSet();
    }

    public function getApprovedUsers() {
        $this->db->query('SELECT users.*, program_studi.nama_prodi FROM ' . $this->table . ' JOIN program_studi ON users.id_prodi = program_studi.id_prodi WHERE status_inventaris = :status');
        $this->db->bind('status', 'approved');
        return $this->db->resultSet();
    }

    public function approveUser($id_user) {
        $this->db->query('UPDATE ' . $this->table . ' SET status_inventaris = :status WHERE id_user = :id_user');
        $this->db->bind('status', 'approved');
        $this->db->bind('id_user', $id_user);
        
        $this->db->execute();
        return $this->db->rowCount(); 
    }

    public function tolakUser($id_user) {
        // Menghapus data dari antrean agar mahasiswa bisa mendaftar ulang jika ada kesalahan
        $this->db->query('DELETE FROM ' . $this->table . ' WHERE id_user = :id_user');
        $this->db->bind('id_user', $id_user);
        
        $this->db->execute();
        return $this->db->rowCount(); 
    }

    public function registerUser($data) {
        $query = "INSERT INTO " . $this->table . " (nama, email, nim, whatsapp, role, id_prodi, status_inventaris) 
                  VALUES (:nama, :email, :nim, :whatsapp, 'mahasiswa', :id_prodi, 'pending')";
        
        $this->db->query($query);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('nim', $data['nim']);
        $this->db->bind('whatsapp', $data['whatsapp']);
        $this->db->bind('id_prodi', $data['id_prodi']); 
        
        $this->db->execute();
        return $this->db->rowCount();
    }
}