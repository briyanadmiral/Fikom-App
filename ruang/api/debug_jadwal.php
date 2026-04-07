<?php
// api/debug_jadwal.php

// Set header ke teks biasa agar mudah dibaca
header('Content-Type: text/plain'); 
require_once '../config/database.php';

echo "Mulai skrip debug...\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "Koneksi database BERHASIL.\n\n";
    } else {
        echo "Koneksi database GAGAL.\n";
        die();
    }

    // --- Tes Query Pengajuan Peminjaman ---
    $sql_pinjam = "SELECT id, keperluan, tanggal_pinjam, jam_mulai, jam_selesai, status, ruangan_id
                   FROM pengajuan_peminjaman
                   WHERE (status = 'approved' OR status = 'pending')";

    echo "Menjalankan Query Pengajuan:\n$sql_pinjam\n\n";

    $stmt_pinjam = $db->prepare($sql_pinjam);
    $stmt_pinjam->execute();
    $bookings = $stmt_pinjam->fetchAll();

    echo "Hasil Query Pengajuan (data mentah):\n";
    print_r($bookings);

    echo "\n\n--------------------------------------\n\n";

    // --- Tes Query Mata Kuliah ---
    $sql_matkul = "SELECT id, nama_matkul, hari, jam_mulai, ruangan_id
                   FROM jadwal_matkul";

    echo "Menjalankan Query Mata Kuliah:\n$sql_matkul\n\n";

    $stmt_matkul = $db->prepare($sql_matkul);
    $stmt_matkul->execute();
    $matkuls = $stmt_matkul->fetchAll();

    echo "Hasil Query Mata Kuliah (data mentah):\n";
    print_r($matkuls);

    echo "\n\n...Skrip debug selesai.";

} catch (Exception $e) {
    echo "\n\n--- TERJADI ERROR ---\n";
    echo $e->getMessage();
}
?>