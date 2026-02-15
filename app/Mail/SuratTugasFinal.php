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
        $this->subjectLine = $subjectLine;
        $this->tugas = $tugas;
        $this->attachmentPath = $attachmentPath;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectLine)
            ->markdown('emails.surat_tugas.final', [
                'tugas' => $this->tugas,
            ]);

        if ($this->attachmentPath) {
            // Check file exists on local disk
            if (Storage::disk('local')->exists($this->attachmentPath)) {
                $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string) ($this->tugas->nomor ?: 'lampiran'));
                $absolute = Storage::disk('local')->path($this->attachmentPath);
                $mail->attach($absolute, [
                    'as' => "Surat_Tugas_{$safeNomor}.pdf",
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
