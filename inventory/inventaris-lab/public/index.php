<?php
// Cek Ekstensi PHP yang dibutuhkan (PDO & pdo_mysql)
if (!class_exists('PDO')) {
    die("<div style='font-family:sans-serif;padding:30px;text-align:center;background:#f8fafc;color:#1e293b;border-radius:12px;border:1px solid #e2e8f0;max-width:600px;margin:10% auto;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);'>
        <h2 style='color:#ef4444;margin-bottom:10px;'>&#9888; Ekstensi PHP 'PDO' Tidak Aktif</h2>
        <p>Sistem ini membutuhkan ekstensi <strong>PDO</strong> dan <strong>pdo_mysql</strong> agar dapat berjalan dengan normal di server hosting.</p>
        <p style='color:#64748b;font-size:14px;margin-bottom:20px;'>Silakan masuk ke cPanel Anda &rarr; pilih menu <strong>Select PHP Version</strong> &rarr; lalu centang/aktifkan ekstensi <strong>pdo</strong> dan <strong>pdo_mysql</strong>.</p>
        <a href='../../../index.php' style='display:inline-block;padding:10px 20px;background:#4f46e5;color:white;text-decoration:none;border-radius:6px;font-weight:600;'>&larr; Kembali ke Dashboard</a>
    </div>");
}

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