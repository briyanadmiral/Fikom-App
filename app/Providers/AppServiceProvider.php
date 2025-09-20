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
