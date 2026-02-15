<?php

namespace App\Services;

use App\Jobs\SendSkEmail;
use App\Mail\SuratKeputusanMail;
use App\Models\KeputusanHeader;
use Illuminate\Support\Facades\Log;

/**
 * Notification service khusus untuk Surat Keputusan.
 * Extends dari BaseNotificationService untuk shared functionality.
 * Enhanced dengan input validation & error handling.
 */
class SuratKeputusanNotificationService extends BaseNotificationService
{
    /**
     * Tipe notifikasi untuk Surat Keputusan
     */
    protected function getNotificationType(): string
    {
        return 'surat_keputusan';
    }

    /**
     * Notifikasi saat SK diajukan untuk approval.
     */
    public function notifyApprovalRequest(KeputusanHeader $sk): void
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                Log::warning('Invalid SK ID for approval request', ['sk' => $sk->id]);

                return;
            }

            // Validate approver ID
            $approverId = validate_integer_id($sk->next_approver ?? $sk->penandatangan);

            if ($approverId === null) {
                Log::warning('No valid approver for SK', [
                    'sk_id' => $skId,
                    'next_approver' => $sk->next_approver,
                    'penandatangan' => $sk->penandatangan,
                ]);

                return;
            }

            $approver = $this->getActiveUser($approverId);

            if (! $approver) {
                Log::warning('Approver not found or inactive', [
                    'sk_id' => $skId,
                    'approver_id' => $approverId,
                ]);

                return;
            }

            // Sanitize nomor surat
            $nomor = sanitize_output($sk->nomor) ?: '(draft)';

            $this->createNotification($approverId, $skId, "Surat Keputusan {$nomor} menunggu persetujuan Anda.");

            $this->logNotificationActivity('approval_request', $skId, [
                'approver_id' => $approverId,
                'approver_name' => sanitize_log_message($approver->nama_lengkap),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send approval request notification', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }
    }

    /**
     * Notifikasi saat SK disetujui.
     */
    public function notifyApproved(KeputusanHeader $sk): void
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                Log::warning('Invalid SK ID for approval notification', ['sk' => $sk->id]);

                return;
            }

            // Validate pembuat ID
            $pembuatId = validate_integer_id($sk->dibuat_oleh);

            if ($pembuatId === null) {
                Log::warning('No valid pembuat for SK', [
                    'sk_id' => $skId,
                    'dibuat_oleh' => $sk->dibuat_oleh,
                ]);

                return;
            }

            $pembuat = $this->getActiveUser($pembuatId);

            if (! $pembuat) {
                Log::warning('Pembuat not found or inactive', [
                    'sk_id' => $skId,
                    'pembuat_id' => $pembuatId,
                ]);

                return;
            }

            // Sanitize nomor surat
            $nomor = sanitize_output($sk->nomor) ?: '(tanpa nomor)';

            // 1. Database notification
            $this->createNotification($pembuatId, $skId, "Surat Keputusan {$nomor} telah disetujui.");

            // 2. Queue email dengan PDF
            $this->dispatchJob(new SendSkEmail($skId));

            $this->logNotificationActivity('approved', $skId, [
                'pembuat_id' => $pembuatId,
                'pembuat_name' => sanitize_log_message($pembuat->nama_lengkap),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send approval notification', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }
    }

    /**
     * Notifikasi saat SK ditolak.
     *
     * @param  string|null  $note  Catatan penolakan
     */
    public function notifyRejected(KeputusanHeader $sk, ?string $note = null): void
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                Log::warning('Invalid SK ID for rejection notification', ['sk' => $sk->id]);

                return;
            }

            // Validate pembuat ID
            $pembuatId = validate_integer_id($sk->dibuat_oleh);

            if ($pembuatId === null) {
                Log::warning('No valid pembuat for SK rejection', [
                    'sk_id' => $skId,
                    'dibuat_oleh' => $sk->dibuat_oleh,
                ]);

                return;
            }

            $pembuat = $this->getActiveUser($pembuatId);

            if (! $pembuat) {
                Log::warning('Pembuat not found or inactive for rejection', [
                    'sk_id' => $skId,
                    'pembuat_id' => $pembuatId,
                ]);

                return;
            }

            // Sanitize nomor surat & note
            $nomor = sanitize_output($sk->nomor) ?: '(tanpa nomor)';
            $sanitizedNote = $note ? sanitize_input($note, 500) : null;

            // 1. Database notification
            $pesan = "Surat Keputusan {$nomor} ditolak.";
            if ($sanitizedNote) {
                $pesan .= " Catatan: {$sanitizedNote}";
            }

            $this->createNotification($pembuatId, $skId, $pesan);

            // 2. Email notification
            if ($this->isValidEmail($pembuat->email)) {
                $ctaUrl = route('surat_keputusan.edit', $skId);
                $line = 'SK Anda ditolak.'.($sanitizedNote ? " Catatan: {$sanitizedNote}" : '');

                $this->queueEmail(new SuratKeputusanMail(sk: $sk, subject: "SK {$nomor} ditolak", heading: 'Surat Keputusan Ditolak', messageLine: $line, ctaUrl: $ctaUrl, ctaText: 'Perbaiki SK', attachSignedPdf: false), $pembuat->email);
            }

            $this->logNotificationActivity('rejected', $skId, [
                'pembuat_id' => $pembuatId,
                'note' => sanitize_log_message($sanitizedNote ?? '(no note)'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send rejection notification', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }
    }

    /**
     * Notifikasi saat SK direvisi oleh pembuat.
     *
     * @param  mixed  $byUser  User yang melakukan revisi
     */
    public function notifyRevised(KeputusanHeader $sk, $byUser): void
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                Log::warning('Invalid SK ID for revision notification', ['sk' => $sk->id]);

                return;
            }

            // Validate approver ID
            $approverId = validate_integer_id($sk->next_approver ?? $sk->penandatangan);

            if ($approverId === null) {
                Log::warning('No valid approver for SK revision', [
                    'sk_id' => $skId,
                    'next_approver' => $sk->next_approver,
                    'penandatangan' => $sk->penandatangan,
                ]);

                return;
            }

            $approver = $this->getActiveUser($approverId);

            if (! $approver) {
                Log::warning('Approver not found or inactive for revision', [
                    'sk_id' => $skId,
                    'approver_id' => $approverId,
                ]);

                return;
            }

            // Sanitize nomor surat & user name
            $nomor = sanitize_output($sk->nomor) ?? '(tanpa nomor)';
            $userName = sanitize_output($byUser->nama_lengkap ?? 'pengguna');

            // 1. Database notification
            $this->createNotification($approverId, $skId, "SK {$nomor} telah direvisi oleh {$userName}.");

            // 2. Email notification
            if ($this->isValidEmail($approver->email)) {
                $ctaUrl = route('surat_keputusan.approveForm', $skId);

                $this->queueEmail(new SuratKeputusanMail(sk: $sk, subject: "Revisi SK {$nomor}", heading: 'SK Direvisi dan Menunggu Tinjauan', messageLine: 'Pembuat telah memperbarui SK yang sedang menunggu persetujuan.', ctaUrl: $ctaUrl, ctaText: 'Tinjau Revisi', attachSignedPdf: false), $approver->email);
            }

            $this->logNotificationActivity('revised', $skId, [
                'approver_id' => $approverId,
                'revised_by' => validate_integer_id($byUser->id ?? null),
                'revised_by_name' => sanitize_log_message($userName),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send revision notification', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }
    }

    /**
     * Notify penerima saat SK terbit.
     */
    public function notifyRecipients(KeputusanHeader $sk): void
    {
        try {
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                return;
            }

            $nomor = sanitize_output($sk->nomor) ?: '(tanpa nomor)';

            $penerima = $sk->penerima;
            if (! $penerima || $penerima->isEmpty()) {
                Log::info('No recipients for SK', ['sk_id' => $skId]);

                return;
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($penerima as $p) {
                $userId = validate_integer_id($p->pengguna_id);

                if ($userId === null) {
                    $failedCount++;

                    continue;
                }

                $success = $this->createNotification($userId, $skId, "Anda tercantum dalam Surat Keputusan {$nomor}.");

                if ($success) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            }

            $this->logNotificationActivity('recipients_notified', $skId, [
                'total' => $penerima->count(),
                'success' => $successCount,
                'failed' => $failedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify SK recipients', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }
    }
}
