<?php

namespace App\Observers;

use App\Jobs\SendSuratTugasEmail;
use App\Models\TugasHeader;
use App\Services\AuditService;
use Illuminate\Support\Facades\Log;

/**
 * TugasHeaderObserver - Observer untuk Surat Tugas.
 * Security enhanced dengan sanitized logging.
 */
class TugasHeaderObserver
{
    /**
     * Handle the TugasHeader "creating" event.
     */
    public function creating(TugasHeader $tugas): void
    {
        // Auto-set pembuat dengan validasi
        if (! $tugas->dibuat_oleh && auth()->check()) {
            $userId = validate_integer_id(auth()->id());
            if ($userId !== null) {
                $tugas->dibuat_oleh = $userId;
            }
        }

        // Auto-set tahun jika belum diisi
        if (! $tugas->tahun) {
            $tugas->tahun = now()->year;
        }

        // Auto-set bulan jika belum diisi
        if (! $tugas->bulan && $tugas->tanggal_surat) {
            $tugas->bulan = now()->format('m');
        }

        // Auto-set status jika belum diisi
        if (! $tugas->status_surat) {
            $tugas->status_surat = 'draft';
        }

        // Log creation (debug level to avoid noise)
        Log::debug('Surat tugas creating', [
            'dibuat_oleh' => $tugas->dibuat_oleh,
            'tahun' => $tugas->tahun,
            'status' => $tugas->status_surat,
        ]);
    }

    /**
     * Handle the TugasHeader "created" event.
     */
    public function created(TugasHeader $tugas): void
    {
        $userId = validate_integer_id(auth()->id());

        Log::info('Surat tugas created', [
            'tugas_id' => $tugas->id,
            'nomor' => sanitize_log_message($tugas->nomor ?? '(belum ada)'),
            'created_by' => $userId ?? 'system',
        ]);

        // Audit logging
        app(AuditService::class)->logCreate($tugas);
    }

    /**
     * Handle the TugasHeader "updated" event.
     */
    public function updated(TugasHeader $tugas): void
    {
        // 1. Status → PENDING: Kirim email ke approver
        if ($tugas->wasChanged('status_surat') && $tugas->status_surat === 'pending' && $tugas->next_approver) {
            $approverId = validate_integer_id($tugas->next_approver);

            if ($approverId !== null) {
                try {
                    SendSuratTugasEmail::dispatch($tugas->id, 'to_approver');

                    Log::info('Email approval dispatched', [
                        'tugas_id' => $tugas->id,
                        'approver_id' => $approverId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch approval email', [
                        'tugas_id' => $tugas->id,
                        'error' => sanitize_log_message($e->getMessage()),
                    ]);
                }
            }
        }

        // 2. Status → DISETUJUI: Auto-set signed_at
        if ($tugas->wasChanged('status_surat') && $tugas->status_surat === 'disetujui') {
            if (! $tugas->signed_at) {
                // Use try-catch for saveQuietly
                try {
                    $tugas->signed_at = now();
                    $tugas->saveQuietly();

                    Log::info('Signed_at auto-set', [
                        'tugas_id' => $tugas->id,
                        'signed_at' => $tugas->signed_at->toDateTimeString(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to auto-set signed_at', [
                        'tugas_id' => $tugas->id,
                        'error' => sanitize_log_message($e->getMessage()),
                    ]);
                }
            }
        }

        // 3. PDF tersedia → Kirim ke penerima
        if ($tugas->wasChanged('signed_pdf_path') && $tugas->signed_pdf_path) {
            $validPath = validate_file_path($tugas->signed_pdf_path);

            if ($validPath !== null) {
                try {
                    SendSuratTugasEmail::dispatch($tugas->id, 'to_recipients');

                    Log::info('Email to recipients dispatched', [
                        'tugas_id' => $tugas->id,
                        'pdf_path' => sanitize_log_message($validPath),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch recipient email', [
                        'tugas_id' => $tugas->id,
                        'error' => sanitize_log_message($e->getMessage()),
                    ]);
                }
            } else {
                // Log invalid path
                Log::warning('Invalid signed_pdf_path', [
                    'tugas_id' => $tugas->id,
                    'path' => sanitize_log_message($tugas->signed_pdf_path),
                ]);
            }
        }

        // 4. Log perubahan status dengan sanitasi
        if ($tugas->wasChanged('status_surat')) {
            $allowedStatuses = ['draft', 'pending', 'disetujui', 'ditolak'];
            $oldStatus = validate_status($tugas->getOriginal('status_surat'), $allowedStatuses);
            $newStatus = validate_status($tugas->status_surat, $allowedStatuses);
            $userId = validate_integer_id(auth()->id());

            Log::info('Status surat tugas changed', [
                'tugas_id' => $tugas->id,
                'old_status' => sanitize_log_message($oldStatus ?? 'unknown'),
                'new_status' => sanitize_log_message($newStatus ?? 'unknown'),
                'changed_by' => $userId ?? 'system',
            ]);
        }

        // Log nomor changes
        if ($tugas->wasChanged('nomor')) {
            Log::info('Nomor surat tugas changed', [
                'tugas_id' => $tugas->id,
                'old_nomor' => sanitize_log_message($tugas->getOriginal('nomor') ?? '(kosong)'),
                'new_nomor' => sanitize_log_message($tugas->nomor ?? '(kosong)'),
            ]);
        }
    }

    /**
     * Handle the TugasHeader "deleting" event.
     */
    public function deleting(TugasHeader $tugas): void
    {
        $status = validate_status($tugas->status_surat, ['draft', 'pending', 'disetujui', 'ditolak']);

        // Hanya izinkan delete jika status draft — throw to actually cancel
        if ($status !== 'draft') {
            Log::warning('Attempted to delete non-draft surat tugas', [
                'tugas_id' => $tugas->id,
                'nomor' => sanitize_log_message($tugas->nomor ?? '(kosong)'),
                'status' => sanitize_log_message($status ?? 'unknown'),
                'user_id' => validate_integer_id(auth()->id()),
            ]);

            throw new \RuntimeException('Hanya surat tugas draft yang dapat dihapus.');
        }

        // Check if has penerima
        $penerimaCount = $tugas->penerima()->count();
        if ($penerimaCount > 0) {
            Log::info('Deleting surat tugas with penerima', [
                'tugas_id' => $tugas->id,
                'penerima_count' => $penerimaCount,
            ]);
        }
    }

    /**
     * Handle the TugasHeader "deleted" event.
     */
    public function deleted(TugasHeader $tugas): void
    {
        $userId = validate_integer_id(auth()->id());

        Log::info('Surat tugas deleted', [
            'tugas_id' => $tugas->id,
            'nomor' => sanitize_log_message($tugas->nomor ?? '(kosong)'),
            'deleted_by' => $userId ?? 'system',
        ]);

        // Optional: Cleanup related data jika cascade delete tidak aktif
        // Note: Jika ada soft delete, data masih bisa di-restore
        // $tugas->penerima()->delete();
    }

    /**
     * Handle the TugasHeader "restored" event.
     */
    public function restored(TugasHeader $tugas): void
    {
        $userId = validate_integer_id(auth()->id());

        Log::info('Surat tugas restored', [
            'tugas_id' => $tugas->id,
            'nomor' => sanitize_log_message($tugas->nomor ?? '(kosong)'),
            'restored_by' => $userId ?? 'system',
        ]);
    }

    /**
     * Handle the TugasHeader "forceDeleted" event.
     */
    public function forceDeleted(TugasHeader $tugas): void
    {
        $userId = validate_integer_id(auth()->id());

        Log::warning('Surat tugas force deleted', [
            'tugas_id' => $tugas->id,
            'nomor' => sanitize_log_message($tugas->nomor ?? '(kosong)'),
            'force_deleted_by' => $userId ?? 'system',
        ]);

        // Cleanup related data on force delete
        try {
            // Force delete penerima
            $tugas->penerima()->forceDelete();

            // Force delete logs
            $tugas->log()->forceDelete();

            Log::info('Related data force deleted', [
                'tugas_id' => $tugas->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to force delete related data', [
                'tugas_id' => $tugas->id,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
        }
    }

    /**
     * Handle the TugasHeader "saving" event.
     * Validation before save.
     */
    public function saving(TugasHeader $tugas): void
    {
        // Validate required fields
        if ($tugas->status_surat === 'pending' && empty($tugas->nomor)) {
            Log::warning('Attempted to set pending status without nomor', [
                'tugas_id' => $tugas->id ?? 'new',
            ]);
        }

        // Validate approver for pending status
        if ($tugas->status_surat === 'pending' && empty($tugas->next_approver)) {
            Log::warning('Attempted to set pending status without approver', [
                'tugas_id' => $tugas->id ?? 'new',
            ]);
        }
    }
}
