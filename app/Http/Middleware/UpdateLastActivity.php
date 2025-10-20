<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateLastActivity
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            try {
                // ✅ PRIORITAS 1: Update session payload
                session(['last_activity' => now()]);

                // ✅ OPSIONAL: Update tabel pengguna (jika perlu tracking di DB)
                // Gunakan throttling agar tidak terlalu sering update DB
                $lastUpdate = session('last_db_update');

                // Update DB maksimal setiap 5 menit
                if (!$lastUpdate || now()->diffInMinutes($lastUpdate) >= 5) {
                    DB::table('pengguna')
                        ->where('id', Auth::id())
                        ->update(['last_activity' => now()]);

                    session(['last_db_update' => now()]);
                }
            } catch (\Throwable $e) {
                // ✅ FIXED: Sanitize log message
                Log::warning('UpdateLastActivity: gagal memperbarui last_activity', [
                    'error' => sanitize_log_message($e->getMessage()),
                    'user_id' => Auth::id(),
                ]);
                // Jangan hentikan request, lanjutkan saja
            }
        }

        return $next($request);
    }
}
