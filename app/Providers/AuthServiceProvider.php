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

        /** ===========================
         *  Gates untuk SURAT TUGAS (Existing)
         *  =========================== */
        // Boleh melihat halaman Approve List (khusus Dekan/Wakil Dekan)
        Gate::define('view-approve-list', fn($user) => in_array((int)$user->peran_id, [2, 3], true));

        // Approve Tugas spesifik (existing)
        Gate::define('approve-tugas', function ($user, TugasHeader $tugas) {
            return in_array((int)$user->peran_id, [2, 3], true)
                && (string)$tugas->status_surat === 'pending'
                && (int)$tugas->next_approver === (int)$user->id;
        });

        // Approver (2/3) boleh koreksi bila dia yang dituju & status draft/pending
        Gate::define('edit-surat', function ($user, $tugas) {
            return in_array((int)$user->peran_id, [2, 3], true)
                && in_array((string)$tugas->status_surat, ['draft', 'pending'], true)
                && (int)$tugas->next_approver === (int)$user->id;
        });

        // Kelola kop surat → Admin TU (peran 1)
        Gate::define('manage-kop-surat', fn($user) => (int)$user->peran_id === 1);

        /** ===========================
         *  Gates praktis untuk SURAT KEPUTUSAN (SK)
         *  (semua diarahkan ke Policy agar logic terkonsolidasi)
         *  =========================== */
        Gate::define('view-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->view($user, $k)
        );

        Gate::define('create-keputusan', fn($user) =>
            app(KeputusanHeaderPolicy::class)->create($user)
        );

        Gate::define('update-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->update($user, $k)
        );

        Gate::define('submit-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->submit($user, $k)
        );

        Gate::define('approve-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->approve($user, $k)
        );

        Gate::define('reject-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->reject($user, $k)
        );

        Gate::define('sign-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->sign($user, $k)
        );

        Gate::define('publish-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->publish($user, $k)
        );

        Gate::define('delete-keputusan', fn($user, KeputusanHeader $k) =>
            app(KeputusanHeaderPolicy::class)->delete($user, $k)
        );
    }
}
