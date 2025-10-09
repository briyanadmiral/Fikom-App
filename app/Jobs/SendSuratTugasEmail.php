<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuratTugasFinal;

class SendSuratTugasEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tugasId;
    public string $mode; // 'to_recipients' (default), (opsional lain kalau nanti mau)

    public int $tries = 3;
    public int $timeout = 120; // detik
    public $backoff = 60;      // detik

    public function __construct(int $tugasId, string $mode = 'to_recipients')
    {
        $this->tugasId = $tugasId;
        $this->mode    = $mode;
    }

    public function handle(): void
    {
        // Ambil header ST
        $st = DB::table('tugas_header')->where('id', $this->tugasId)->first();
        if (!$st) return;

        // Susun subject
        $subject = 'Surat Tugas Disetujui: ' . ($st->nomor ?: '(tanpa nomor)');

        // Data untuk template email
        $payload = (object) [
            'nomor'          => $st->nomor,
            'tugas'          => $st->tugas,
            'tanggal_surat'  => $st->tanggal_surat,
            'waktu_mulai'    => $st->waktu_mulai,
            'waktu_selesai'  => $st->waktu_selesai,
            'tempat'         => $st->tempat,
            'redaksi_pembuka'=> $st->redaksi_pembuka,
            'penutup'        => $st->penutup,
        ];

        // Lampiran PDF (bila ada)
        $attachmentPath = $st->signed_pdf_path ?: null;

        // Tentukan daftar penerima sesuai mode
        $emails = [];

        if ($this->mode === 'to_recipients') {
            // Penerima internal terpilih dengan email
            $rows = DB::table('tugas_penerima AS tp')
                ->join('pengguna AS u', 'u.id', '=', 'tp.pengguna_id')
                ->where('tp.tugas_id', $st->id)
                ->whereNotNull('tp.pengguna_id')
                ->whereNotNull('u.email')
                ->where('u.status', '=', 'aktif')
                ->pluck('u.email')
                ->all();

            $emails = array_values(array_unique(array_filter($rows)));
        }

        if (empty($emails)) {
            Log::info('SendSuratTugasEmail: tidak ada email penerima', [
                'tugas_id' => $this->tugasId,
                'mode'     => $this->mode,
            ]);
            return;
        }

        foreach ($emails as $email) {
            try {
                $mailable = new SuratTugasFinal($subject, $payload, $attachmentPath);
                Mail::to($email)->send($mailable);
            } catch (\Throwable $e) {
                Log::error('Gagal kirim email ST', [
                    'tugas_id' => $this->tugasId,
                    'email'    => $email,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }
}
