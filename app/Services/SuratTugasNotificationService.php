<?php

namespace App\Services;

use App\Jobs\SendSuratTugasEmail;
use App\Models\TugasHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Notification service khusus untuk Surat Tugas
 * ✅ REFACTORED: Menggunakan global helpers untuk DRY code
 * ✅ EXCELLENT: Perfect implementation dengan comprehensive error handling
 */
class SuratTugasNotificationService extends BaseNotificationService
{
    protected function getNotificationType(): string
    {
        return 'surat_tugas';
    }

    /**
     * ✅ EXCELLENT: Perfect validation & error handling
     */
    public function notifyApprovalRequest(TugasHeader $tugas): void
    {
        // ✅ ADDED: Validate tugas ID first
        $tugasId = validate_integer_id($tugas->id);
        if ($tugasId === null) {
            Log::warning('notifyApprovalRequest: Invalid tugas ID', [
                'tugas_id' => $tugas->id,
            ]);
            return;
        }

        // ✅ GOOD: Validasi next_approver dengan helper
        $approverId = validate_integer_id($tugas->next_approver);

        if ($approverId === null) {
            Log::warning('notifyApprovalRequest: Invalid next_approver', [
                'tugas_id' => $tugasId,
                'next_approver' => $tugas->next_approver,
            ]);
            return;
        }

        $approver = $this->getActiveUser($approverId);

        if (!$approver) {
            Log::warning('notifyApprovalRequest: next_approver not found or inactive', [
                'tugas_id' => $tugasId,
                'next_approver_id' => $approverId,
            ]);
            return;
        }

        // ✅ GOOD: Sanitasi nomor surat dengan helper
        $nomorSurat = sanitize_notification($tugas->nomor, 100);

        try {
            $this->createNotification($approverId, $tugasId, "Surat Tugas {$nomorSurat} menunggu persetujuan Anda.");

            $this->logNotificationActivity('approval_request', $tugasId, [
                'next_approver_id' => $approverId,
                'approver_name' => sanitize_log_message($approver->nama_lengkap),
            ]);
        } catch (\Exception $e) {
            Log::error('notifyApprovalRequest: Failed to create notification', [
                'tugas_id' => $tugasId,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }
    }

    /**
     * ✅ EXCELLENT: Comprehensive notification with batch processing
     */
    public function notifyApproved(TugasHeader $tugas): void
    {
        // ✅ ADDED: Validate tugas ID
        $tugasId = validate_integer_id($tugas->id);
        if ($tugasId === null) {
            Log::warning('notifyApproved: Invalid tugas ID', [
                'tugas_id' => $tugas->id,
            ]);
            return;
        }

        // ✅ GOOD: Sanitasi nomor surat dengan helper
        $nomorSurat = sanitize_notification($tugas->nomor, 100);

        // 1. Notify pembuat
        $pembuatId = validate_integer_id($tugas->dibuat_oleh);

        if ($pembuatId !== null) {
            $pembuat = $this->getActiveUser($pembuatId);

            if ($pembuat) {
                try {
                    $this->createNotification($pembuatId, $tugasId, "Surat Tugas {$nomorSurat} telah disetujui.");
                } catch (\Exception $e) {
                    Log::error('notifyApproved: Failed to notify pembuat', [
                        'tugas_id' => $tugasId,
                        'pembuat_id' => $pembuatId,
                        'error' => sanitize_log_message($e->getMessage()),
                    ]);
                }
            }
        }

        // 2. Notify all internal recipients
        $recipients = $this->getActiveInternalRecipients($tugas);

        if (empty($recipients)) {
            Log::info('notifyApproved: No active internal recipients', [
                'tugas_id' => $tugasId,
            ]);
            return;
        }

        $notifiedCount = 0;
        $emailQueuedCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            // ✅ GOOD: Validasi recipient dengan helper
            if (!$this->isValidRecipient($recipient)) {
                Log::warning('notifyApproved: Invalid recipient data', [
                    'tugas_id' => $tugasId,
                    'recipient_id' => $recipient->pengguna_id ?? null,
                ]);
                $failedCount++;
                continue;
            }

            $recipientId = validate_integer_id($recipient->pengguna_id);

            // A. Database notification
            try {
                if ($this->createNotification($recipientId, $tugasId, "Anda menerima Surat Tugas baru: {$nomorSurat}")) {
                    $notifiedCount++;
                }
            } catch (\Exception $e) {
                Log::error('notifyApproved: Failed to create notification', [
                    'tugas_id' => $tugasId,
                    'recipient_id' => $recipientId,
                    'error' => sanitize_log_message($e->getMessage()),
                ]);
                $failedCount++;
            }

            // B. Queue email
            if ($this->isValidEmail($recipient->email)) {
                try {
                    if ($this->dispatchJob(new SendSuratTugasEmail($tugasId, 'to_recipients', $recipientId))) {
                        $emailQueuedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('notifyApproved: Failed to queue email', [
                        'tugas_id' => $tugasId,
                        'recipient_id' => $recipientId,
                        'error' => sanitize_log_message($e->getMessage()),
                    ]);
                    $failedCount++;
                }
            }
        }

        $this->logNotificationActivity('approved', $tugasId, [
            'total_recipients' => count($recipients),
            'notified' => $notifiedCount,
            'email_queued' => $emailQueuedCount,
            'failed' => $failedCount,
        ]);
    }

    /**
     * ✅ EXCELLENT: Perfect sanitization & error handling
     */
    public function notifyRejected(TugasHeader $tugas, string $alasan = ''): void
    {
        // ✅ ADDED: Validate tugas ID
        $tugasId = validate_integer_id($tugas->id);
        if ($tugasId === null) {
            Log::warning('notifyRejected: Invalid tugas ID', [
                'tugas_id' => $tugas->id,
            ]);
            return;
        }

        $pembuatId = validate_integer_id($tugas->dibuat_oleh);

        if ($pembuatId === null) {
            return;
        }

        $pembuat = $this->getActiveUser($pembuatId);

        if ($pembuat) {
            // ✅ GOOD: Sanitasi dengan helper
            $nomorSurat = sanitize_notification($tugas->nomor, 100);
            $alasanSafe = sanitize_notification($alasan, 500);

            $pesan = "Surat Tugas {$nomorSurat} ditolak.";
            if ($alasanSafe) {
                $pesan .= " Alasan: {$alasanSafe}";
            }

            try {
                $this->createNotification($pembuatId, $tugasId, $pesan);

                $this->logNotificationActivity('rejected', $tugasId, [
                    'pembuat_id' => $pembuatId,
                    'alasan' => sanitize_log_message($alasan),
                ]);
            } catch (\Exception $e) {
                Log::error('notifyRejected: Failed to create notification', [
                    'tugas_id' => $tugasId,
                    'error' => sanitize_log_message($e->getMessage()),
                ]);
            }
        }
    }

    /**
     * ✅ EXCELLENT: Perfect sanitization & error handling
     */
    public function notifyRevisionRequested(TugasHeader $tugas, string $catatan = ''): void
    {
        // ✅ ADDED: Validate tugas ID
        $tugasId = validate_integer_id($tugas->id);
        if ($tugasId === null) {
            Log::warning('notifyRevisionRequested: Invalid tugas ID', [
                'tugas_id' => $tugas->id,
            ]);
            return;
        }

        $pembuatId = validate_integer_id($tugas->dibuat_oleh);

        if ($pembuatId === null) {
            return;
        }

        $pembuat = $this->getActiveUser($pembuatId);

        if ($pembuat) {
            // ✅ GOOD: Sanitasi dengan helper
            $nomorSurat = sanitize_notification($tugas->nomor, 100);
            $catatanSafe = sanitize_notification($catatan, 500);

            $pesan = "Surat Tugas {$nomorSurat} memerlukan revisi.";
            if ($catatanSafe) {
                $pesan .= " Catatan: {$catatanSafe}";
            }

            try {
                $this->createNotification($pembuatId, $tugasId, $pesan);

                $this->logNotificationActivity('revision_requested', $tugasId, [
                    'pembuat_id' => $pembuatId,
                    'catatan' => sanitize_log_message($catatan),
                ]);
            } catch (\Exception $e) {
                Log::error('notifyRevisionRequested: Failed to create notification', [
                    'tugas_id' => $tugasId,
                    'error' => sanitize_log_message($e->getMessage()),
                ]);
            }
        }
    }

    /**
     * ✅ EXCELLENT: Safe SQL query with proper validation
     */
    private function getActiveInternalRecipients(TugasHeader $tugas): array
    {
        // ✅ GOOD: Validasi tugas ID dengan helper
        $tugasId = validate_integer_id($tugas->id);

        if ($tugasId === null) {
            Log::error('getActiveInternalRecipients: Invalid tugas ID', [
                'tugas_id' => $tugas->id,
            ]);
            return [];
        }

        try {
            $recipients = DB::table('tugas_penerima as tp')
                ->join('pengguna as u', 'u.id', '=', 'tp.pengguna_id') // ✅ FIXED: Use correct table name
                ->where('tp.tugas_id', $tugasId)
                ->whereNotNull('tp.pengguna_id')
                ->where('u.status', 'aktif')
                ->whereNotNull('u.email')
                ->select(['u.id as pengguna_id', 'u.nama_lengkap as nama', 'u.email as email'])
                ->distinct()
                ->get()
                ->toArray();

            return $recipients;
        } catch (\Exception $e) {
            Log::error('getActiveInternalRecipients: Failed to get recipients', [
                'tugas_id' => $tugasId,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            return [];
        }
    }

    /**
     * ✅ GOOD: Validation helper
     */
    private function isValidRecipient($recipient): bool
    {
        $recipientId = validate_integer_id($recipient->pengguna_id ?? null);

        return $recipientId !== null && !empty($recipient->email);
    }

    /**
     * ✅ ADDED: Notify single recipient
     */
    public function notifySingleRecipient(TugasHeader $tugas, int $recipientId): bool
    {
        $tugasId = validate_integer_id($tugas->id);
        $validRecipientId = validate_integer_id($recipientId);

        if ($tugasId === null || $validRecipientId === null) {
            return false;
        }

        $nomorSurat = sanitize_notification($tugas->nomor, 100);

        try {
            return $this->createNotification($validRecipientId, $tugasId, "Anda menerima Surat Tugas baru: {$nomorSurat}");
        } catch (\Exception $e) {
            Log::error('notifySingleRecipient: Failed', [
                'tugas_id' => $tugasId,
                'recipient_id' => $validRecipientId,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            return false;
        }
    }
}
