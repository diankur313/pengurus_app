<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

try {
    echo "Running shield:generate --all...\n";
    $exitCode = Artisan::call('shield:generate', ['--all' => true]);
    echo "Exit Code: " . $exitCode . "\n";
    echo "Output: \n" . Artisan::output() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
