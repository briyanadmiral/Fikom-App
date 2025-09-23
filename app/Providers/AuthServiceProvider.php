<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\TugasHeader;
use App\Policies\TugasHeaderPolicy;
use App\Models\KeputusanHeader;
use App\Policies\KeputusanHeaderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TugasHeader::class     => TugasHeaderPolicy::class,
        KeputusanHeader::class => KeputusanHeaderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // === BARU: Boleh melihat halaman Approve List (khusus Dekan/Wakil Dekan) ===
        Gate::define('view-approve-list', function ($user) {
            return in_array((int) $user->peran_id, [2, 3], true);
        });

        // === BARU: Boleh APPROVE satu surat tertentu ===
        Gate::define('approve-tugas', function ($user, TugasHeader $tugas) {
            // Hanya Dekan/Wakil Dekan
            if (!in_array((int) $user->peran_id, [2, 3], true)) {
                return false;
            }

            // Tidak boleh meng-approve surat yang dibuat sendiri
            if ((int) $tugas->dibuat_oleh === (int) $user->id) {
                return false;
            }

            // Hanya jika status pending & user adalah next_approver
            return $tugas->status_surat === 'pending'
                && (int) $tugas->next_approver === (int) $user->id;
        });

        // (SUDAH ADA) Gate: approver (2/3) boleh koreksi bila dia yang dituju & status draft/pending
        Gate::define('edit-surat', function ($user, $tugas) {
            return in_array((int) $user->peran_id, [2, 3], true)
                && in_array((string) $tugas->status_surat, ['draft', 'pending'], true)
                && (int) $tugas->next_approver === (int) $user->id;
        });

        // (SUDAH ADA) Gate: kelola kop surat → Admin TU (id peran 1)
        Gate::define('manage-kop-surat', function ($user) {
            return in_array((int) $user->peran_id, [1], true);
        });
    }
}
