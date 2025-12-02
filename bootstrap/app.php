<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\UpdateLastActivity;
use App\Http\Middleware\CheckSessionRole; // ✅ TAMBAH: Import middleware baru

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Existing middleware
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastActivity::class
        ]);
        
        // ✅ TAMBAH: Register middleware alias untuk check session role
        $middleware->alias([
            'check.session.role' => \App\Http\Middleware\CheckSessionRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
