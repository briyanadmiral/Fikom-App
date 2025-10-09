<?php

namespace App\Policies;

use App\Models\TugasHeader;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Policy untuk mengatur authorization Surat Tugas
 * 
 * Role ID:
 * - 1: Admin TU (Admin Tata Usaha)
 * - 2: Dekan
 * - 3: Wakil Dekan
 * 
 * Status Surat:
 * - draft: Baru dibuat, belum disubmit
 * - pending: Menunggu approval
 * - disetujui: Sudah diapprove dan final
 */
class TugasHeaderPolicy
{
    /**
     * Determine whether the user can view any models (list page).
     * Hanya Admin TU yang dapat melihat semua surat tugas.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model detail.
     * 
     * Yang boleh view:
     * 1. Admin TU (Role 1)
     * 2. Pembuat surat
     * 3. Penandatangan surat
     * 4. Next approver (yang ditugaskan untuk approve)
     * 5. Penerima surat
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return bool
     */
    public function view(User $user, TugasHeader $tugas): bool
    {
        // Admin TU boleh lihat semua
        if ($user->isAdmin()) {
            return true;
        }

        // Cek apakah user adalah penerima surat
        $isRecipient = $tugas->penerima()
            ->where('pengguna_id', $user->id)
            ->exists();

        // Allow jika user adalah pembuat, penandatangan, next_approver, atau penerima
        return $user->id === $tugas->dibuat_oleh ||
               $user->id === $tugas->penandatangan ||
               $user->id === $tugas->next_approver ||  // FIXED: Ditambahkan
               $isRecipient;
    }

    /**
     * Determine whether the user can create models.
     * Hanya Admin TU yang dapat membuat surat tugas baru.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * 
     * KASUS 1: Admin TU dapat edit DRAFT yang dibuat sendiri
     * KASUS 2: Approver (Dekan/WD) dapat edit surat PENDING untuk koreksi
     * 
     * Surat yang sudah DISETUJUI tidak boleh diubah oleh siapapun.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return bool
     */
    public function update(User $user, TugasHeader $tugas): bool
    {
        // GUARD: Surat yang sudah disetujui tidak boleh diubah
        if ($tugas->status_surat === 'disetujui') {
            $this->logUnauthorizedAttempt($user, 'update', $tugas, 'Surat sudah disetujui');
            return false;
        }

        // KASUS 1: Admin TU edit draft miliknya
        $adminEditDraft = $user->isAdmin() &&
                          $user->id === $tugas->dibuat_oleh &&
                          $tugas->status_surat === 'draft';

        // KASUS 2: Approver melakukan koreksi pada surat PENDING
        // FIXED: Ganti penandatangan ke next_approver
        $approverCorrectsPending = $user->canApproveSurat() &&
                                   $user->id === $tugas->next_approver &&
                                   $tugas->status_surat === 'pending';

        return $adminEditDraft || $approverCorrectsPending;
    }

    /**
     * Determine whether the user can delete the model.
     * Hanya Admin TU yang dapat menghapus DRAFT miliknya sendiri.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return bool
     */
    public function delete(User $user, TugasHeader $tugas): bool
    {
        $canDelete = $user->isAdmin() &&
                     $user->id === $tugas->dibuat_oleh &&
                     $tugas->status_surat === 'draft';

        if (!$canDelete && $tugas->status_surat !== 'draft') {
            $this->logUnauthorizedAttempt($user, 'delete', $tugas, 'Hanya draft yang bisa dihapus');
        }

        return $canDelete;
    }

    /**
     * Determine whether the user can approve the model.
     * 
     * Hanya Dekan/WD (Role 2, 3) yang merupakan next_approver
     * dan surat berstatus PENDING yang dapat melakukan approval.
     * 
     * CRITICAL FIX: Sebelumnya check 'penandatangan', sekarang 'next_approver'
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return bool
     */
    public function approve(User $user, TugasHeader $tugas): bool
    {
        $canApprove = $user->canApproveSurat() &&
                      $user->id === $tugas->next_approver &&  // FIXED: Dari penandatangan
                      $tugas->status_surat === 'pending';

        if (!$canApprove) {
            $reason = $this->getApprovalDenialReason($user, $tugas);
            $this->logUnauthorizedAttempt($user, 'approve', $tugas, $reason);
        }

        return $canApprove;
    }

    /**
     * Determine whether the user can add recipients to the model.
     * Hanya pembuat surat yang dapat menambah penerima, dan hanya saat DRAFT.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return bool
     */
    public function addRecipient(User $user, TugasHeader $tugas): bool
    {
        return $user->id === $tugas->dibuat_oleh &&
               $tugas->status_surat === 'draft';
    }

    /**
     * Determine whether the user can view the approval list.
     * Hanya Dekan/WD yang dapat melihat daftar surat untuk diapprove.
     * 
     * NEW METHOD: Untuk replace Gate 'view-approve-list'
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewApproveList(User $user): bool
    {
        return $user->canApproveSurat();
    }

    /**
     * Determine whether the user can restore the model.
     * Currently disabled - soft delete not implemented.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return bool
     */
    public function restore(User $user, TugasHeader $tugas): bool
    {
        // Jika nanti implement SoftDeletes, uncomment:
        // return $user->isAdmin() && $user->id === $tugas->dibuat_oleh;
        
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Currently disabled - soft delete not implemented.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return bool
     */
    public function forceDelete(User $user, TugasHeader $tugas): bool
    {
        // Jika nanti implement SoftDeletes, uncomment:
        // return $user->isAdmin() && $user->peran_id === 1; // Super admin only
        
        return false;
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get detailed reason why approval was denied.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TugasHeader  $tugas
     * @return string
     */
    private function getApprovalDenialReason(User $user, TugasHeader $tugas): string
    {
        if (!$user->canApproveSurat()) {
            return 'User tidak memiliki role approver';
        }

        if ($tugas->status_surat !== 'pending') {
            return "Status surat adalah '{$tugas->status_surat}', bukan pending";
        }

        if ($user->id !== $tugas->next_approver) {
            return "User bukan next_approver yang ditunjuk (next_approver: {$tugas->next_approver})";
        }

        return 'Unknown reason';
    }

    /**
     * Log unauthorized access attempts for audit trail.
     *
     * @param  \App\Models\User  $user
     * @param  string  $action
     * @param  \App\Models\TugasHeader  $tugas
     * @param  string  $reason
     * @return void
     */
    private function logUnauthorizedAttempt(User $user, string $action, TugasHeader $tugas, string $reason): void
    {
        Log::warning('Unauthorized access attempt to TugasHeader', [
            'user_id' => $user->id,
            'user_role' => $user->peran_id,
            'action' => $action,
            'tugas_id' => $tugas->id,
            'tugas_status' => $tugas->status_surat,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
