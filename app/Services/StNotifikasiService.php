<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSuratTugasEmail;

class StNotifikasiService
{
    /**
     * Ambil penerima internal (punya pengguna_id) beserta emailnya.
     */
    private function getInternalRecipients(int $tugasId): array
    {
        return DB::table('tugas_penerima AS tp')
            ->join('pengguna AS u', 'u.id', '=', 'tp.pengguna_id')
            ->where('tp.tugas_id', $tugasId)
            ->whereNotNull('tp.pengguna_id')
            ->where('u.status', '=', 'aktif')
            ->select([
                'u.id as pengguna_id',
                'u.nama_lengkap as nama',
                'u.email as email',
            ])->get()->all();
    }

    /**
     * Notif saat diajukan (pending) → ke penandatangan.
     */
    public function notifyApprovalRequest(object $tugas): void
    {
        if (empty($tugas->penandatangan)) {
            return;
        }

        $nomor = $tugas->nomor ?? '(draft)';

        DB::table('notifikasi')->insert([
            'pengguna_id'  => (int) $tugas->penandatangan,
            'tipe'         => 'surat_tugas',
            'referensi_id' => (int) $tugas->id,
            'pesan'        => 'Surat Tugas ' . $nomor . ' menunggu persetujuan Anda.',
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]);
    }

    /**
     * Notif & email saat disetujui:
     * - HANYA penerima internal terpilih yang dikabari + dikirimi email
     * - (opsional) kabari pembuat → buka komentar di bawah
     */
    public function notifyApproved(object $tugas): void
    {
        $recipients = $this->getInternalRecipients((int) $tugas->id);

        // (opsional) kabari pembuat — non-email — buka komentar jika diperlukan
        /*
        if (!empty($tugas->dibuat_oleh)) {
            DB::table('notifikasi')->insert([
                'pengguna_id'  => (int) $tugas->dibuat_oleh,
                'tipe'         => 'surat_tugas',
                'referensi_id' => (int) $tugas->id,
                'pesan'        => 'Surat Tugas ' . ($tugas->nomor ?: '(tanpa nomor)') . ' telah disetujui.',
                'dibaca'       => 0,
                'dibuat_pada'  => now(),
            ]);
        }
        */

        // Notifikasi & email untuk setiap penerima internal terpilih
        foreach ($recipients as $r) {
            DB::table('notifikasi')->insert([
                'pengguna_id'  => (int) $r->pengguna_id,
                'tipe'         => 'surat_tugas',
                'referensi_id' => (int) $tugas->id,
                'pesan'        => 'Anda terdaftar sebagai penerima pada Surat Tugas ' . ($tugas->nomor ?: '(tanpa nomor)') . '.',
                'dibaca'       => 0,
                'dibuat_pada'  => now(),
            ]);
        }

        // Kirim email ST final (PDF ditandatangani) ke penerima internal yang punya email
        try {
            dispatch(new SendSuratTugasEmail(
                (int) $tugas->id,
                'to_recipients' // <— hanya penerima terpilih
            ))->onQueue('mail');
        } catch (\Throwable $e) {
            Log::error('Gagal dispatch SendSuratTugasEmail', [
                'tugas_id' => $tugas->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notif ditolak → ke pembuat (tanpa email).
     */
    public function notifyRejected(object $tugas, ?string $catatan = null): void
    {
        if (empty($tugas->dibuat_oleh)) return;

        DB::table('notifikasi')->insert([
            'pengguna_id'  => (int) $tugas->dibuat_oleh,
            'tipe'         => 'surat_tugas',
            'referensi_id' => (int) $tugas->id,
            'pesan'        => 'Surat Tugas ' . ($tugas->nomor ?: '(tanpa nomor)') .
                              ' ditolak.' . ($catatan ? ' Catatan: ' . $catatan : ''),
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]);
    }

    /**
     * Notif revisi (masih pending) → ke penandatangan.
     */
    public function notifyRevised(object $tugas, object $reviserUser): void
    {
        if (empty($tugas->penandatangan)) return;

        DB::table('notifikasi')->insert([
            'pengguna_id'  => (int) $tugas->penandatangan,
            'tipe'         => 'surat_tugas',
            'referensi_id' => (int) $tugas->id,
            'pesan'        => 'ST ' . ($tugas->nomor ?? '(tanpa nomor)') .
                              ' telah direvisi oleh ' . ($reviserUser->nama_lengkap ?? 'pengguna') . '.',
            'dibaca'       => 0,
            'dibuat_pada'  => now(),
        ]);
    }
}
