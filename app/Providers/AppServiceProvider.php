<?php

namespace App\Providers;

use App\Models\KeputusanHeader;
use App\Models\TugasHeader;
use App\Observers\KeputusanHeaderObserver;
use App\Observers\TugasHeaderObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider; // ✅ ADDED

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ✅ GOOD: Bind sebagai singleton
        $this->app->singleton(\App\Services\BaseNotificationService::class);
        $this->app->singleton(\App\Services\SuratTugasNotificationService::class);
        $this->app->singleton(\App\Services\SuratKeputusanNotificationService::class);
        $this->app->singleton(\App\Services\NomorSuratService::class);
        $this->app->singleton(\App\Services\AuditService::class); // ✅ PHASE 1: AuditService

        // ✅ ADDED: Register helpers as singletons if they're classes
        // (Skip if helpers are functions)

        // ✅ ADDED: Performance optimization for production
        if ($this->app->environment('production')) {
            $this->app->singleton('url', function ($app) {
                return new \Illuminate\Routing\UrlGenerator($app['router']->getRoutes(), $app['request'], $app['config']['app.asset_url']);
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ ADDED: Database connection safety
        try {
            // Set default string length for older MySQL versions
            Schema::defaultStringLength(191);
        } catch (\Exception $e) {
            // Database might not be available during certain operations
            report($e);
        }

        // ✅ FIX: Set Carbon locale and timezone for correct diffForHumans()
        \Carbon\Carbon::setLocale(config('app.locale', 'id'));
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

        // ✅ GOOD: Bootstrap pagination
        if (method_exists(Paginator::class, 'useBootstrapFive')) {
            Paginator::useBootstrapFive();
        } else {
            Paginator::useBootstrap();
        }

        // ✅ GOOD: Observers registration
        TugasHeader::observe(TugasHeaderObserver::class);
        KeputusanHeader::observe(KeputusanHeaderObserver::class); // ✅ PHASE 1: SK Observer

        // ✅ GOOD: Force HTTPS di production
        if (app()->environment('production')) {
            URL::forceScheme('https');

            // ✅ ADDED: Force root URL if needed
            // URL::forceRootUrl(config('app.url'));
        }

        // ✅ GOOD: Eloquent strict mode untuk development
        if (! app()->isProduction()) {
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
            Model::preventAccessingMissingAttributes();

            // ✅ ADDED: Additional development safety
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                $class = get_class($model);
                report(new \Exception("Attempted to lazy load [{$relation}] on model [{$class}]"));
            });
        }

        // ✅ ADDED: Custom validation rules registration
        $this->registerCustomValidationRules();

        // ✅ ADDED: View composers registration
        $this->registerViewComposers();
    }

    /**
     * ✅ ADDED: Register custom validation rules
     */
    private function registerCustomValidationRules(): void
    {
        // Example: Custom validation using helpers
        \Validator::extend('sanitized_input', function ($attribute, $value, $parameters, $validator) {
            if (! function_exists('sanitize_input')) {
                return true; // Skip if helper not available
            }

            $maxLength = isset($parameters[0]) ? (int) $parameters[0] : 255;
            $sanitized = sanitize_input($value, $maxLength);

            return $sanitized !== null && $sanitized === $value;
        });

        \Validator::extend('valid_integer_id', function ($attribute, $value, $parameters, $validator) {
            if (! function_exists('validate_integer_id')) {
                return is_numeric($value) && (int) $value > 0;
            }

            return validate_integer_id($value) !== null;
        });
    }

    /**
     * ✅ ADDED: Register view composers
     */
    private function registerViewComposers(): void
    {
        // Share common data with all views
        view()->composer('*', function ($view) {
            $view->with([
                'app_name' => config('app.name', 'Laravel'),
                'app_version' => config('app.version', '1.0.0'),
            ]);
        });

        // Share user data with authenticated views
        view()->composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $view->with([
                    'current_user' => auth()->user(),
                    // ✅ FIXED: Get results from relationship
                    'unread_notifications' => auth()->user()->unreadNotifications()->get(),
                ]);
            }
        });
    }
}
