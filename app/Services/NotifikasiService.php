<?php

namespace App\Services;

use App\Models\TugasHeader;
use App\Models\Notifikasi;
use App\Mail\SuratTugasFinal;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotifikasiService
{
    /**
     * Notifikasi ke penandatangan (role 2/3) bahwa ada surat menunggu persetujuan.
     */
    public function notifyApprovalRequest(TugasHeader $tugas): void
    {
        if (!$tugas->penandatangan) {
            return;
        }

        Notifikasi::create([
            'pengguna_id'  => $tugas->penandatangan,
            'tipe'         => 'surat_tugas',
            'referensi_id' => $tugas->id,
            'pesan'        => "Surat Tugas {$tugas->nomor} menunggu persetujuan Anda.",
        ]);

        // (opsional) kirim email ke penandatangan di sini bila dibutuhkan
        // contoh:
        // if ($tugas->penandatanganUser?->email) {
        //     Mail::to($tugas->penandatanganUser->email)->send(
        //         new SuratTugasFinal("Permintaan Persetujuan: {$tugas->nomor}", (object) $tugas->toArray())
        //     );
        // }
    }

    /**
     * Notifikasi ke pembuat dan semua penerima internal setelah surat disetujui.
     * - Buat notifikasi DB.
     * - Kirim email dengan lampiran PDF final bila tersedia.
     */
    public function notifyApproved(TugasHeader $tugas): void
    {
        // 1) Notifikasi ke pembuat
        Notifikasi::create([
            'pengguna_id'  => $tugas->dibuat_oleh,
            'tipe'         => 'surat_tugas',
            'referensi_id' => $tugas->id,
            'pesan'        => "Surat Tugas {$tugas->nomor} telah disetujui.",
        ]);

        // 2) Ambil penerima internal (punya pengguna_id & email)
        $penerimaInternal = $tugas->penerima()
            ->whereNotNull('pengguna_id')
            ->with('pengguna:id,email')
            ->get();

        if ($penerimaInternal->isEmpty()) {
            return;
        }

        // Siapkan subjek & path lampiran (relatif terhadap storage/app)
        $subject = 'Surat Tugas Disetujui: ' . (($tugas->nomor ?? '') ?: '');
        $attachmentPathRel = $tugas->signed_pdf_path ?: null; // contoh: "private/surat_tugas/signed/10_abcd.pdf"

        // 3) Loop penerima: tulis notifikasi DB + kirim email (hindari duplikasi email)
        $sentEmails = [];

        foreach ($penerimaInternal as $row) {
            $email = $row->pengguna?->email;
            if (empty($email)) {
                continue;
            }
            // Hindari kirim email ganda ke alamat yang sama
            if (isset($sentEmails[$email])) {
                continue;
            }
            $sentEmails[$email] = true;

            // A) Notif DB “lonceng”
            Notifikasi::create([
                'pengguna_id'  => $row->pengguna_id,
                'tipe'         => 'surat_tugas',
                'referensi_id' => $tugas->id,
                'pesan'        => "Anda terdaftar sebagai penerima pada Surat Tugas {$tugas->nomor}.",
            ]);

            // B) Email + Lampiran PDF (jika tersedia)
            try {
                // Gunakan konstruktor fleksibel:
                // - kirim subjek eksplisit
                // - lampiran via path relatif (argumen 3=filename opsional=NULL, argumen 4=$isPath=true)
                $mailable = new SuratTugasFinal(
                    $subject,
                    (object) $tugas->toArray(),
                    $attachmentPathRel // <<- bisa null; Mailable akan fallback ke signed_pdf_path
                );
                // Karena mailable kita tidak mengimplementasikan ShouldQueue, gunakan send() (sinkron).
                Mail::to($email)->send($mailable);
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email Surat Tugas Final', [
                    'email' => $email,
                    'tugas_id' => $tugas->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
