<?php

namespace App\Mail;

use App\Models\KeputusanHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SkFinal extends Mailable
{
    use Queueable, SerializesModels;

    public KeputusanHeader $sk;

    public function __construct(KeputusanHeader $sk)
    {
        $this->sk = $sk->loadMissing(['pembuat', 'penandatanganUser']);
    }

    public function build()
    {
        $nomor   = $this->sk->nomor ?: '(tanpa nomor)';
        $subject = 'Surat Keputusan Disetujui: ' . $nomor;

        $mail = $this->subject($subject)
            ->markdown('emails.surat_keputusan.final', [
                'sk' => $this->sk,
            ]);

        // Lampirkan PDF bila ada
        if ($this->sk->signed_pdf_path && Storage::disk('local')->exists($this->sk->signed_pdf_path)) {
            $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string) $this->sk->nomor ?: 'TanpaNomor');
            $absolute  = Storage::disk('local')->path($this->sk->signed_pdf_path);
            $mail->attach($absolute, [
                'as'   => "Surat_Keputusan_{$safeNomor}.pdf",
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
