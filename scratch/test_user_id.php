<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
echo "User ID: " . $user->id . ", Name: " . $user->name . "\n";
$civitas = \App\Models\CivitasPendidikan::where('source_id', $user->id)->first();
if ($civitas) {
    echo "Matched Civitas UUID: " . $civitas->uuid . ", Source Type: " . $civitas->source_type . "\n";
} else {
    echo "No matching Civitas found for source_id = " . $user->id . "\n";
}
