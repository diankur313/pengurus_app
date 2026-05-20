<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $columns = Illuminate\Support\Facades\DB::connection('yisic_db_lama')->getSchemaBuilder()->getColumnListing('member');
    echo "Columns in yisic_db_lama.member:\n";
    print_r($columns);
} catch (\Exception $e) {
    echo "Error yisic: " . $e->getMessage() . "\n";
}

try {
    $columns = Illuminate\Support\Facades\DB::connection('ppab')->getSchemaBuilder()->getColumnListing('ppab_member');
    echo "\nColumns in ppab.ppab_member:\n";
    print_r($columns);
} catch (\Exception $e) {
    echo "Error ppab: " . $e->getMessage() . "\n";
}
