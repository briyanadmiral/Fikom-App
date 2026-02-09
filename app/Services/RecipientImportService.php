<?php

namespace App\Services;

use App\Models\RecipientImport;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RecipientImportService
{
    protected array $requiredColumns = ['nama_penerima'];

    protected array $optionalColumns = ['jabatan', 'npp', 'email', 'instansi'];

    /**
     * Parse uploaded file and return preview data
     */
    public function parseFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->parseCsv($file);
        }

        return $this->parseExcel($file);
    }

    /**
     * Parse CSV file
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $rows = [];
        $errors = [];
        $handle = fopen($file->getRealPath(), 'r');

        // Read header
        $header = fgetcsv($handle);
        $header = array_map('strtolower', array_map('trim', $header));

        // Validate required columns
        foreach ($this->requiredColumns as $col) {
            if (! in_array($col, $header)) {
                fclose($handle);
                throw new \Exception("Kolom wajib '{$col}' tidak ditemukan di file.");
            }
        }

        $rowNum = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            $row = array_combine($header, $data);
            $rowData = $this->processRow($row, $rowNum, $errors);
            if ($rowData) {
                $rows[] = $rowData;
            }
        }

        fclose($handle);

        return [
            'rows' => $rows,
            'errors' => $errors,
            'total' => count($rows),
        ];
    }

    /**
     * Parse Excel file
     */
    protected function parseExcel(UploadedFile $file): array
    {
        $rows = [];
        $errors = [];

        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        if (empty($data)) {
            throw new \Exception('File Excel kosong.');
        }

        // Read header
        $header = array_map('strtolower', array_map('trim', $data[0]));

        // Validate required columns
        foreach ($this->requiredColumns as $col) {
            if (! in_array($col, $header)) {
                throw new \Exception("Kolom wajib '{$col}' tidak ditemukan di file.");
            }
        }

        for ($i = 1; $i < count($data); $i++) {
            $row = array_combine($header, $data[$i]);
            $rowData = $this->processRow($row, $i + 1, $errors);
            if ($rowData) {
                $rows[] = $rowData;
            }
        }

        return [
            'rows' => $rows,
            'errors' => $errors,
            'total' => count($rows),
        ];
    }

    /**
     * Process a single row
     */
    protected function processRow(array $row, int $rowNum, array &$errors): ?array
    {
        $nama = trim($row['nama_penerima'] ?? '');

        if (empty($nama)) {
            $errors[] = [
                'row' => $rowNum,
                'message' => 'Nama penerima kosong',
            ];

            return null;
        }

        $processedRow = [
            'row_number' => $rowNum,
            'nama_penerima' => sanitize_input($nama, 255),
            'jabatan' => sanitize_input($row['jabatan'] ?? '', 255) ?: null,
            'npp' => sanitize_input($row['npp'] ?? '', 50) ?: null,
            'email' => sanitize_email($row['email'] ?? '') ?: null,
            'instansi' => sanitize_input($row['instansi'] ?? '', 255) ?: null,
            'matched_user_id' => null,
            'is_internal' => false,
        ];

        // Try to match with existing user
        if (! empty($processedRow['npp'])) {
            $user = User::where('npp', $processedRow['npp'])->first();
            if ($user) {
                $processedRow['matched_user_id'] = $user->id;
                $processedRow['is_internal'] = true;
                $processedRow['jabatan'] = $processedRow['jabatan'] ?: $user->jabatan;
            }
        } elseif (! empty($processedRow['email'])) {
            $user = User::where('email', $processedRow['email'])->first();
            if ($user) {
                $processedRow['matched_user_id'] = $user->id;
                $processedRow['is_internal'] = true;
                $processedRow['nama_penerima'] = $user->nama_lengkap;
                $processedRow['jabatan'] = $processedRow['jabatan'] ?: $user->jabatan;
            }
        }

        return $processedRow;
    }

    /**
     * Generate import template
     */
    public function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['nama_penerima', 'jabatan', 'npp', 'email', 'instansi'];
        $sheet->fromArray($headers, null, 'A1');

        // Example data
        $examples = [
            ['Dr. John Doe, M.Kom.', 'Dosen', '123456', 'john@unika.ac.id', 'FIKOM'],
            ['Jane Smith, S.T., M.T.', 'Kaprodi TI', '789012', 'jane@unika.ac.id', 'FIKOM'],
        ];
        $sheet->fromArray($examples, null, 'A2');

        // Style header
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save to temp
        $filename = 'template_penerima_'.date('Ymd_His').'.xlsx';
        $path = storage_path('app/temp/'.$filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    /**
     * Save import record
     */
    public function saveImport(UploadedFile $file, int $userId, array $parseResult): RecipientImport
    {
        $path = $file->store('recipient_imports', 'private');

        return RecipientImport::create([
            'user_id' => $userId,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'completed',
            'total_rows' => $parseResult['total'],
            'success_count' => count($parseResult['rows']),
            'error_count' => count($parseResult['errors']),
            'errors' => $parseResult['errors'],
        ]);
    }
}
