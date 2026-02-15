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
     *
     * Dipakai untuk: index, terbitList, approveList (authorize di controller).
     */
    public function viewAny(User $user): bool
    {
        // Use helper for validation
        $peranId = validate_integer_id($user->peran_id);

        // Semua peran yang valid (>=1) boleh akses listing SK,
        // nanti difilter lagi di controller / query.
        return $peranId !== null && $peranId >= 1;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KeputusanHeader $sk): bool
    {
        // Validate IDs
        $userPeranId = validate_integer_id($user->peran_id);
        $userId = validate_integer_id($user->id);
        $pembuatId = validate_integer_id($sk->dibuat_oleh);

        // Admin, Dekan, WD boleh lihat semua
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
        // Hanya Admin TU yang boleh buat SK
        $peranId = validate_integer_id($user->peran_id);

        return $peranId !== null && $peranId === 1;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KeputusanHeader $sk): bool
    {
        $userPeranId = (int) $user->peran_id;
        $userId = (int) $user->id;
        $pembuatId = (int) $sk->dibuat_oleh;

        // ALLOW: Route Duplicate (doesn't modify the original SK)
        if (request()->routeIs('surat_keputusan.duplicate')) {
            return true;
        }

        // 1) Normalisasi status (null => draft)
        $rawStatus = $sk->status_surat ?? 'draft';
        $currentStatus = trim(strtolower($rawStatus));
        $allowedStatuses = ['draft', 'ditolak'];

        // STRICT: Status harus Draft/Ditolak.
        if (! in_array($currentStatus, $allowedStatuses, true)) {
            return false;
        }

        // 2) Admin TU boleh edit draft punya siapa saja
        if ($userPeranId === 1) {
            return true;
        }

        // 3) Pembuat sendiri boleh update draft punya sendiri
        if ($userId > 0 && $pembuatId > 0 && $userId === $pembuatId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KeputusanHeader $sk): bool
    {
        // Validate status
        $status = validate_status($sk->status_surat, ['draft']);

        // Hanya draft yang bisa dihapus
        if ($status !== 'draft') {
            return false;
        }

        // Validate IDs
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
        // Validate peran_id
        $peranId = validate_integer_id($user->peran_id);

        return $peranId === 1; // Only admin
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KeputusanHeader $sk): bool
    {
        // Validate peran_id
        $peranId = validate_integer_id($user->peran_id);

        return $peranId === 1; // Only admin
    }

    /**
     * Determine whether the user can submit for approval.
     */
    public function submit(User $user, KeputusanHeader $sk): bool
    {
        // SK draft atau ditolak bisa diajukan
        $allowedStatuses = ['draft', 'ditolak'];

        if (empty($sk->status_surat)) {
            return false;
        }

        $currentStatus = trim(strtolower($sk->status_surat));
        if (! in_array($currentStatus, $allowedStatuses, true)) {
            return false;
        }

        // Validasi user
        $userPeranId = (int) $user->peran_id;
        $userId = (int) $user->id;
        $pembuatId = (int) $sk->dibuat_oleh;

        // Admin atau pembuat sendiri
        if ($userPeranId === 1) {
            return true;
        }

        if ($userId > 0 && $pembuatId > 0 && $userId === $pembuatId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the model.
     */
    public function approve(User $user, KeputusanHeader $sk): bool
    {
        // Validate status
        $status = validate_status($sk->status_surat, ['pending']);
        if ($status !== 'pending') {
            return false;
        }

        // Validate user peran_id
        $userPeranId = validate_integer_id($user->peran_id);

        // Dekan (2) atau WD (3) bisa approve pending
        if ($userPeranId !== null && in_array($userPeranId, [2, 3], true)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reject the model.
     */
    public function reject(User $user, KeputusanHeader $sk): bool
    {
        // Validate status
        $status = validate_status($sk->status_surat, ['pending']);
        if ($status !== 'pending') {
            return false;
        }

        // Validate user peran_id
        $userPeranId = validate_integer_id($user->peran_id);

        // Dekan (2) atau WD (3) bisa reject
        if ($userPeranId !== null && in_array($userPeranId, [2, 3], true)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reopen (tarik ke draft) the model.
     */
    public function reopen(User $user, KeputusanHeader $sk): bool
    {
        // Validate status
        $status = validate_status($sk->status_surat, ['pending', 'ditolak']);

        if (! in_array($status, ['pending', 'ditolak'], true)) {
            return false;
        }

        // Validate IDs
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

    public function publish(User $user, KeputusanHeader $keputusan): bool
    {
        // Validasi status SK
        $status = validate_status($keputusan->status_surat, ['disetujui']);
        if ($status !== 'disetujui') {
            return false;
        }

        // Validasi peran_id user
        $userPeranId = validate_integer_id($user->peran_id);
        $userId = validate_integer_id($user->id);
        $penandatanganId = validate_integer_id($keputusan->penandatangan);

        // Admin/TU (peran_id=1) atau Penandatangan bisa menerbitkan
        if ($userPeranId === 1) {
            return true;
        }

        if ($userId !== null && $penandatanganId !== null && $userId === $penandatanganId) {
            return true;
        }

        return false;
    }

    /**
     * Determine if user can unpublish (batal terbitkan) SK
     */
    public function unpublish(User $user, KeputusanHeader $keputusan): bool
    {
        $peranId = validate_integer_id($user->peran_id);

        return $keputusan->status_surat === 'terbit' && $peranId === 1;
    }

    /**
     * Determine if user can archive SK
     */
    public function archive(User $user, KeputusanHeader $keputusan): bool
    {
        $peranId = validate_integer_id($user->peran_id);

        return $keputusan->status_surat === 'terbit' && $peranId === 1;
    }

    /**
     * Ability: boleh lihat halaman Arsip SK (list arsip).
     * Dipakai di route: /surat_keputusan/arsip (viewArchive).
     */
    public function viewArchive(User $user): bool
    {
        $peranId = validate_integer_id($user->peran_id);

        // Hanya Admin TU (peran_id 1) yang boleh akses halaman Arsip.
        return $peranId === 1;
    }

    /**
     * Determine whether the user can download the model.
     */
    public function download(User $user, KeputusanHeader $sk): bool
    {
        // Validate status - hanya bisa download jika sudah disetujui/terbit/arsip
        $status = validate_status($sk->status_surat, ['disetujui', 'terbit', 'arsip']);

        if (! in_array($status, ['disetujui', 'terbit', 'arsip'], true)) {
            return false;
        }

        // Reuse view logic
        return $this->view($user, $sk);
    }

    /**
     * Before hook - runs before all policy checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Super admin bypass (if implemented)
        // if ($user->peran_id === 0) {
        //     return true;
        // }

        return null; // Continue to specific policy methods
    }
}
