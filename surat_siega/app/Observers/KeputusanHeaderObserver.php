<?php

namespace App\Observers;

use App\Models\KeputusanHeader;
use App\Services\AuditService;
use Illuminate\Support\Facades\Log;

/**
 * KeputusanHeaderObserver - Observer untuk Surat Keputusan.
 * Menangani audit logging untuk SK.
 */
class KeputusanHeaderObserver
{
    /**
     * Handle the KeputusanHeader "creating" event.
     */
    public function creating(KeputusanHeader $sk): void
    {
        // Auto-set pembuat
        if (! $sk->dibuat_oleh && auth()->check()) {
            $sk->dibuat_oleh = auth()->id();
        }

        // Auto-set tahun
        if (! $sk->tahun) {
            $sk->tahun = now()->year;
        }

        // Auto-set status
        if (! $sk->status_surat) {
            $sk->status_surat = 'draft';
        }
    }

    /**
     * Handle the KeputusanHeader "created" event.
     */
    public function created(KeputusanHeader $sk): void
    {
        Log::info('Surat keputusan created', [
            'sk_id' => $sk->id,
            'nomor' => sanitize_log_message($sk->nomor ?? '(belum ada)'),
            'created_by' => validate_integer_id(auth()->id()) ?? 'system',
        ]);

        // Audit logging
        app(AuditService::class)->logCreate($sk);
    }

    /**
     * Handle the KeputusanHeader "updated" event.
     */
    public function updated(KeputusanHeader $sk): void
    {
        $original = $sk->getOriginal();

        // Log status changes
        if ($sk->wasChanged('status_surat')) {
            $oldStatus = $original['status_surat'] ?? 'unknown';
            $newStatus = $sk->status_surat;

            Log::info('Status surat keputusan changed', [
                'sk_id' => $sk->id,
                'old_status' => sanitize_log_message($oldStatus),
                'new_status' => sanitize_log_message($newStatus),
                'changed_by' => validate_integer_id(auth()->id()) ?? 'system',
            ]);

            // Log specific actions based on new status
            $auditService = app(AuditService::class);

            switch ($newStatus) {
                case 'pending':
                    $auditService->logSubmit($sk);
                    break;
                case 'disetujui':
                    $auditService->logApprove($sk);
                    break;
                case 'ditolak':
                    $auditService->logReject($sk, $sk->alasan_tolak ?? null);
                    break;
                case 'terbit':
                    $auditService->logPublish($sk);
                    break;
                case 'arsip':
                    $auditService->logArchive($sk);
                    break;
                default:
                    $auditService->logUpdate($sk, $original);
            }
        } else {
            // Regular update
            app(AuditService::class)->logUpdate($sk, $original);
        }
    }

    /**
     * Handle the KeputusanHeader "deleting" event.
     */
    public function deleting(KeputusanHeader $sk): void
    {
        // Only allow delete if draft — throw to actually cancel deletion
        if ($sk->status_surat !== 'draft') {
            Log::warning('Attempted to delete non-draft surat keputusan', [
                'sk_id' => $sk->id,
                'status' => sanitize_log_message($sk->status_surat),
                'user_id' => validate_integer_id(auth()->id()),
            ]);

            throw new \RuntimeException('Hanya SK dengan status draft yang dapat dihapus.');
        }
    }

    /**
     * Handle the KeputusanHeader "deleted" event.
     */
    public function deleted(KeputusanHeader $sk): void
    {
        Log::info('Surat keputusan deleted', [
            'sk_id' => $sk->id,
            'nomor' => sanitize_log_message($sk->nomor ?? '(kosong)'),
            'deleted_by' => validate_integer_id(auth()->id()) ?? 'system',
        ]);

        app(AuditService::class)->logDelete($sk);
    }

    /**
     * Handle the KeputusanHeader "restored" event.
     */
    public function restored(KeputusanHeader $sk): void
    {
        Log::info('Surat keputusan restored', [
            'sk_id' => $sk->id,
            'nomor' => sanitize_log_message($sk->nomor ?? '(kosong)'),
            'restored_by' => validate_integer_id(auth()->id()) ?? 'system',
        ]);
    }
}
