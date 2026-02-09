<?php

namespace App\Policies;

use App\Models\TugasHeader;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Policy untuk mengatur authorization Surat Tugas
 * ✅ FIXED: Block edit untuk status disetujui
 */
class TugasHeaderPolicy
{
    /**
     * Determine whether the user can view any models (list page).
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can view the tugas
     */
    public function view(User $user, TugasHeader $tugas): bool
    {
        // Admin TU (peran_id 1) bisa lihat semua
        if ($user->peran_id === 1) {
            return true;
        }

        // Pembuat bisa lihat
        if ($tugas->dibuat_oleh === $user->id) {
            return true;
        }

        // Penerima bisa lihat
        if ($tugas->penerima()->where('pengguna_id', $user->id)->exists()) {
            return true;
        }

        // Penandatangan atau next approver bisa lihat
        if ($tugas->penandatangan === $user->id || $tugas->next_approver === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * ✅ FIXED: Determine if user can update the tugas
     */
    public function update(User $user, TugasHeader $tugas): bool
    {
        // ✅ FIXED: TIDAK BOLEH edit surat yang sudah disetujui
        if ($tugas->status_surat === 'disetujui') {
            Log::info('Policy update DITOLAK: Status sudah disetujui', [
                'user_id' => $user->id,
                'tugas_id' => $tugas->id,
                'tugas_nomor' => sanitize_log_message($tugas->nomor ?? ''),
                'status' => $tugas->status_surat,
            ]);

            return false;
        }

        // Admin TU (peran_id 1) - boleh edit draft/pending/ditolak yang dia buat
        if ($user->peran_id === 1) {
            $canUpdate = $tugas->dibuat_oleh === $user->id
                && in_array($tugas->status_surat, ['draft', 'pending', 'ditolak'], true);

            Log::info('Policy update - Admin TU', [
                'user_id' => $user->id,
                'tugas_id' => $tugas->id,
                'status' => $tugas->status_surat,
                'can_update' => $canUpdate,
            ]);

            return $canUpdate;
        }

        // Dekan/WD (peran_id 2/3) - boleh edit saat pending dan dia penandatangannya
        if (in_array($user->peran_id, [2, 3], true)) {
            $canUpdate = $tugas->status_surat === 'pending'
                && (int) $tugas->penandatangan === (int) $user->id;

            Log::info('Policy update - Dekan/WD', [
                'user_id' => $user->id,
                'tugas_id' => $tugas->id,
                'status' => $tugas->status_surat,
                'penandatangan' => $tugas->penandatangan,
                'can_update' => $canUpdate,
            ]);

            return $canUpdate;
        }

        Log::warning('Policy update DITOLAK: User tidak memiliki akses', [
            'user_id' => $user->id,
            'user_peran_id' => $user->peran_id,
            'tugas_id' => $tugas->id,
        ]);

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * ✅ GOOD: Validasi ID dan status dengan helper
     */
    public function delete(User $user, TugasHeader $tugas): bool
    {
        // ✅ GOOD: Validasi ID dan status
        $userId = validate_integer_id($user->id);
        $dibuatOleh = validate_integer_id($tugas->dibuat_oleh);
        $status = validate_status($tugas->status_surat, ['draft', 'pending', 'disetujui']);

        // ✅ ADDED: Null safety check
        if ($userId === null) {
            return false;
        }

        $canDelete = $user->isAdmin() && $userId === $dibuatOleh && $status === 'draft';

        if (! $canDelete && $status !== 'draft') {
            $this->logUnauthorizedAttempt($user, 'delete', $tugas, 'Hanya draft yang bisa dihapus');
        }

        return $canDelete;
    }

    /**
     * Determine whether the user can approve the model.
     * ✅ GOOD: Validasi ID dan status dengan helper
     */
    public function approve(User $user, TugasHeader $tugas): bool
    {
        // ✅ GOOD: Validasi ID dan status
        $userId = validate_integer_id($user->id);
        $nextApprover = validate_integer_id($tugas->next_approver);
        $status = validate_status($tugas->status_surat, ['pending', 'draft', 'disetujui']);

        // ✅ ADDED: Null safety check
        if ($userId === null) {
            return false;
        }

        $canApprove = $user->canApproveSurat() && $userId === $nextApprover && $status === 'pending';

        if (! $canApprove) {
            $reason = $this->getApprovalDenialReason($user, $tugas);
            $this->logUnauthorizedAttempt($user, 'approve', $tugas, $reason);
        }

        return $canApprove;
    }

    /**
     * ✅ ADDED: Determine whether the user can reject the model.
     */
    public function reject(User $user, TugasHeader $tugas): bool
    {
        // Same logic as approve
        return $this->approve($user, $tugas);
    }

    /**
     * ✅ ADDED: Determine whether the user can submit for approval.
     */
    public function submit(User $user, TugasHeader $tugas): bool
    {
        $userId = validate_integer_id($user->id);
        $dibuatOleh = validate_integer_id($tugas->dibuat_oleh);
        $status = validate_status($tugas->status_surat, ['draft']);

        if ($userId === null || $status !== 'draft') {
            return false;
        }

        return $user->isAdmin() && $userId === $dibuatOleh;
    }

    /**
     * Determine whether the user can add recipients to the model.
     * ✅ GOOD: Validasi ID dan status dengan helper
     */
    public function addRecipient(User $user, TugasHeader $tugas): bool
    {
        // ✅ GOOD: Validasi ID dan status
        $userId = validate_integer_id($user->id);
        $dibuatOleh = validate_integer_id($tugas->dibuat_oleh);
        $status = validate_status($tugas->status_surat, ['draft', 'pending']);

        // ✅ ADDED: Null safety check
        if ($userId === null) {
            return false;
        }

        return $userId === $dibuatOleh && $status === 'draft';
    }

    /**
     * ✅ ADDED: Determine whether the user can remove recipients.
     */
    public function removeRecipient(User $user, TugasHeader $tugas): bool
    {
        // Same logic as addRecipient
        return $this->addRecipient($user, $tugas);
    }

    /**
     * Determine whether the user can view the approval list.
     */
    public function viewApproveList(User $user): bool
    {
        return $user->canApproveSurat();
    }

    /**
     * ✅ IMPROVED: Allow admin to restore
     */
    public function restore(User $user, TugasHeader $tugas): bool
    {
        return $user->isAdmin();
    }

    /**
     * ✅ IMPROVED: Allow admin to force delete (with caution)
     */
    public function forceDelete(User $user, TugasHeader $tugas): bool
    {
        // Only admin, and log the action
        if ($user->isAdmin()) {
            Log::warning('Force delete attempt on TugasHeader', [
                'user_id' => validate_integer_id($user->id),
                'tugas_id' => validate_integer_id($tugas->id),
                'nomor' => sanitize_log_message($tugas->nomor ?? '(kosong)'),
            ]);

            return true;
        }

        return false;
    }

    /**
     * ✅ ADDED: Determine whether the user can download the model.
     */
    public function download(User $user, TugasHeader $tugas): bool
    {
        // Only approved/published documents can be downloaded
        $status = validate_status($tugas->status_surat, ['disetujui']);

        if ($status !== 'disetujui') {
            return false;
        }

        // Must have view permission
        return $this->view($user, $tugas);
    }

    /**
     * ✅ ADDED: Determine whether the user can print the model.
     */
    public function print(User $user, TugasHeader $tugas): bool
    {
        // Same as download
        return $this->download($user, $tugas);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get detailed reason why approval was denied.
     * ✅ GOOD: Sanitasi output untuk log
     */
    private function getApprovalDenialReason(User $user, TugasHeader $tugas): string
    {
        if (! $user->canApproveSurat()) {
            return 'User tidak memiliki role approver';
        }

        // ✅ GOOD: Sanitasi status untuk output
        $status = sanitize_output($tugas->status_surat);

        if ($tugas->status_surat !== 'pending') {
            return "Status surat adalah '{$status}', bukan pending";
        }

        // ✅ GOOD: Validasi ID
        $userId = validate_integer_id($user->id);
        $nextApprover = validate_integer_id($tugas->next_approver);

        if ($userId !== $nextApprover) {
            return "User bukan next_approver yang ditunjuk (next_approver: {$nextApprover})";
        }

        return 'Unknown reason';
    }

    /**
     * Log unauthorized access attempts for audit trail.
     * ✅ GOOD: Sanitasi semua data untuk log
     */
    private function logUnauthorizedAttempt(User $user, string $action, TugasHeader $tugas, string $reason): void
    {
        // ✅ GOOD: Validasi dan sanitasi data untuk log
        $userId = validate_integer_id($user->id);
        $roleId = validate_integer_id($user->peran_id);
        $tugasId = validate_integer_id($tugas->id);

        Log::warning('Unauthorized access attempt to TugasHeader', [
            'user_id' => $userId,
            'user_role' => $roleId,
            'action' => sanitize_log_message($action),
            'tugas_id' => $tugasId,
            'tugas_nomor' => sanitize_log_message($tugas->nomor ?? '(kosong)'),
            'tugas_status' => sanitize_log_message($tugas->status_surat),
            'reason' => sanitize_log_message($reason),
            'ip_address' => request()->ip(),
            'user_agent' => sanitize_log_message(request()->userAgent() ?? 'unknown'),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * ✅ FIXED: Before hook - runs before all policy checks
     */
    public function before(User $user, string $ability): ?bool
    {
        // ✅ FIXED: Jangan bypass policy untuk 'update' dan 'approve'
        // Biarkan method-specific policy yang handle
        if (in_array($ability, ['update', 'approve', 'reject', 'delete'], true)) {
            return null; // Continue ke method spesifik
        }

        // Admin dapat melakukan action lain (view, create, delete, dll)
        if ($user->isAdmin()) {
            return true;
        }

        return null; // Continue to specific policy methods
    }
}
