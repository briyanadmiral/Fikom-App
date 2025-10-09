<?php

namespace App\Services;

use App\Mail\SuratKeputusanMail;
use App\Models\KeputusanHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendSkEmail;
use Illuminate\Support\Facades\URL;

class SkNotifikasiService
{
    public function notifyApprovalRequest(KeputusanHeader $sk): void
    {
        if (!$sk->penandatangan) return;

        DB::table('notifikasi')->insert([
            'pengguna_id'  => $sk->penandatangan,
            'tipe'         => 'surat_keputusan',
            'referensi_id' => $sk->id,
            'pesan'        => 'Surat Keputusan ' . ($sk->nomor ?: '(draft)') . ' menunggu persetujuan Anda.',
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]);
    }

    public function notifyApproved(KeputusanHeader $sk): void
    {
        // Notif ke pembuat
        DB::table('notifikasi')->insert([
            'pengguna_id'  => $sk->dibuat_oleh,
            'tipe'         => 'surat_keputusan',
            'referensi_id' => $sk->id,
            'pesan'        => 'Surat Keputusan ' . ($sk->nomor ?: '(tanpa nomor)') . ' telah disetujui.',
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]);

        // Kirim email via queue (jalankan queue:work)
        dispatch(new SendSkEmail($sk->id))->onQueue('mail');
    }

    public function notifyRejected(KeputusanHeader $sk, ?string $note = null): void
    {
        // Notifikasi in-app ke pembuat
        DB::table('notifikasi')->insert([
            'pengguna_id'  => (int) $sk->dibuat_oleh,
            'tipe'         => 'surat_keputusan',
            'referensi_id' => (int) $sk->id,
            'pesan'        => 'Surat Keputusan ' . ($sk->nomor ?: '(tanpa nomor)') . ' ditolak.' .
                              ($note ? ' Catatan: ' . $note : ''),
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]);

        // Email ke pembuat
        $creator = $sk->pembuat;
        if ($creator && filter_var($creator->email, FILTER_VALIDATE_EMAIL)) {
            $ctaUrl = route('surat_keputusan.edit', $sk->id);

            $line = 'SK Anda ditolak.' . ($note ? ' Catatan: ' . $note : '');
            Mail::to($creator->email)->queue(
                new SuratKeputusanMail(
                    sk: $sk,
                    subject: 'SK ' . ($sk->nomor ?: '(tanpa nomor)') . ' ditolak',
                    heading: 'Surat Keputusan Ditolak',
                    messageLine: $line,
                    ctaUrl: $ctaUrl,
                    ctaText: 'Perbaiki SK',
                    attachSignedPdf: false
                )
            );
        }
    }

    // Optional: notifikasi saat pembuat merevisi dokumen pending
    public function notifyRevised(KeputusanHeader $sk, $byUser): void
    {
        if (! $sk->penandatangan) return;

        DB::table('notifikasi')->insert([
            'pengguna_id'  => (int) $sk->penandatangan,
            'tipe'         => 'surat_keputusan',
            'referensi_id' => (int) $sk->id,
            'pesan'        => 'SK ' . ($sk->nomor ?? '(tanpa nomor)') .
                              ' telah direvisi oleh ' . ($byUser->nama_lengkap ?? 'pengguna') . '.',
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]);

        $pen = $sk->penandatanganUser;
        if ($pen && filter_var($pen->email, FILTER_VALIDATE_EMAIL)) {
            $ctaUrl = route('surat_keputusan.approveForm', $sk->id);

            Mail::to($pen->email)->queue(
                new SuratKeputusanMail(
                    sk: $sk,
                    subject: 'Revisi SK ' . ($sk->nomor ?? '(tanpa nomor)'),
                    heading: 'SK Direvisi dan Menunggu Tinjauan',
                    messageLine: 'Pembuat telah memperbarui SK yang sedang menunggu persetujuan.',
                    ctaUrl: $ctaUrl,
                    ctaText: 'Tinjau Revisi',
                    attachSignedPdf: false
                )
            );
        }
    }
}
