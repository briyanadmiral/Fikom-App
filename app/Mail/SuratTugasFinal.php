<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SuratTugasFinal extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectLine;
    /** @var object */
    public $tugas;
    public ?string $attachmentPath;

    public function __construct(string $subjectLine, object $tugas, ?string $attachmentPath = null)
    {
        $this->subjectLine   = $subjectLine;
        $this->tugas         = $tugas;
        $this->attachmentPath = $attachmentPath;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->markdown('emails.surat_tugas.final', [
                'tugas' => $this->tugas,
            ]);

        if ($this->attachmentPath) {
            // Coba ambil dari disk local (storage/app/...)
            if (Storage::disk('local')->exists($this->attachmentPath)) {
                $mail->attach(
                    storage_path('app/' . ltrim($this->attachmentPath, '/')),
                    ['as' => 'Surat_Tugas_' . ($this->tugas->nomor ?: 'lampiran') . '.pdf', 'mime' => 'application/pdf']
                );
            }
        }

        return $mail;
    }
}
