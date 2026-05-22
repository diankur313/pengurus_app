<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;

echo "Publishing filament-shield config...\n";
$exitCode = Artisan::call('vendor:publish', ['--tag' => 'filament-shield-config']);
echo "Exit Code: " . $exitCode . "\n";
echo "Output:\n" . Artisan::output() . "\n";
