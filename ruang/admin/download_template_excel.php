<?php
require_once '../config/database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$database = new Database();
$db = $database->getConnection();

// Fetch ruangan
$ruangan_list = [];
try {
    $stmt_ruangan = $db->query("SELECT id, nama_ruangan FROM ruangan WHERE status = 'active'");
    $ruangan_list = $stmt_ruangan->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Template Jadwal');

// Set Header
$headers = [
    'Ruangan', 'Kode Matkul', 'Nama Matkul', 'Dosen', 'Kelas', 'Hari', 
    'Jam Mulai', 'Jam Selesai', 'Semester', 'Tahun Ajaran', 
    'Tanggal Mulai (DD-MM-YYYY)', 'Tanggal Selesai (DD-MM-YYYY)'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Data Validation - Ruangan (Sheet kedua untuk referensi)
$refSheet = $spreadsheet->createSheet();
$refSheet->setTitle('Referensi');
$ruanganData = [];
$row = 1;
foreach ($ruangan_list as $r) {
    $val = $r['id'] . ' - ' . $r['nama_ruangan'];
    $ruanganData[] = $val;
    $refSheet->setCellValue('A' . $row, $val);
    $row++;
}

// Data Validation - Hari
$hariData = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$row = 1;
foreach ($hariData as $h) {
    $refSheet->setCellValue('B' . $row, $h);
    $row++;
}

// Data Validation - Jam (per 30 menit)
$jamData = [];
$start = strtotime('06:00');
$end = strtotime('21:00');
while ($start <= $end) {
    $jamData[] = date('H:i', $start);
    $start = strtotime('+30 minutes', $start);
}
$row = 1;
foreach ($jamData as $j) {
    $refSheet->setCellValue('C' . $row, $j);
    $row++;
}

// Hide referensi sheet
$spreadsheet->getSheetByName('Referensi')->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

// Apply Data Validation to a range (e.g., up to 100 rows)
$mainSheet = $spreadsheet->getSheetByName('Template Jadwal');
$rowCount = 100;

for ($i = 2; $i <= $rowCount; $i++) {
    // Ruangan (Kolom A)
    if (!empty($ruanganData)) {
        $validation = $mainSheet->getCell('A' . $i)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input Error');
        $validation->setError('Pilih ruangan dari dropdown.');
        $validation->setFormula1('Referensi!$A$1:$A$' . count($ruanganData));
    }

    // Hari (Kolom F)
    $validation = $mainSheet->getCell('F' . $i)->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validation->setAllowBlank(true);
    $validation->setShowInputMessage(true);
    $validation->setShowErrorMessage(true);
    $validation->setShowDropDown(true);
    $validation->setErrorTitle('Input Error');
    $validation->setError('Pilih hari dari dropdown.');
    $validation->setFormula1('Referensi!$B$1:$B$' . count($hariData));

    // Jam Mulai & Selesai (Kolom G dan H)
    foreach (['G', 'H'] as $colJam) {
        $validation = $mainSheet->getCell($colJam . $i)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input Error');
        $validation->setError('Pilih jam dari dropdown.');
        $validation->setFormula1('Referensi!$C$1:$C$' . count($jamData));
    }
}

// Format Tanggal (Kolom K & L) to text, so that user forcing format DD-MM-YYYY is correct
$mainSheet->getStyle('K2:L' . $rowCount)
    ->getNumberFormat()
    ->setFormatCode(NumberFormat::FORMAT_TEXT);

// Active sheet back to main
$spreadsheet->setActiveSheetIndex(0);

// Set header for output file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Jadwal.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
