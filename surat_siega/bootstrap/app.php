<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ✅ TAMBAH: Import middleware baru

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastActivity::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\RequestId::class,
        ]);

        $middleware->alias([
            'check.session.role' => \App\Http\Middleware\CheckSessionRole::class,
        ]);

        // Redirect unauthenticated guests to the main bridge login
        $middleware->redirectTo(
            guests: function($request) {
                throw new \Exception("GUEST REDIRECT TRIGGERED FOR: " . $request->fullUrl());
                $base = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
                $pos = strpos($base, '/surat_siega/public');
                $main_url = ($pos !== false) ? substr($base, 0, $pos) : $base;
                return $main_url . '/index.php';
            }
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {
            $log_file = storage_path('logs/surat_debug_laravel.log');
            $log_data = "=========================================\n";
            $log_data .= "TIME: " . date('Y-m-d H:i:s') . "\n";
            $log_data .= "URL: " . request()->fullUrl() . "\n";
            $log_data .= "EXCEPTION: " . $e->getMessage() . "\n";
            $log_data .= "FILE: " . $e->getFile() . " (Line " . $e->getLine() . ")\n";
            $log_data .= "TRACE: " . $e->getTraceAsString() . "\n";
            file_put_contents($log_file, $log_data, FILE_APPEND);
        });
    })
    ->create();
