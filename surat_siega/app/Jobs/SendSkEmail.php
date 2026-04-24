<?php

namespace App\Jobs;

use App\Mail\SkFinal;
use App\Models\KeputusanHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $skId;

    public ?string $recipientEmail;

    public bool $afterCommit = true; // pastikan kirim setelah commit DB

    public $tries = 3;

    public $backoff = 30;

    /**
     * @param  int  $skId  ID Surat Keputusan
     * @param  string|null  $recipientEmail  Email spesifik (untuk penerima eksternal). Jika null, kirim ke pembuat.
     */
    public function __construct(int $skId, ?string $recipientEmail = null)
    {
        $this->skId = $skId;
        $this->recipientEmail = $recipientEmail;
    }

    public function handle(): void
    {
        $sk = KeputusanHeader::with(['pembuat'])->find($this->skId);
        if (! $sk) {
            Log::info('SendSkEmail: skipped, SK not found', ['sk_id' => $this->skId]);

            return;
        }

        // Tentukan email tujuan: eksplisit (eksternal) atau pembuat (default)
        $targetEmail = $this->recipientEmail;
        if (empty($targetEmail)) {
            if (! $sk->pembuat || empty($sk->pembuat->email)) {
                Log::info('SendSkEmail: skipped, no valid recipient', ['sk_id' => $this->skId]);

                return;
            }
            $targetEmail = $sk->pembuat->email;
        }

        // Validasi format email
        if (! filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            Log::warning('SendSkEmail: invalid email address', [
                'sk_id' => $this->skId,
                'email' => sanitize_log_message($targetEmail),
            ]);

            return;
        }

        try {
            Mail::to($targetEmail)->send(new SkFinal($sk));

            Log::info('SendSkEmail: sent successfully', [
                'sk_id' => $this->skId,
                'email' => sanitize_log_message($targetEmail),
            ]);
        } catch (\Throwable $e) {
            Log::error('SendSkEmail: failed to send', [
                'sk_id' => $this->skId,
                'email' => sanitize_log_message($targetEmail),
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }
}
