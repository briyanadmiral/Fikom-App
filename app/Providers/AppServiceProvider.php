<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // Penting untuk pagination
use App\Models\TugasHeader;
use App\Observers\TugasHeaderObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
         // Register notification services
    $this->app->singleton(\App\Services\BaseNotificationService::class);
    $this->app->singleton(\App\Services\SuratTugasNotificationService::class);
    $this->app->singleton(\App\Services\SuratKeputusanNotificationService::class);
    $this->app->singleton(\App\Services\NomorSuratService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Memberitahu Laravel untuk menggunakan template Bootstrap untuk semua pagination
        Paginator::useBootstrap();
        TugasHeader::observe(TugasHeaderObserver::class);
    }
}
