<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\SuratTugasFinal;

class SendSuratTugasEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tugasId;
    public string $mode; // 'to_recipients' | 'to_approver'

    public function __construct(int $tugasId, string $mode = 'to_recipients')
    {
        $this->tugasId = $tugasId;
        $this->mode = $mode;
    }

    public function handle(): void
    {
        $tugas = DB::table('tugas_header')->where('id', $this->tugasId)->first();
        if (!$tugas) {
            throw new ModelNotFoundException("tugas_header #{$this->tugasId} tidak ditemukan");
        }

        $tos = [];
        $ccs = [];

        if ($this->mode === 'to_approver' && $tugas->next_approver) {
            $approver = DB::table('pengguna')->where('id', $tugas->next_approver)->value('email');
            if ($approver) $tos[] = $approver;
        } else {
            // penerima internal dari tugas_penerima → pengguna.email
            $tos = DB::table('tugas_penerima as tp')
                ->join('pengguna as p', 'p.id', '=', 'tp.pengguna_id')
                ->where('tp.tugas_id', $this->tugasId)
                ->pluck('p.email')
                ->filter()
                ->unique()
                ->values()
                ->all();

            // CC opsional: asal_surat & penandatangan
            if ($tugas->asal_surat) {
                $cc = DB::table('pengguna')->where('id', $tugas->asal_surat)->value('email');
                if ($cc) $ccs[] = $cc;
            }
            if ($tugas->penandatangan) {
                $cc = DB::table('pengguna')->where('id', $tugas->penandatangan)->value('email');
                if ($cc) $ccs[] = $cc;
            }
            $ccs = array_values(array_unique($ccs));
        }

        if (empty($tos)) {
            // Tidak ada tujuan, jangan kirim (silent)
            return;
        }

        // Path relatif lampiran (storage/app/...)
        $attachmentPathRel = $tugas->signed_pdf_path ?: null;
        if ($attachmentPathRel && !Storage::exists($attachmentPathRel)) {
            $attachmentPathRel = null; // aman: kirim tanpa lampiran jika file hilang
        }

        $subject = "[Surat Tugas] {$tugas->nomor} — {$tugas->tugas}";

        // Kirim pakai mailable Markdown view: emails/surat_tugas/final
        $mailable = new SuratTugasFinal($subject, $tugas, $attachmentPathRel);

        $mailer = Mail::to($tos);
        if (!empty($ccs)) $mailer->cc($ccs);
        $mailer->send($mailable);
    }
}
