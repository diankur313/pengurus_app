<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$perms = \Spatie\Permission\Models\Permission::where('name', 'like', '%education_schedule%')->pluck('name')->toArray();
echo "Permissions: " . implode(', ', $perms) . "\n";
