<?php
// Mulai session jika belum ada
if (!session_id()) {
    session_start();
}

// Panggil file konfigurasi
require_once '../config/config.php';

// Panggil kelas inti (App/Router)
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/core/Flasher.php';

// Inisialisasi aplikasi
$app = new App();