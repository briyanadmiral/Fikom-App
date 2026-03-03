<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            try {
                $user = Auth::user();
                // Update only if last update was > 2 minutes ago to reduce DB writes
                $shouldUpdate = ! $user->last_activity
                    || now()->diffInMinutes($user->last_activity) > 2;

                if ($shouldUpdate) {
                    // Use direct DB update to bypass model events and ensuring updates
                    \Illuminate\Support\Facades\DB::table('pengguna')
                        ->where('id', $user->id)
                        ->update(['last_activity' => now()]);
                }
            } catch (\Exception $e) {
                // Don't break the request if activity update fails
                report($e);
            }
        }

        return $next($request);
    }
}
