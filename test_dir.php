<?php
header('Content-Type: text/plain');

$dir = __DIR__ . '/inventory/inventaris-lab/app/controllers';
echo "Listing controllers in $dir:\n";
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo " - $file\n";
        }
    }
} else {
    echo "Directory does not exist!\n";
}
