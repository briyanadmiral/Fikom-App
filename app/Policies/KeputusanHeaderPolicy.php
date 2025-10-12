<?php

namespace App\Policies;

use App\Models\KeputusanHeader;
use App\Models\User; // atau Pengguna, sesuai model yang dipakai
use Illuminate\Auth\Access\HandlesAuthorization;

class KeputusanHeaderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array((int) $user->peran_id, [1, 2, 3, 4], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KeputusanHeader $sk): bool
    {
        // Admin, Dekan, WD, atau penerima yang tercantum
        if (in_array((int) $user->peran_id, [1, 2, 3], true)) {
            return true;
        }

        // Pembuat
        if ((int) $sk->dibuat_oleh === (int) $user->id) {
            return true;
        }

        // Penerima (many-to-many)
        return $sk->penerima()->where('pengguna_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array((int) $user->peran_id, [1, 2, 3], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KeputusanHeader $sk): bool
    {
        // Admin atau pembuat sendiri, dan status masih draft/ditolak
        if (in_array($sk->status_surat, ['draft', 'ditolak'], true)) {
            return (int) $user->peran_id === 1 || (int) $user->id === (int) $sk->dibuat_oleh;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KeputusanHeader $sk): bool
    {
        // Hanya draft yang bisa dihapus
        if ($sk->status_surat !== 'draft') {
            return false;
        }

        // Admin atau pembuat sendiri
        return (int) $user->peran_id === 1 || (int) $user->id === (int) $sk->dibuat_oleh;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, KeputusanHeader $sk): bool
    {
        return (int) $user->peran_id === 1;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KeputusanHeader $sk): bool
    {
        return (int) $user->peran_id === 1;
    }

    /**
     * Determine whether the user can submit for approval.
     */
    public function submit(User $user, KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'draft'
            && ((int) $user->peran_id === 1 || (int) $user->id === (int) $sk->dibuat_oleh);
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'pending'
            && !empty($sk->penandatangan)
            && (int) $sk->penandatangan === (int) $user->id;
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'pending'
            && !empty($sk->penandatangan)
            && (int) $sk->penandatangan === (int) $user->id;
    }

    /**
     * Determine whether the user can reopen (tarik ke draft) the model.
     */
    public function reopen(User $user, KeputusanHeader $sk): bool
    {
        return in_array($sk->status_surat, ['pending', 'ditolak'], true)
            && ((int) $user->peran_id === 1 || (int) $user->id === (int) $sk->dibuat_oleh);
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'disetujui'
            && in_array((int) $user->peran_id, [1, 2, 3], true);
    }

    /**
     * Determine whether the user can archive the model.
     */
    public function archive(User $user, KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'terbit'
            && in_array((int) $user->peran_id, [1, 2, 3], true);
    }
}
