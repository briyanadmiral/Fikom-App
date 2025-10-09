<?php

namespace App\Mail;

use App\Models\KeputusanHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SuratKeputusanMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $heading;
    public string $messageLine;
    public ?string $ctaUrl;
    public ?string $ctaText;
    public KeputusanHeader $sk;
    public bool $attachSignedPdf;

    /**
     * @param  KeputusanHeader  $sk
     * @param  string           $subject
     * @param  string           $heading        Judul di email body
     * @param  string           $messageLine    Isi ringkas
     * @param  string|null      $ctaUrl         Link aksi (opsional)
     * @param  string|null      $ctaText        Teks tombol (opsional)
     * @param  bool             $attachSignedPdf lampirkan PDF bertanda tangan (jika ada)
     */
    public function __construct(
        KeputusanHeader $sk,
        string $subject,
        string $heading,
        string $messageLine,
        ?string $ctaUrl = null,
        ?string $ctaText = null,
        bool $attachSignedPdf = false
    ) {
        $this->subject($subject);
        $this->heading = $heading;
        $this->messageLine = $messageLine;
        $this->ctaUrl = $ctaUrl;
        $this->ctaText = $ctaText;
        $this->sk = $sk->withoutRelations();
        $this->attachSignedPdf = $attachSignedPdf;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            from: new Address(config('mail.from.address'), config('mail.from.name'))
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sk',
            with: [
                'sk'          => $this->sk,
                'heading'     => $this->heading,
                'messageLine' => $this->messageLine,
                'ctaUrl'      => $this->ctaUrl,
                'ctaText'     => $this->ctaText,
            ]
        );
    }

    public function attachments(): array
    {
        if (! $this->attachSignedPdf || empty($this->sk->signed_pdf_path)) {
            return [];
        }

        // signed_pdf_path disimpan di disk "local" → ambil absolute path
        if (Storage::disk('local')->exists($this->sk->signed_pdf_path)) {
            $safeNomor = preg_replace('/[\/\\\\]+/', '-', (string) ($this->sk->nomor ?? 'TanpaNomor'));
            $filename  = "SuratKeputusan_{$safeNomor}.pdf";
            return [
                Attachment::fromPath(Storage::disk('local')->path($this->sk->signed_pdf_path))
                    ->as($filename)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
