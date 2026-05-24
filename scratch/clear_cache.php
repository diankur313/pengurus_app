<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Artisan::call('view:clear');
Illuminate\Support\Facades\Artisan::call('cache:clear');
Illuminate\Support\Facades\Artisan::call('config:clear');
echo "Cache cleared.\n";
