<?php

namespace App\Routing;

use Illuminate\Routing\Redirector as BaseRedirector;

class DebugRedirector extends BaseRedirector
{
    protected function createRedirect($path, $status, $headers)
    {
        $log_file = base_path('../surat_debug_redirect.txt');
        $log_data = "============================= REDIRECTOR HOOK =========================\n";
        $log_data .= "TIME: " . date('Y-m-d H:i:s') . "\n";
        $log_data .= "REDIRECT TO PATH: " . $path . "\n";
        $log_data .= "STATUS: " . $status . "\n";
        
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $log_data .= "TRACE:\n";
        foreach ($trace as $i => $step) {
            $log_data .= "#$i " . ($step['file'] ?? 'unknown') . " line " . ($step['line'] ?? 'unknown') . " in " . ($step['function'] ?? 'unknown') . "\n";
        }
        file_put_contents($log_file, $log_data, FILE_APPEND);

        return parent::createRedirect($path, $status, $headers);
    }
}
