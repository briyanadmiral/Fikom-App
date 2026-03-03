<?php

class Controller {
    public function view($view, $data = []) {
        // Cek apakah file view ada
        if (file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        } else {
            die('View does not exist.');
        }
    }

    public function model($model) {
        // Cek apakah file model ada
        if (file_exists('../app/models/' . $model . '.php')) {
            require_once '../app/models/' . $model . '.php';
            return new $model;
        } else {
            die('Model does not exist.');
        }
    }

    // app/core/Controller.php
    protected function buatLog($aktivitas) {
        if (isset($_SESSION['app_user']['email'])) {
            $email = $_SESSION['app_user']['email'];
            $db_log = new Database;
            
            $query = "INSERT INTO log_aktivitas (email_user, waktu, aktivitas) VALUES (:email, :waktu, :aktivitas)";
            $db_log->query($query);
            $db_log->bind('email', $email);
            $db_log->bind('waktu', date('Y-m-d H:i:s'));
            $db_log->bind('aktivitas', $aktivitas);
            $db_log->execute();
        }
    }

    protected function buatLogStok($id_barang, $aktivitas, $jumlah_ubah, $stok_akhir, $keterangan = '') {
        if (isset($_SESSION['app_user']['email'])) {
            $email = $_SESSION['app_user']['email'];
            $db_log = new Database;
            
            $query = "INSERT INTO log_stok (id_barang, waktu, aktivitas, jumlah_ubah, stok_akhir, keterangan, email_user) 
                    VALUES (:id_barang, :waktu, :aktivitas, :jumlah_ubah, :stok_akhir, :keterangan, :email)";
            
            $db_log->query($query);
            $db_log->bind('id_barang', $id_barang);
            $db_log->bind('waktu', date('Y-m-d H:i:s'));
            $db_log->bind('aktivitas', $aktivitas);
            $db_log->bind('jumlah_ubah', $jumlah_ubah);
            $db_log->bind('stok_akhir', $stok_akhir);
            $db_log->bind('keterangan', $keterangan);
            $db_log->bind('email', $email);
            $db_log->execute();
        }
    }

}