<?php

namespace App\Policies;

use App\Models\KeputusanHeader;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KeputusanHeaderPolicy
{
    use HandlesAuthorization;

    /** Lihat daftar */
    public function viewAny(User $user): bool
    {
        // Kalau ingin tetap khusus Admin TU saja, ganti jadi: return (int)$user->peran_id === 1;
        return in_array((int)$user->peran_id, [1,2,3], true);
    }

    /** Lihat detail: admin, pembuat, penandatangan, atau penerima */
    public function view(User $user, KeputusanHeader $sk): bool
    {
        if ((int)$user->peran_id === 1) return true;
        if ((int)$user->id === (int)$sk->dibuat_oleh) return true;
        if ((int)$user->id === (int)$sk->penandatangan) return true;
        return $sk->penerima()->where('pengguna_id', $user->id)->exists();
    }

    /** Buat: hanya Admin TU */
    public function create(User $user): bool
    {
        return (int)$user->peran_id === 1;
    }

    /**
     * Update (revisi):
     * - Admin TU (peran 1) yang MEMBUAT boleh edit saat draft/pending/ditolak
     * - Penandatangan (peran 2/3) yang ditunjuk boleh koreksi saat pending
     */
    public function update(User $user, KeputusanHeader $sk): bool
{
    $isAdminTU   = (int) $user->peran_id === 1;
    $isApprover  = in_array((int) $user->peran_id, [2,3], true) && (int)$user->id === (int)$sk->penandatangan;

    // Admin TU boleh revisi DRAFT & PENDING, terbatas pada SK yang dia buat
    $adminCan    = $isAdminTU
                && (int)$user->id === (int)$sk->dibuat_oleh
                && in_array($sk->status_surat, ['draft','pending'], true);

    // Penandatangan (2/3) boleh koreksi saat pending
    $approverCan = $isApprover && $sk->status_surat === 'pending';

    return $adminCan || $approverCan;
}

    /** Hapus: opsional — admin & hanya draft miliknya */
    public function delete(User $user, KeputusanHeader $sk): bool
    {
        return (int)$user->peran_id === 1
            && $sk->status_surat === 'draft'
            && (int)$user->id === (int)$sk->dibuat_oleh;
    }

    /** Submit dari draft ke pending: pembuat (admin TU) */
    public function submit(User $user, KeputusanHeader $sk): bool
    {
        return (int)$user->peran_id === 1
            && $sk->status_surat === 'draft'
            && (int)$user->id === (int)$sk->dibuat_oleh;
    }

    /** Approve / Reject: hanya penandatangan (peran 2/3) saat pending */
    public function approve(User $user, KeputusanHeader $sk): bool
    {
        return in_array((int)$user->peran_id, [2,3], true)
            && (int)$user->id === (int)$sk->penandatangan
            && $sk->status_surat === 'pending';
    }
    public function reject(User $user, KeputusanHeader $sk): bool
    {
        return $this->approve($user, $sk);
    }

    /**
     * Reopen: tarik kembali ke draft untuk direvisi.
     * Di sini saya izinkan Admin TU yang MEMBUAT.
     * Jika ingin semua Admin TU bisa, hapus cek $isCreator.
     */
    public function reopen(User $user, KeputusanHeader $sk): bool
{
    // Admin TU (pembuat) boleh menarik ke Draft untuk direvisi dari status selain draft
    return (int)$user->peran_id === 1
        && (int)$user->id === (int)$sk->dibuat_oleh
        && in_array($sk->status_surat, ['pending','ditolak','disetujui','terbit'], true);
}

    /** Publish: setelah disetujui oleh peran 2/3; admin juga boleh */
    public function publish(User $user, KeputusanHeader $sk): bool
    {
        return $sk->status_surat === 'disetujui'
            && in_array((int)$user->peran_id, [1,2,3], true);
    }

    /** Arsip: Admin TU / Dekan / WD */
    public function archive(User $user, KeputusanHeader $sk): bool
    {
        return in_array((int)$user->peran_id, [1,2,3], true);
    }
}
