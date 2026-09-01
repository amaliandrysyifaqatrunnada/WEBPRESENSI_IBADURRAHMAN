<?php

// Resolve serverless writable temp directory
$tmp = is_dir('/tmp') ? '/tmp' : sys_get_temp_dir();

// Prepare Laravel storage directories inside temp
$storageDirs = [
    $tmp . '/storage/framework/views',
    $tmp . '/storage/framework/cache/data',
    $tmp . '/storage/framework/sessions',
    $tmp . '/storage/logs',
    $tmp . '/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Redirect storage and compiled views to writable temp directory
$_ENV['LARAVEL_STORAGE_PATH'] = $tmp . '/storage';
$_SERVER['LARAVEL_STORAGE_PATH'] = $tmp . '/storage';
putenv('LARAVEL_STORAGE_PATH=' . $tmp . '/storage');

if (!isset($_ENV['VIEW_COMPILED_PATH'])) {
    $_ENV['VIEW_COMPILED_PATH'] = $tmp . '/storage/framework/views';
    $_SERVER['VIEW_COMPILED_PATH'] = $tmp . '/storage/framework/views';
    putenv('VIEW_COMPILED_PATH=' . $tmp . '/storage/framework/views');
}

// Forward request to standard Laravel public/index.php entrypoint
require __DIR__ . '/../public/index.php';
