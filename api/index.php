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

$_ENV['VIEW_COMPILED_PATH'] = $tmp . '/storage/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = $tmp . '/storage/framework/views';
putenv('VIEW_COMPILED_PATH=' . $tmp . '/storage/framework/views');

// Redirect bootstrap caches to writable temp directory
$_ENV['APP_SERVICES_CACHE'] = $tmp . '/bootstrap/cache/services.php';
$_SERVER['APP_SERVICES_CACHE'] = $tmp . '/bootstrap/cache/services.php';
putenv('APP_SERVICES_CACHE=' . $tmp . '/bootstrap/cache/services.php');

$_ENV['APP_PACKAGES_CACHE'] = $tmp . '/bootstrap/cache/packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = $tmp . '/bootstrap/cache/packages.php';
putenv('APP_PACKAGES_CACHE=' . $tmp . '/bootstrap/cache/packages.php');

$_ENV['APP_CONFIG_CACHE'] = $tmp . '/bootstrap/cache/config.php';
$_SERVER['APP_CONFIG_CACHE'] = $tmp . '/bootstrap/cache/config.php';
putenv('APP_CONFIG_CACHE=' . $tmp . '/bootstrap/cache/config.php');

$_ENV['APP_ROUTES_CACHE'] = $tmp . '/bootstrap/cache/routes.php';
$_SERVER['APP_ROUTES_CACHE'] = $tmp . '/bootstrap/cache/routes.php';
putenv('APP_ROUTES_CACHE=' . $tmp . '/bootstrap/cache/routes.php');

$_ENV['APP_EVENTS_CACHE'] = $tmp . '/bootstrap/cache/events.php';
$_SERVER['APP_EVENTS_CACHE'] = $tmp . '/bootstrap/cache/events.php';
putenv('APP_EVENTS_CACHE=' . $tmp . '/bootstrap/cache/events.php');

// Copy pre-discovered packages and services if present in repository
$baseBootstrapCache = __DIR__ . '/../bootstrap/cache';
if (is_dir($baseBootstrapCache)) {
    foreach (['packages.php', 'services.php'] as $cacheFile) {
        $source = $baseBootstrapCache . '/' . $cacheFile;
        $dest = $tmp . '/bootstrap/cache/' . $cacheFile;
        if (is_file($source) && !is_file($dest)) {
            copy($source, $dest);
        }
    }
}

// Forward request to standard Laravel public/index.php entrypoint
require __DIR__ . '/../public/index.php';
