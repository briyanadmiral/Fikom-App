<?php
header('Content-Type: text/plain');

$log_paths = [
    __DIR__ . '/error_log',
    __DIR__ . '/../error_log',
    '/home/fike8938/public_html/error_log',
    '/home/fike8938/error_log',
    __DIR__ . '/surat_siega/public/error_log',
    __DIR__ . '/surat_siega/error_log'
];

foreach ($log_paths as $path) {
    $real = realpath($path);
    if ($real && file_exists($real)) {
        echo "=== Content of $real ===\n";
        // Print the last 40 lines
        $lines = file($real);
        $last_lines = array_slice($lines, -40);
        echo implode('', $last_lines);
        echo "\n\n";
    } else {
        echo "Log not found at: $path\n";
    }
}
