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
    protected $policies = [
        TugasHeader::class     => TugasHeaderPolicy::class,
        KeputusanHeader::class => KeputusanHeaderPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /** ===========================
         * Gates untuk SURAT TUGAS (EXISTING)
         * =========================== */
        Gate::define('view-approve-list', fn($user) => in_array((int) $user->peran_id, [2, 3], true));
        Gate::define('approve-tugas', function ($user, TugasHeader $tugas) {
            return in_array((int)$user->peran_id, [2, 3], true)
                && (string)$tugas->status_surat === 'pending'
                && (int)$tugas->next_approver === (int)$user->id;
        });
        Gate::define('edit-surat', function ($user, $tugas) {
            return in_array((int)$user->peran_id, [2, 3], true)
                && in_array((string)$tugas->status_surat, ['draft', 'pending'], true)
                && (int)$tugas->next_approver === (int)$user->id;
        });

        // Kelola kop surat → Admin TU (peran 1)
        Gate::define('manage-kop-surat', fn($user) => (int)$user->peran_id === 1);

        /**
         * TIDAK ada gate untuk SURAT KEPUTUSAN di sini.
         * Semua akses SK ditangani oleh KeputusanHeaderPolicy.
         */
    }
}
