<?php

namespace App\Jobs;

use App\Mail\SkFinal;
use App\Models\KeputusanHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $skId;

    public bool $afterCommit = true; // pastikan kirim setelah commit DB

    public $tries = 3;

    public $backoff = 30;

    public function __construct(int $skId)
    {
        $this->skId = $skId;
    }

    public function handle(): void
    {
        $sk = KeputusanHeader::with(['pembuat'])->find($this->skId);
        if (! $sk || ! $sk->pembuat || empty($sk->pembuat->email)) {
            return; // tidak ada email yang valid
        }

        Mail::to($sk->pembuat->email)->send(new SkFinal($sk));
        // OPTIONAL: cc ke penandatangan untuk arsip
        // if ($sk->penandatanganUser?->email) {
        //     Mail::to($sk->pembuat->email)
        //         ->cc([$sk->penandatanganUser->email])
        //         ->send(new SkFinal($sk));
        // }
    }
}
