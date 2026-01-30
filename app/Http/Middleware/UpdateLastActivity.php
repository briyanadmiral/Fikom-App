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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Update only if last update was > 2 minutes ago to reduce DB writes
            if (!$user->last_activity || $user->last_activity->diffInMinutes(now()) > 2) {
                 $user->update(['last_activity' => now()]);
            }
        }

        return $next($request);
    }
}
