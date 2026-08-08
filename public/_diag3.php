<?php
header('Content-Type: text/plain');

register_shutdown_function(function() {
    $e = error_get_last();
    if ($e) echo "\nSHUTDOWN ERROR: " . json_encode($e) . "\n";
});

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M');

// Step 1: autoload
try {
    require __DIR__.'/../vendor/autoload.php';
    echo "1. autoload: OK\n";
} catch (\Throwable $e) {
    die("1. autoload FAIL: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine());
}

// Step 2: app bootstrap
try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "2. app bootstrap: OK\n";
} catch (\Throwable $e) {
    die("2. app bootstrap FAIL: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine());
}

// Step 3: kernel make
try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "3. kernel make: OK\n";
} catch (\Throwable $e) {
    die("3. kernel make FAIL: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine());
}

// Step 4: handle request
try {
    $res = $kernel->handle(Illuminate\Http\Request::capture());
    echo "4. handle: OK status=" . $res->getStatusCode() . "\n";
} catch (\Throwable $e) {
    echo "4. handle FAIL: " . $e->getMessage() . "\n";
    echo "   FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
    $prev = $e->getPrevious();
    if ($prev) echo "   PREV: " . $prev->getMessage() . "\n   FILE: " . $prev->getFile() . ":" . $prev->getLine() . "\n";
}

echo "\nMemory peak: " . round(memory_get_peak_usage(true)/1024/1024, 2) . " MB\n";
