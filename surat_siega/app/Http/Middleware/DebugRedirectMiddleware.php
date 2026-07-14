<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DebugRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $statusCode = $response->getStatusCode();
        $hasLocation = $response->headers->has('Location');

        if (($response instanceof RedirectResponse) || ($statusCode >= 300 && $statusCode < 400 && $hasLocation)) {
            $log_file = base_path('../surat_debug_redirect.txt');
            $log_data = "=========================================\n";
            $log_data .= "TIME: " . date('Y-m-d H:i:s') . "\n";
            $log_data .= "URL: " . $request->fullUrl() . "\n";
            $log_data .= "REDIRECT TO: " . ($hasLocation ? $response->headers->get('Location') : 'unknown') . "\n";
            $log_data .= "STATUS CODE: " . $statusCode . "\n";
            $log_data .= "RESPONSE CLASS: " . get_class($response) . "\n";
            
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30);
            $log_data .= "TRACE:\n";
            foreach ($trace as $i => $step) {
                $log_data .= "#$i " . ($step['file'] ?? 'unknown') . " line " . ($step['line'] ?? 'unknown') . " in " . ($step['function'] ?? 'unknown') . "\n";
            }
            file_put_contents($log_file, $log_data, FILE_APPEND);
        }

        return $response;
    }
}
