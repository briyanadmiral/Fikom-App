<?php

namespace App\Services;

use App\Jobs\SendSuratTugasEmail;
use App\Models\TugasHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Notification service khusus untuk Surat Tugas
 * Extends dari BaseNotificationService untuk shared functionality
 */
class SuratTugasNotificationService extends BaseNotificationService
{
    /**
     * Tipe notifikasi untuk Surat Tugas
     */
    protected function getNotificationType(): string
    {
        return 'surat_tugas';
    }

    /**
     * Notifikasi saat surat diajukan ke approver (draft → pending)
     * FIXED: Uses next_approver instead of penandatangan
     *
     * @param TugasHeader $tugas
     * @return void
     */
    public function notifyApprovalRequest(TugasHeader $tugas): void
    {
        if (!$tugas->next_approver) {
            Log::warning('notifyApprovalRequest: next_approver is null', [
                'tugas_id' => $tugas->id
            ]);
            return;
        }

        $approver = $this->getActiveUser($tugas->next_approver);
        
        if (!$approver) {
            Log::warning('notifyApprovalRequest: next_approver not found or inactive', [
                'tugas_id' => $tugas->id,
                'next_approver_id' => $tugas->next_approver
            ]);
            return;
        }

        $this->createNotification(
            $tugas->next_approver,
            $tugas->id,
            "Surat Tugas {$tugas->nomor} menunggu persetujuan Anda."
        );

        $this->logNotificationActivity('approval_request', $tugas->id, [
            'next_approver_id' => $tugas->next_approver,
            'approver_name' => $approver->nama_lengkap
        ]);
    }

    /**
     * Notifikasi saat surat disetujui (pending → disetujui)
     * Notify: Pembuat + Semua Recipients Internal
     *
     * @param TugasHeader $tugas
     * @return void
     */
    public function notifyApproved(TugasHeader $tugas): void
    {
        // 1. Notify pembuat
        if ($tugas->dibuat_oleh) {
            $pembuat = $this->getActiveUser($tugas->dibuat_oleh);
            
            if ($pembuat) {
                $this->createNotification(
                    $tugas->dibuat_oleh,
                    $tugas->id,
                    "Surat Tugas {$tugas->nomor} telah disetujui."
                );
            }
        }

        // 2. Notify all internal recipients
        $recipients = $this->getActiveInternalRecipients($tugas);

        if (empty($recipients)) {
            Log::info('No active internal recipients', ['tugas_id' => $tugas->id]);
            return;
        }

        $notifiedCount = 0;
        $emailQueuedCount = 0;

        foreach ($recipients as $recipient) {
            // A. Database notification
            if ($this->createNotification(
                $recipient->pengguna_id,
                $tugas->id,
                "Anda menerima Surat Tugas baru: {$tugas->nomor}"
            )) {
                $notifiedCount++;
            }

            // B. Queue email
            if ($this->isValidEmail($recipient->email)) {
                if ($this->dispatchJob(
                    new SendSuratTugasEmail($tugas->id, 'to_recipients', $recipient->pengguna_id)
                )) {
                    $emailQueuedCount++;
                }
            }
        }

        $this->logNotificationActivity('approved', $tugas->id, [
            'total_recipients' => count($recipients),
            'notified' => $notifiedCount,
            'email_queued' => $emailQueuedCount
        ]);
    }

    /**
     * Notifikasi saat surat ditolak
     *
     * @param TugasHeader $tugas
     * @param string $alasan
     * @return void
     */
    public function notifyRejected(TugasHeader $tugas, string $alasan = ''): void
    {
        if (!$tugas->dibuat_oleh) {
            return;
        }

        $pembuat = $this->getActiveUser($tugas->dibuat_oleh);
        
        if ($pembuat) {
            $pesan = "Surat Tugas {$tugas->nomor} ditolak.";
            if ($alasan) {
                $pesan .= " Alasan: {$alasan}";
            }

            $this->createNotification($tugas->dibuat_oleh, $tugas->id, $pesan);

            $this->logNotificationActivity('rejected', $tugas->id, [
                'pembuat_id' => $tugas->dibuat_oleh,
                'alasan' => $alasan
            ]);
        }
    }

    /**
     * Notifikasi saat perlu revisi
     *
     * @param TugasHeader $tugas
     * @param string $catatan
     * @return void
     */
    public function notifyRevisionRequested(TugasHeader $tugas, string $catatan = ''): void
    {
        if (!$tugas->dibuat_oleh) {
            return;
        }

        $pembuat = $this->getActiveUser($tugas->dibuat_oleh);
        
        if ($pembuat) {
            $pesan = "Surat Tugas {$tugas->nomor} memerlukan revisi.";
            if ($catatan) {
                $pesan .= " Catatan: {$catatan}";
            }

            $this->createNotification($tugas->dibuat_oleh, $tugas->id, $pesan);

            $this->logNotificationActivity('revision_requested', $tugas->id, [
                'pembuat_id' => $tugas->dibuat_oleh,
                'catatan' => $catatan
            ]);
        }
    }

    /**
     * Get active internal recipients
     *
     * @param TugasHeader $tugas
     * @return array
     */
    private function getActiveInternalRecipients(TugasHeader $tugas): array
    {
        try {
            return DB::table('tugas_penerima as tp')
                ->join('users as u', 'u.id', '=', 'tp.pengguna_id')
                ->where('tp.tugas_id', $tugas->id)
                ->whereNotNull('tp.pengguna_id')
                ->where('u.status', 'active')
                ->whereNotNull('u.email')
                ->select([
                    'u.id as pengguna_id',
                    'u.nama_lengkap as nama',
                    'u.email as email',
                ])
                ->distinct()
                ->get()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('Failed to get internal recipients', [
                'tugas_id' => $tugas->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
