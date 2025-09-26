<?php

namespace App\Policies;

use App\Enums\SuratStatus; // jika belum ada enum, policy tetap aman dengan fallback string
use App\Models\KeputusanHeader;
use App\Models\User;

class KeputusanHeaderPolicy
{
    /**
     * Admin TU (peran_id=1) boleh melihat daftar semua SK.
     * Untuk halaman daftar "Approve List" milik Dekan/WD, gunakan Gate 'view-approve-list' di AuthServiceProvider.
     */
    public function viewAny(User $user): bool
    {
        return (int)$user->peran_id === 1;
    }

    /**
     * Siapa yang boleh melihat 1 SK?
     * - Admin TU
     * - Pembuat SK
     * - Penerima SK
     * - Penandatangan SK
     * - Dekan/WD yang berhak approve (cross-approval) saat status pending
     */
    public function view(User $user, KeputusanHeader $k = null): bool
    {
        if (is_null($k)) return true;

        if ((int)$user->peran_id === 1) return true; // Admin TU
        if ((int)$user->id === (int)$k->dibuat_oleh) return true; // pembuat
        if ((int)$user->id === (int)($k->penandatangan ?? 0)) return true; // penandatangan

        // penerima SK
        if ($k->penerima()->where('pengguna_id', $user->id)->exists()) return true;

        // approver (Dekan/WD) ketika pending
        if ($this->bisaDiApproveOleh($user, $k)) return true;

        return false;
    }

    /**
     * Hanya Admin TU yang boleh membuat SK.
     */
    public function create(User $user): bool
    {
        return (int)$user->peran_id === 1;
    }

    /**
     * Hanya Admin TU yang membuat SK & status masih draft yang boleh edit.
     */
    public function update(User $user, KeputusanHeader $k): bool
    {
        return (int)$user->peran_id === 1
            && (int)$user->id === (int)$k->dibuat_oleh
            && $this->statusIs($k, 'draft');
    }

    /**
     * Submit draft → pending (Admin TU pembuat).
     */
    public function submit(User $user, KeputusanHeader $k): bool
    {
        return (int)$user->peran_id === 1
            && (int)$user->id === (int)$k->dibuat_oleh
            && $this->statusIs($k, 'draft');
    }

    /**
     * Cross-approval Dekan (2) ↔ Wakil Dekan (3). Tidak boleh self-approve. Hanya ketika pending.
     */
    public function approve(User $user, KeputusanHeader $k): bool
    {
        return $this->bisaDiApproveOleh($user, $k);
    }

    /**
     * Reject mengikuti aturan approve.
     */
    public function reject(User $user, KeputusanHeader $k): bool
    {
        return $this->approve($user, $k);
    }

    /**
     * Tanda tangan: hanya user yang ditetapkan sebagai penandatangan & status sudah disetujui.
     */
    public function sign(User $user, KeputusanHeader $k): bool
    {
        return (int)$user->id === (int)($k->penandatangan ?? 0)
            && $this->statusIs($k, 'disetujui');
    }

    /**
     * Publish: Admin TU, status disetujui, dan sudah ada signed_pdf_path.
     */
    public function publish(User $user, KeputusanHeader $k): bool
    {
        return (int)$user->peran_id === 1
            && $this->statusIs($k, 'disetujui')
            && !empty($k->signed_pdf_path);
    }

    /**
     * Hapus: Admin TU pembuat & masih draft (biasanya soft delete).
     */
    public function delete(User $user, KeputusanHeader $k): bool
    {
        return (int)$user->peran_id === 1
            && (int)$user->id === (int)$k->dibuat_oleh
            && $this->statusIs($k, 'draft');
    }

    public function restore(User $user, KeputusanHeader $k): bool
    {
        return false;
    }

    public function forceDelete(User $user, KeputusanHeader $k): bool
    {
        return false;
    }

    /* ========================
     * Helpers
     * ======================== */

    private function statusIs(KeputusanHeader $k, string $val): bool
    {
        // kompatibel enum atau string
        if ($k->status_surat instanceof SuratStatus) {
            return $k->status_surat->value === $val;
        }
        return (string)$k->status_surat === $val;
    }

    /**
     * Aturan cross-approval Dekan (2) ↔ Wakil Dekan (3)
     */
    private function bisaDiApproveOleh(User $user, KeputusanHeader $k): bool
    {
        if (!$this->statusIs($k, 'pending')) return false;
        if ((int)$user->id === (int)$k->dibuat_oleh) return false; // no self-approve

        $pembuatRole = (int)optional($k->pembuat)->peran_id;
        $isDekanApproveWD = ((int)$user->peran_id === 2) && ($pembuatRole === 3);
        $isWDApproveDekan = ((int)$user->peran_id === 3) && ($pembuatRole === 2);

        return $isDekanApproveWD || $isWDApproveDekan;
    }
}
