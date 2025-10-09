<?php

namespace App\Services;

use App\Jobs\SendSkEmail;
use App\Mail\SuratKeputusanMail;
use App\Models\KeputusanHeader;
use Illuminate\Support\Facades\Mail;

/**
 * Notification service khusus untuk Surat Keputusan
 * Extends dari BaseNotificationService untuk shared functionality
 * Enhanced dari SkNotifikasiService.php dengan error handling yang lebih baik
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
     * Notifikasi saat SK diajukan untuk approval
     * CRITICAL FIX: Check next_approver dulu, fallback ke penandatangan jika perlu
     *
     * @param KeputusanHeader $sk
     * @return void
     */
    public function notifyApprovalRequest(KeputusanHeader $sk): void
    {
        // RECOMMENDED: Jika SK juga punya next_approver field, pakai itu
        // Jika tidak, gunakan penandatangan
        $approverId = $sk->next_approver ?? $sk->penandatangan;

        if (!$approverId) {
            return;
        }

        $approver = $this->getActiveUser($approverId);
        
        if (!$approver) {
            return;
        }

        $this->createNotification(
            $approverId,
            $sk->id,
            "Surat Keputusan " . ($sk->nomor ?: '(draft)') . " menunggu persetujuan Anda."
        );

        $this->logNotificationActivity('approval_request', $sk->id, [
            'approver_id' => $approverId,
            'approver_name' => $approver->nama_lengkap
        ]);
    }

    /**
     * Notifikasi saat SK disetujui
     * Notify pembuat + kirim email via queue
     *
     * @param KeputusanHeader $sk
     * @return void
     */
    public function notifyApproved(KeputusanHeader $sk): void
    {
        if (!$sk->dibuat_oleh) {
            return;
        }

        $pembuat = $this->getActiveUser($sk->dibuat_oleh);
        
        if (!$pembuat) {
            return;
        }

        // 1. Database notification
        $this->createNotification(
            $sk->dibuat_oleh,
            $sk->id,
            "Surat Keputusan " . ($sk->nomor ?: '(tanpa nomor)') . " telah disetujui."
        );

        // 2. Queue email dengan PDF
        $this->dispatchJob(new SendSkEmail($sk->id));

        $this->logNotificationActivity('approved', $sk->id, [
            'pembuat_id' => $sk->dibuat_oleh,
            'pembuat_name' => $pembuat->nama_lengkap
        ]);
    }

    /**
     * Notifikasi saat SK ditolak
     *
     * @param KeputusanHeader $sk
     * @param string|null $note Catatan penolakan
     * @return void
     */
    public function notifyRejected(KeputusanHeader $sk, ?string $note = null): void
    {
        if (!$sk->dibuat_oleh) {
            return;
        }

        $pembuat = $this->getActiveUser($sk->dibuat_oleh);
        
        if (!$pembuat) {
            return;
        }

        // 1. Database notification
        $pesan = "Surat Keputusan " . ($sk->nomor ?: '(tanpa nomor)') . " ditolak.";
        if ($note) {
            $pesan .= " Catatan: {$note}";
        }

        $this->createNotification($sk->dibuat_oleh, $sk->id, $pesan);

        // 2. Email notification
        if ($this->isValidEmail($pembuat->email)) {
            $ctaUrl = route('surat_keputusan.edit', $sk->id);
            $line = "SK Anda ditolak." . ($note ? " Catatan: {$note}" : '');

            $this->queueEmail(
                new SuratKeputusanMail(
                    sk: $sk,
                    subject: "SK " . ($sk->nomor ?: '(tanpa nomor)') . " ditolak",
                    heading: 'Surat Keputusan Ditolak',
                    messageLine: $line,
                    ctaUrl: $ctaUrl,
                    ctaText: 'Perbaiki SK',
                    attachSignedPdf: false
                ),
                $pembuat->email
            );
        }

        $this->logNotificationActivity('rejected', $sk->id, [
            'pembuat_id' => $sk->dibuat_oleh,
            'note' => $note
        ]);
    }

    /**
     * Notifikasi saat SK direvisi oleh pembuat
     *
     * @param KeputusanHeader $sk
     * @param mixed $byUser User yang melakukan revisi
     * @return void
     */
    public function notifyRevised(KeputusanHeader $sk, $byUser): void
    {
        // RECOMMENDED: Check next_approver dulu
        $approverId = $sk->next_approver ?? $sk->penandatangan;

        if (!$approverId) {
            return;
        }

        $approver = $this->getActiveUser($approverId);
        
        if (!$approver) {
            return;
        }

        // 1. Database notification
        $this->createNotification(
            $approverId,
            $sk->id,
            "SK " . ($sk->nomor ?? '(tanpa nomor)') . 
            " telah direvisi oleh " . ($byUser->nama_lengkap ?? 'pengguna') . "."
        );

        // 2. Email notification
        if ($this->isValidEmail($approver->email)) {
            $ctaUrl = route('surat_keputusan.approveForm', $sk->id);

            $this->queueEmail(
                new SuratKeputusanMail(
                    sk: $sk,
                    subject: "Revisi SK " . ($sk->nomor ?? '(tanpa nomor)'),
                    heading: 'SK Direvisi dan Menunggu Tinjauan',
                    messageLine: 'Pembuat telah memperbarui SK yang sedang menunggu persetujuan.',
                    ctaUrl: $ctaUrl,
                    ctaText: 'Tinjau Revisi',
                    attachSignedPdf: false
                ),
                $approver->email
            );
        }

        $this->logNotificationActivity('revised', $sk->id, [
            'approver_id' => $approverId,
            'revised_by' => $byUser->id ?? null
        ]);
    }
}
