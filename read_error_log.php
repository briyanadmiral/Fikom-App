<?php
header('Content-Type: text/plain');

$log_paths = [
    __DIR__ . '/error_log',
    __DIR__ . '/inventory/inventaris-lab/public/error_log',
    __DIR__ . '/inventory/inventaris-lab/error_log'
];

foreach ($log_paths as $path) {
    if (file_exists($path)) {
        echo "=== Content of $path ===\n";
        // Print the last 30 lines
        $lines = file($path);
        $last_lines = array_slice($lines, -30);
        echo implode('', $last_lines);
        echo "\n\n";
    } else {
        echo "Log not found at: $path\n";
    }
}
