<?php

namespace App\Observers;

use App\Jobs\SendSuratTugasEmail;
use App\Models\TugasHeader;
use Illuminate\Support\Facades\Log;
use App\Services\SuratTugasNotificationService;


class TugasHeaderObserver
{
    
    /**
     * Handle the TugasHeader "creating" event.
     * Auto-fill fields saat record baru dibuat
     */
    public function creating(TugasHeader $tugas): void
    {
        // Auto-set pembuat jika belum diisi
        if (!$tugas->dibuat_oleh && auth()->check()) {
            $tugas->dibuat_oleh = auth()->id();
        }

        // Auto-set tahun jika belum diisi
        if (!$tugas->tahun) {
            $tugas->tahun = now()->year;
        }

        // Auto-set bulan jika belum diisi dan ada tanggal_surat
        if (!$tugas->bulan && $tugas->tanggal_surat) {
            $tugas->bulan = now()->format('m');
        }
    }

    /**
     * Handle the TugasHeader "updated" event.
     * Trigger notifications dan automation saat update
     */
    public function updated(TugasHeader $tugas): void
    {
        // 1. Saat status berubah ke PENDING → kirim email ke approver
        if ($tugas->wasChanged('status_surat') && 
            $tugas->status_surat === 'pending' && 
            $tugas->next_approver) {
            
            try {
                SendSuratTugasEmail::dispatch($tugas->id, 'to_approver');
                
                Log::info('Email approval dispatched', [
                    'tugas_id' => $tugas->id,
                    'approver_id' => $tugas->next_approver
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch approval email', [
                    'tugas_id' => $tugas->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 2. Saat status berubah ke DISETUJUI → auto-set signed_at
        if ($tugas->wasChanged('status_surat') && 
            $tugas->status_surat === 'disetujui') {
            
            // Set timestamp jika belum ada
            if (!$tugas->signed_at) {
                $tugas->signed_at = now();
                $tugas->saveQuietly(); // Save tanpa trigger observer lagi
            }
        }

        // 3. Saat PDF final tersedia → kirim ke penerima
        if ($tugas->wasChanged('signed_pdf_path') && $tugas->signed_pdf_path) {
            
            try {
                SendSuratTugasEmail::dispatch($tugas->id, 'to_recipients');
                
                Log::info('Email to recipients dispatched', [
                    'tugas_id' => $tugas->id,
                    'pdf_path' => $tugas->signed_pdf_path
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch recipient email', [
                    'tugas_id' => $tugas->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // 4. Log perubahan status untuk audit trail
        if ($tugas->wasChanged('status_surat')) {
            Log::info('Status surat tugas changed', [
                'tugas_id' => $tugas->id,
                'old_status' => $tugas->getOriginal('status_surat'),
                'new_status' => $tugas->status_surat,
                'changed_by' => auth()->id() ?? 'system'
            ]);
        }
    }

    /**
     * Handle the TugasHeader "deleting" event.
     * Prevent deletion jika status bukan draft
     */
    public function deleting(TugasHeader $tugas): bool
    {
        // Hanya izinkan delete jika status draft
        if ($tugas->status_surat !== 'draft') {
            Log::warning('Attempted to delete non-draft surat tugas', [
                'tugas_id' => $tugas->id,
                'status' => $tugas->status_surat,
                'user_id' => auth()->id()
            ]);
            
            // Return false untuk cancel deletion
            return false;
        }

        return true;
    }

    /**
     * Handle the TugasHeader "deleted" event.
     * Cleanup related data
     */
    public function deleted(TugasHeader $tugas): void
    {
        Log::info('Surat tugas deleted', [
            'tugas_id' => $tugas->id,
            'deleted_by' => auth()->id()
        ]);

        // Optional: Cleanup penerima jika cascade delete tidak aktif
        // $tugas->penerima()->delete();
    }
}
