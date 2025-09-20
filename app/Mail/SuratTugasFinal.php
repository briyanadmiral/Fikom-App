<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuratTugasFinal extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public object $tugas;              // stdClass dari DB
    public ?string $attachmentPathRel; // path relatif di storage/app (mis. private/...)

    public function __construct(string $subjectText, object $tugas, ?string $attachmentPathRel = null)
    {
        $this->subjectText = $subjectText;
        $this->tugas = $tugas;
        $this->attachmentPathRel = $attachmentPathRel;
    }

    public function build()
    {
        $mail = $this->subject($this->subjectText)
            ->markdown('emails.surat_tugas.final', [
                'tugas' => $this->tugas,
                // 'recipientName' => null, // bisa dipakai jika personalisasi per penerima
            ]);

        if ($this->attachmentPathRel) {
            $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string)($this->tugas->nomor ?? 'SuratTugas'));
            $mail->attachFromStorage(
                $this->attachmentPathRel,
                "SuratTugas_{$safeNomor}.pdf",
                ['mime' => 'application/pdf']
            );
        }

        return $mail;
    }
}
