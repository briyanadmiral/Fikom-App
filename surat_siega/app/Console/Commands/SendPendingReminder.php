<?php

namespace App\Console\Commands;

use App\Models\KeputusanHeader;
use App\Models\Notifikasi;
use App\Models\TugasHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command untuk mengirim reminder otomatis jika surat pending terlalu lama.
 *
 * Mendeteksi Surat Tugas dan Surat Keputusan yang berstatus 'pending'
 * lebih dari X hari, lalu mengirim notifikasi in-app ke approver.
 *
 * Schedule: Setiap hari jam 08:00 WIB
 * Usage: php artisan surat:pending-reminder
 *        php artisan surat:pending-reminder --days=5
 */
class SendPendingReminder extends Command
{
    protected $signature = 'surat:pending-reminder
                            {--days=3 : Jumlah hari threshold sebelum reminder dikirim}';

    protected $description = 'Kirim reminder ke approver untuk surat yang pending lebih dari X hari';

    public function handle(): int
    {
        $thresholdDays = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($thresholdDays);
        $sentCount = 0;

        $this->info("Mencari surat pending lebih dari {$thresholdDays} hari...");

        // ============================================================
        // 1. Surat Tugas yang pending terlalu lama
        // ============================================================
        $pendingST = TugasHeader::where('status_surat', 'pending')
            ->where(function ($q) use ($cutoff) {
                $q->where('submitted_at', '<=', $cutoff)
                  ->orWhere(function ($q2) use ($cutoff) {
                      // Fallback jika submitted_at null, pakai updated_at
                      $q2->whereNull('submitted_at')
                         ->where('updated_at', '<=', $cutoff);
                  });
            })
            ->whereNotNull('penandatangan')
            ->get();

        foreach ($pendingST as $tugas) {
            $approverId = (int) ($tugas->next_approver ?? $tugas->penandatangan);

            if (! $approverId) {
                continue;
            }

            // Cek apakah reminder sudah dikirim hari ini untuk surat ini
            $alreadySent = Notifikasi::where('pengguna_id', $approverId)
                ->where('tipe', 'reminder')
                ->where('referensi_id', $tugas->id)
                ->where('dibuat_pada', '>=', now()->startOfDay())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            // Hitung berapa hari pending
            $pendingSince = $tugas->submitted_at ?? $tugas->updated_at;
            $daysPending = $pendingSince ? (int) now()->diffInDays($pendingSince) : $thresholdDays;

            $perihal = $tugas->tugas ?? $tugas->nama_umum ?? 'Surat Tugas';

            Notifikasi::createNotification([
                'pengguna_id' => $approverId,
                'tipe' => 'reminder',
                'referensi_id' => $tugas->id,
                'pesan' => "Reminder: Surat Tugas \"{$perihal}\" sudah menunggu persetujuan selama {$daysPending} hari.",
            ]);

            $sentCount++;
        }

        // ============================================================
        // 2. Surat Keputusan yang pending terlalu lama
        // ============================================================
        $pendingSK = KeputusanHeader::where('status_surat', 'pending')
            ->where('updated_at', '<=', $cutoff)
            ->whereNotNull('penandatangan')
            ->get();

        foreach ($pendingSK as $sk) {
            $approverId = (int) $sk->penandatangan;

            if (! $approverId) {
                continue;
            }

            // Cek apakah reminder sudah dikirim hari ini
            $alreadySent = Notifikasi::where('pengguna_id', $approverId)
                ->where('tipe', 'reminder')
                ->where('referensi_id', $sk->id)
                ->where('dibuat_pada', '>=', now()->startOfDay())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $daysPending = (int) now()->diffInDays($sk->updated_at);
            $tentang = $sk->tentang ?? 'Surat Keputusan';

            Notifikasi::createNotification([
                'pengguna_id' => $approverId,
                'tipe' => 'reminder',
                'referensi_id' => $sk->id,
                'pesan' => "Reminder: SK \"{$tentang}\" sudah menunggu persetujuan selama {$daysPending} hari.",
            ]);

            $sentCount++;
        }

        // ============================================================
        // Summary
        // ============================================================
        if ($sentCount > 0) {
            $this->info("Berhasil mengirim {$sentCount} reminder.");
            Log::info("SendPendingReminder: {$sentCount} reminder terkirim", [
                'threshold_days' => $thresholdDays,
                'st_pending' => $pendingST->count(),
                'sk_pending' => $pendingSK->count(),
            ]);
        } else {
            $this->info('Tidak ada surat yang perlu di-remind.');
        }

        return self::SUCCESS;
    }
}
