<?php
header('Content-Type: text/plain');

$dir = __DIR__ . '/inventory/inventaris-lab/app/controllers';

echo "Renaming files on server:\n";

$files_to_rename = [
    'JenisBarangController.php' => 'JenisbarangController.php',
    'StockOpnameController.php' => 'StockopnameController.php'
];

foreach ($files_to_rename as $old => $new) {
    $old_path = "$dir/$old";
    $new_path = "$dir/$new";
    
    if (file_exists($old_path)) {
        if (rename($old_path, $new_path)) {
            echo "Successfully renamed $old to $new\n";
        } else {
            echo "Failed to rename $old to $new\n";
        }
    } else {
        echo "File $old does not exist (already renamed?)\n";
    }
}

echo "\nNew listing:\n";
$files = scandir($dir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo " - $file\n";
    }
}
