<?php

namespace App\Services;

use App\Models\KeputusanHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
// use App\Mail\SuratTugasFinal; // <- aktifkan hanya kalau memang ada mailable ini

class SkNotifikasiService
{
    public function notifyApprovalRequest(KeputusanHeader $sk): void
    {
        if (!$sk->penandatangan) {
            return;
        }

        $nomorTeks = $sk->nomor ?: '(draft)';

        DB::table('notifikasi')->insert([
            'pengguna_id'  => $sk->penandatangan,
            'tipe'         => 'surat_keputusan',
            'referensi_id' => $sk->id,
            'pesan'        => 'Surat Keputusan ' . $nomorTeks . ' menunggu persetujuan Anda.',
            'dibaca'       => 0,
            // ganti ke 'created_at' bila kolom 'dibuat_pada' tidak ada di tabel
            'dibuat_pada'  => now(),
        ]);
    }

    public function notifyApproved(KeputusanHeader $sk): void
    {
        DB::table('notifikasi')->insert([
            'pengguna_id'  => $sk->dibuat_oleh,
            'tipe'         => 'surat_keputusan',
            'referensi_id' => $sk->id,
            'pesan'        => 'Surat Keputusan ' . ($sk->nomor ?: '(tanpa nomor)') . ' telah disetujui.',
            'dibaca'       => 0,
            // ganti ke 'created_at' bila kolom 'dibuat_pada' tidak ada
            'dibuat_pada'  => now(),
        ]);

        // Kirim notifikasi ke penerima internal (kalau relasi ada)
        // Jika relasi/table Anda bernama selain 'users', sesuaikan query ini.
        foreach ($sk->penerima as $u) {
            DB::table('notifikasi')->insert([
                'pengguna_id'  => $u->id,
                'tipe'         => 'surat_keputusan',
                'referensi_id' => $sk->id,
                'pesan'        => 'Anda mendapat tembusan Surat Keputusan ' . ($sk->nomor ?: '(tanpa nomor)') . '.',
                'dibaca'       => 0,
                'dibuat_pada'  => now(),
            ]);

            // OPTIONAL: kirim email jika memang ada mailable & email user
            /*
            if ($u->email) {
                try {
                    Mail::to($u->email)->send(
                        new SuratTugasFinal(
                            'Surat Keputusan Disetujui: ' . ($sk->nomor ?: '(tanpa nomor)'),
                            (object) $sk->toArray(),
                            $sk->signed_pdf_path
                        )
                    );
                } catch (\Throwable $e) {
                    Log::error('Gagal kirim email SK', [
                        'email' => $u->email,
                        'sk_id' => $sk->id,
                        'err'   => $e->getMessage(),
                    ]);
                }
            }
            */
        }
    }
}
