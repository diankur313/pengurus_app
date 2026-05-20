<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Clearing View Cache...\n";
\Illuminate\Support\Facades\Artisan::call('view:clear');
echo "Clearing Cache...\n";
\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo "Clearing Config Cache...\n";
\Illuminate\Support\Facades\Artisan::call('config:clear');
echo "Done.\n";
