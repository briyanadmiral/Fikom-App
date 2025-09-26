<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SuratTugasFinal extends Mailable
{
    use Queueable, SerializesModels;

    /** @var object Data surat tugas (Model atau stdClass) */
    public $tugas;

    /** @var string Subjek email */
    protected string $subjectText;

    /** @var string|null PDF dalam bytes (untuk attachData) */
    protected ?string $pdfBytes = null;

    /** @var string|null Path relatif di storage (mis. "private/…/signed/….pdf") */
    protected ?string $pdfPath = null;

    /** @var string Nama file lampiran yang akan dikirim */
    protected string $filename;

    /**
     * Konstruktor fleksibel agar kompatibel dengan berbagai pemanggilan:
     *
     * 1) new SuratTugasFinal($tugas)
     * 2) new SuratTugasFinal($tugas, $pdfBytes)
     * 3) new SuratTugasFinal($tugas, $pdfPath, null, true)        // kirim path, set $isPath=true
     * 4) new SuratTugasFinal($subject, $tugas, $pdfPathOrBytes)   // pola lama: subjek di arg1
     *
     * @param mixed       $arg1   Bisa $tugas (object) atau $subject (string)
     * @param mixed|null  $arg2   Jika $arg1 adalah $tugas: ini bisa bytes atau path
     *                            Jika $arg1 adalah $subject: ini wajib $tugas
     * @param string|null $arg3   Opsional: nama file lampiran (tidak wajib)
     * @param bool        $isPath Set true jika arg2/arg3 adalah path (bukan bytes)
     */
    public function __construct($arg1, $arg2 = null, ?string $arg3 = null, bool $isPath = false)
    {
        // Deteksi mode: apakah arg1 adalah subjek atau tugas?
        if (is_string($arg1) && (is_object($arg2) || is_array($arg2))) {
            // Pola lama: ($subject, $tugas, $pdfPathOrBytes)
            $this->subjectText = $arg1;
            $this->tugas = is_object($arg2) ? $arg2 : (object) $arg2;

            // $arg3 = attachment (bytes atau path tergantung $isPath)
            if ($arg3 !== null) {
                if ($isPath) {
                    $this->pdfPath = $arg3;
                } else {
                    $this->pdfBytes = $arg3;
                }
            }
        } else {
            // Pola baru/umum: ($tugas, $attachment = null, $filename = null, $isPath = false)
            $this->tugas = is_object($arg1) ? $arg1 : (object) $arg1;
            $this->subjectText = 'Surat Tugas Disetujui: ' . (($this->tugas->nomor ?? '') ?: '');

            if ($arg2 !== null) {
                if ($isPath) {
                    // $arg2 adalah path relatif di storage
                    $this->pdfPath = $arg2;
                } else {
                    // $arg2 adalah bytes
                    $this->pdfBytes = $arg2;
                }
            }
        }

        // Tentukan nama file lampiran yang aman
        $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string)($this->tugas->nomor ?? 'SuratTugas'));
        $this->filename = $arg3 && !$isPath ? $arg3 : "SuratTugas_{$safeNomor}.pdf";
    }

    public function build()
    {
        // Gunakan markdown view yang sudah ada di project kamu
        $mail = $this->subject($this->subjectText)
            ->markdown('emails.surat_tugas.final', [
                'tugas' => $this->tugas,
            ]);

        // Siapkan bytes PDF
        $bytes = $this->pdfBytes;

        // 1) Jika belum ada bytes tapi ada path yang dikirim ke constructor
        if (!$bytes && $this->pdfPath) {
            $bytes = $this->readPdfFromStorage($this->pdfPath);
        }

        // 2) Fallback terakhir: gunakan path dari kolom signed_pdf_path (hasil approve)
        if (!$bytes && !empty($this->tugas->signed_pdf_path)) {
            $bytes = $this->readPdfFromStorage($this->tugas->signed_pdf_path);
        }

        if ($bytes) {
            $mail->attachData($bytes, $this->filename, ['mime' => 'application/pdf']);
        }

        return $mail;
    }

    /**
     * Membaca PDF dari storage.
     * - Utamakan disk 'local' (storage/app/...), sesuai path yang kita simpan saat approve.
     * - Jatuhkan ke disk default jika perlu.
     */
    private function readPdfFromStorage(string $path): ?string
    {
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->get($path);
        }
        if (Storage::exists($path)) {
            return Storage::get($path);
        }
        return null;
    }
}
