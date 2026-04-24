<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses hanya untuk Admin TU.
 *
 * Digunakan pada route-route master data, audit logs, reports, dll
 * yang seharusnya hanya bisa diakses oleh administrator.
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya Admin TU yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
