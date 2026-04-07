<?php
require_once '../config/database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$database = new Database();
$db = $database->getConnection();

if (isset($_POST["import"])) {
    $fileName = $_FILES["file"]["tmp_name"];
    
    if ($_FILES["file"]["size"] > 0) {
        try {
            // Load Excel file using PhpSpreadsheet
            $spreadsheet = IOFactory::load($fileName);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow(); 

            $db->beginTransaction(); // Mulai transaksi agar data aman

            $sql = "INSERT INTO jadwal_matkul 
                    (ruangan_id, kode_matkul, nama_matkul, dosen, kelas, hari, jam_mulai, jam_selesai, semester, tahun_ajaran, tanggal_mulai, tanggal_selesai) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);

            // Row 1 is header, start from row 2
            for ($row = 2; $row <= $highestRow; $row++) {
                // Get data from columns
                $ruangan_raw = $sheet->getCell('A' . $row)->getValue();
                
                // If cell is completely empty and no more rows, stop
                if (empty($ruangan_raw) && empty($sheet->getCell('B' . $row)->getValue())) {
                    continue; 
                }

                // Extract ID ruangan from pattern (ID - Nama Ruangan), handling if they just input ID
                $ruangan_parts = explode(' - ', $ruangan_raw);
                $ruangan_id = trim($ruangan_parts[0]);

                $kode_matkul = trim($sheet->getCell('B' . $row)->getValue());
                $nama_matkul = trim($sheet->getCell('C' . $row)->getValue());
                $dosen = trim($sheet->getCell('D' . $row)->getValue());
                $kelas = trim($sheet->getCell('E' . $row)->getValue());
                $hari = strtolower(trim($sheet->getCell('F' . $row)->getValue()));
                
                // Get formatted time and date since PhpSpreadsheet might read them as Excel dates
                $jam_mulai = trim($sheet->getCell('G' . $row)->getFormattedValue());
                $jam_selesai = trim($sheet->getCell('H' . $row)->getFormattedValue());
                
                $semester = trim($sheet->getCell('I' . $row)->getValue());
                $tahun_ajaran = trim($sheet->getCell('J' . $row)->getValue());
                
                // Format tanggal might be text (DD-MM-YYYY) or excel date format
                // So fetch formated value and convert to YYYY-MM-DD
                $tanggal_mulai_raw = trim($sheet->getCell('K' . $row)->getFormattedValue());
                $tanggal_selesai_raw = trim($sheet->getCell('L' . $row)->getFormattedValue());

                // Pastikan format kode matkul tidak kosong (syarat skip baris kosong)
                if (empty($kode_matkul)) continue;

                // Coba parsir DD-MM-YYYY menjadi YYYY-MM-DD untuk insert ke DB
                $tgl_mulai_db = $tanggal_mulai_raw;
                if(DateTime::createFromFormat('d-m-Y', $tanggal_mulai_raw) !== false) {
                    $tgl_mulai_db = DateTime::createFromFormat('d-m-Y', $tanggal_mulai_raw)->format('Y-m-d');
                } else if(DateTime::createFromFormat('m/d/Y', $tanggal_mulai_raw) !== false) {
                    $tgl_mulai_db = DateTime::createFromFormat('m/d/Y', $tanggal_mulai_raw)->format('Y-m-d');
                }

                $tgl_selesai_db = $tanggal_selesai_raw;
                if(DateTime::createFromFormat('d-m-Y', $tanggal_selesai_raw) !== false) {
                    $tgl_selesai_db = DateTime::createFromFormat('d-m-Y', $tanggal_selesai_raw)->format('Y-m-d');
                } else if(DateTime::createFromFormat('m/d/Y', $tanggal_selesai_raw) !== false) {
                    $tgl_selesai_db = DateTime::createFromFormat('m/d/Y', $tanggal_selesai_raw)->format('Y-m-d');
                }

                $stmt->execute([
                    $ruangan_id,
                    $kode_matkul,
                    $nama_matkul,
                    $dosen,
                    $kelas,
                    $hari,
                    $jam_mulai,
                    $jam_selesai,
                    $semester,
                    $tahun_ajaran,
                    $tgl_mulai_db,
                    $tgl_selesai_db
                ]);
            }

            $db->commit();
            echo "<script>alert('Data Berhasil Diimport!'); window.location='lihat_jadwal.php';</script>";
            
        } catch (Exception $e) {
            $db->rollBack();
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location='import_matkul.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Jadwal</title>
</head>
<body style="font-family: sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

<div class="card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 500px;">
    <h2 style="margin-top: 0; color: #333;">📁 Upload Jadwal Kuliah</h2>
    <p style="color: #666; line-height: 1.5;">Pastikan file disimpan dalam format <b>Excel (.xlsx)</b>. Unduh dan gunakan template yang disediakan untuk mempermudah pengisian data.</p>
    
    <div style="margin-bottom: 20px;">
        <a href="download_template_excel.php" style="background-color: #fca311; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block; font-size: 0.9rem;">
            ⬇️ Download Template Excel
        </a>
    </div>

    <div style="background: #eef2f5; padding: 15px; border-left: 4px solid #1a73e8; margin-bottom: 20px; font-size: 0.85rem; color: #555;">
        <strong>Catatan Penting:</strong><br>
        - Gunakan menu dropdown untuk memilih Ruangan, Hari, dan Jam.<br>
        - Format tanggal adalah <strong>DD-MM-YYYY</strong> (contoh: 25-08-2024).
    </div>

    <form action="" method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
        <input type="file" name="file" accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required style="border: 1px solid #ccc; padding: 10px; border-radius: 4px;">
        <div style="display: flex; gap: 10px;">
            <a href="lihat_jadwal.php" style="flex: 1; text-align: center; background: #ddd; color: #333; text-decoration: none; padding: 12px; border-radius: 4px; font-weight: bold;">Batal</a>
            <button type="submit" name="import" style="flex: 2; background: #4CAF50; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1rem;">
                Mulai Import Excel
            </button>
        </div>
    </form>
</div>

</body>
</html>