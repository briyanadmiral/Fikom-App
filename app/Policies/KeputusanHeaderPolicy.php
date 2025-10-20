<?php

namespace App\Policies;

use App\Models\KeputusanHeader;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeputusanHeaderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // ✅ IMPROVED: Use helper for validation
        $peranId = validate_integer_id($user->peran_id);

        return $peranId !== null && in_array($peranId, [1, 2, 3, 4], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate IDs
        $userPeranId = validate_integer_id($user->peran_id);
        $userId = validate_integer_id($user->id);
        $pembuatId = validate_integer_id($sk->dibuat_oleh);

        // Admin, Dekan, WD
        if ($userPeranId !== null && in_array($userPeranId, [1, 2, 3], true)) {
            return true;
        }

        // Pembuat
        if ($userId !== null && $pembuatId !== null && $userId === $pembuatId) {
            return true;
        }

        // Penerima (many-to-many)
        if ($userId !== null) {
            return $sk->penerima()->where('pengguna_id', $userId)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // ✅ IMPROVED: Validate peran_id
        $peranId = validate_integer_id($user->peran_id);

        return $peranId !== null && in_array($peranId, [1, 2, 3], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['draft', 'ditolak', 'pending', 'disetujui']);

        // Only draft/ditolak can be updated
        if (!in_array($status, ['draft', 'ditolak'], true)) {
            return false;
        }

        // ✅ IMPROVED: Validate IDs
        $userPeranId = validate_integer_id($user->peran_id);
        $userId = validate_integer_id($user->id);
        $pembuatId = validate_integer_id($sk->dibuat_oleh);

        // Admin atau pembuat sendiri
        if ($userPeranId === 1) {
            return true;
        }

        if ($userId !== null && $pembuatId !== null && $userId === $pembuatId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['draft']);

        // Hanya draft yang bisa dihapus
        if ($status !== 'draft') {
            return false;
        }

        // ✅ IMPROVED: Validate IDs
        $userPeranId = validate_integer_id($user->peran_id);
        $userId = validate_integer_id($user->id);
        $pembuatId = validate_integer_id($sk->dibuat_oleh);

        // Admin atau pembuat sendiri
        if ($userPeranId === 1) {
            return true;
        }

        if ($userId !== null && $pembuatId !== null && $userId === $pembuatId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate peran_id
        $peranId = validate_integer_id($user->peran_id);

        return $peranId === 1; // Only admin
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate peran_id
        $peranId = validate_integer_id($user->peran_id);

        return $peranId === 1; // Only admin
    }

    /**
     * Determine whether the user can submit for approval.
     */
    public function submit(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['draft']);

        if ($status !== 'draft') {
            return false;
        }

        // ✅ IMPROVED: Validate IDs
        $userPeranId = validate_integer_id($user->peran_id);
        $userId = validate_integer_id($user->id);
        $pembuatId = validate_integer_id($sk->dibuat_oleh);

        // Admin atau pembuat sendiri
        if ($userPeranId === 1) {
            return true;
        }

        if ($userId !== null && $pembuatId !== null && $userId === $pembuatId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['pending']);

        if ($status !== 'pending') {
            return false;
        }

        // ✅ IMPROVED: Validate IDs
        $userId = validate_integer_id($user->id);
        $penandatanganId = validate_integer_id($sk->penandatangan);

        if ($userId === null || $penandatanganId === null) {
            return false;
        }

        return $userId === $penandatanganId;
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['pending']);

        if ($status !== 'pending') {
            return false;
        }

        // ✅ IMPROVED: Validate IDs
        $userId = validate_integer_id($user->id);
        $penandatanganId = validate_integer_id($sk->penandatangan);

        if ($userId === null || $penandatanganId === null) {
            return false;
        }

        return $userId === $penandatanganId;
    }

    /**
     * Determine whether the user can reopen (tarik ke draft) the model.
     */
    public function reopen(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['pending', 'ditolak']);

        if (!in_array($status, ['pending', 'ditolak'], true)) {
            return false;
        }

        // ✅ IMPROVED: Validate IDs
        $userPeranId = validate_integer_id($user->peran_id);
        $userId = validate_integer_id($user->id);
        $pembuatId = validate_integer_id($sk->dibuat_oleh);

        // Admin atau pembuat sendiri
        if ($userPeranId === 1) {
            return true;
        }

        if ($userId !== null && $pembuatId !== null && $userId === $pembuatId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['disetujui']);

        if ($status !== 'disetujui') {
            return false;
        }

        // ✅ IMPROVED: Validate peran_id
        $peranId = validate_integer_id($user->peran_id);

        return $peranId !== null && in_array($peranId, [1, 2, 3], true);
    }

    /**
     * Determine whether the user can archive the model.
     */
    public function archive(User $user, KeputusanHeader $sk): bool
    {
        // ✅ IMPROVED: Validate status
        $status = validate_status($sk->status_surat, ['terbit']);

        if ($status !== 'terbit') {
            return false;
        }

        // ✅ IMPROVED: Validate peran_id
        $peranId = validate_integer_id($user->peran_id);

        return $peranId !== null && in_array($peranId, [1, 2, 3], true);
    }

    /**
     * ✅ ADDED: Determine whether the user can download the model.
     */
    public function download(User $user, KeputusanHeader $sk): bool
    {
        // ✅ Validate status - hanya bisa download jika sudah disetujui
        $status = validate_status($sk->status_surat, ['disetujui', 'terbit', 'arsip']);

        if (!in_array($status, ['disetujui', 'terbit', 'arsip'], true)) {
            return false;
        }

        // ✅ Reuse view logic
        return $this->view($user, $sk);
    }

    /**
     * ✅ ADDED: Before hook - runs before all policy checks
     */
    public function before(User $user, string $ability): ?bool
    {
        // ✅ Super admin bypass (if implemented)
        // Uncomment if you have super admin role
        // if ($user->peran_id === 0) {
        //     return true;
        // }

        return null; // Continue to specific policy methods
    }
}
