<?php

namespace App\Providers;

use App\Models\KeputusanHeader;
use App\Models\TugasHeader;
use App\Models\User;
use App\Policies\KeputusanHeaderPolicy;
use App\Policies\TugasHeaderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TugasHeader::class => TugasHeaderPolicy::class,
        KeputusanHeader::class => KeputusanHeaderPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /*
        |--------------------------------------------------------------------------
        | Gates untuk SURAT TUGAS
        |--------------------------------------------------------------------------
        */

        // Lihat antrian approval (khusus approver aktif: Dekan / Wakil Dekan)
        Gate::define('view-approve-list', function (?User $user): bool {
            if (! $user) {
                return false;
            }

            return $user->isActive() && $user->isApprover();
        });

        // Menyetujui surat tugas dengan validasi ID
        Gate::define('approve-tugas', function (?User $user, TugasHeader $tugas): bool {
            if (! $user) {
                return false;
            }

            // Validasi ID dan status
            $userId = validate_integer_id($user->id);
            $nextApproverId = validate_integer_id($tugas->next_approver);
            $status = validate_status($tugas->status_surat, ['pending', 'draft', 'disetujui']);

            if ($userId === null || $nextApproverId === null || $status !== 'pending') {
                return false;
            }

            return $user->isActive() && $user->isApprover() && $userId === $nextApproverId;
        });

        // Mengedit surat tugas dengan validasi
        Gate::define('edit-surat', function (?User $user, TugasHeader $tugas): bool {
            if (! $user) {
                return false;
            }

            // Validasi nomor_status
            $nomorStatus = validate_status($tugas->nomor_status, ['locked', 'active']);
            if ($nomorStatus === 'locked') {
                return false;
            }

            // Validasi status surat
            $status = validate_status($tugas->status_surat, ['draft', 'pending', 'disetujui', 'ditolak']);
            if (! in_array($status, ['draft', 'pending'], true)) {
                return false;
            }

            // Validasi ID
            $userId = validate_integer_id($user->id);
            $dibuatOleh = validate_integer_id($tugas->dibuat_oleh);
            $nextApproverId = validate_integer_id($tugas->next_approver);

            if ($userId === null) {
                return false;
            }

            if ($status === 'draft') {
                return $user->isActive() && $userId === $dibuatOleh;
            }

            // status === 'pending'
            return $user->isActive() && $user->isApprover() && $userId === $nextApproverId;
        });

        /*
        |--------------------------------------------------------------------------
        | Gates untuk SURAT KEPUTUSAN
        |--------------------------------------------------------------------------
        */

        // Gate untuk approve keputusan
        Gate::define('approve-keputusan', function (?User $user, KeputusanHeader $sk): bool {
            if (! $user) {
                return false;
            }

            $userId = validate_integer_id($user->id);
            $penandatanganId = validate_integer_id($sk->penandatangan);
            $status = validate_status($sk->status_surat, ['pending']);

            if ($userId === null || $penandatanganId === null || $status !== 'pending') {
                return false;
            }

            return $user->isActive() && $user->canApproveSurat() && $userId === $penandatanganId;
        });

        // Gate untuk submit keputusan
        Gate::define('submit-keputusan', function (?User $user, KeputusanHeader $sk): bool {
            if (! $user) {
                return false;
            }

            $userId = validate_integer_id($user->id);
            $dibuatOleh = validate_integer_id($sk->dibuat_oleh);
            $status = validate_status($sk->status_surat, ['draft']);

            if ($userId === null || $dibuatOleh === null || $status !== 'draft') {
                return false;
            }

            return $user->isActive() && $userId === $dibuatOleh;
        });

        /*
        |--------------------------------------------------------------------------
        | Gates untuk MANAJEMEN SISTEM
        |--------------------------------------------------------------------------
        */

        // Pengaturan Kop Surat (Hanya Admin TU)
        Gate::define('manage-kop-surat', function (?User $user): bool {
            if (! $user) {
                return false;
            }

            return $user->isActive() && $user->isAdmin();
        });

        // Manajemen user
        Gate::define('manage-users', function (?User $user): bool {
            if (! $user) {
                return false;
            }

            return $user->isActive() && $user->isAdmin();
        });

        // View reports/analytics
        Gate::define('view-reports', function (?User $user): bool {
            if (! $user) {
                return false;
            }
            $peranId = validate_integer_id($user->peran_id);

            return $user->isActive() && $peranId !== null && in_array($peranId, [1, 2, 3], true);
        });

        // Manage notifications
        Gate::define('manage-notifications', function (?User $user): bool {
            if (! $user) {
                return false;
            }

            return $user->isActive() && $user->isAdmin();
        });

        /*
        |--------------------------------------------------------------------------
        | Gates untuk FILE OPERATIONS
        |--------------------------------------------------------------------------
        */

        // Upload signature
        Gate::define('upload-signature', function (?User $user): bool {
            if (! $user) {
                return false;
            }

            return $user->isActive();
        });

        // Download signed documents
        Gate::define('download-signed-document', function (?User $user, $document): bool {
            if (! $user || ! $user->isActive()) {
                return false;
            }

            // Check if document is signed/approved
            if (method_exists($document, 'getAttribute')) {
                $status = validate_status($document->getAttribute('status_surat'), ['disetujui', 'terbit']);

                return in_array($status, ['disetujui', 'terbit'], true);
            }

            return false;
        });

        /*
        |--------------------------------------------------------------------------
        | Super Admin Gates (Optional)
        |--------------------------------------------------------------------------
        */

        // Super admin gate (if implemented)
        Gate::before(function (?User $user, string $ability) {
            // Uncomment if you have super admin role (peran_id = 0)
            // if ($user && validate_integer_id($user->peran_id) === 0) {
            //     return true;
            // }

            return null; // Continue to specific gates
        });

        /*
        |--------------------------------------------------------------------------
        | Development Gates (Non-Production Only)
        |--------------------------------------------------------------------------
        */

        if (! app()->environment('production')) {
            // Debug gates for development
            Gate::define('view-debug-info', function (?User $user): bool {
                return $user && $user->isAdmin();
            });

            Gate::define('clear-cache', function (?User $user): bool {
                return $user && $user->isAdmin();
            });
        }
    }
}
