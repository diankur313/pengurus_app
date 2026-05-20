<?php

use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "Generating Shield permissions...\n";
$exitCode = Artisan::call('shield:generate', ['--all' => true]);
echo "Exit Code: " . $exitCode . "\n";
echo Artisan::output();
