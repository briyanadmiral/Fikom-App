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
}