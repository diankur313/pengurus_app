<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "LAMA:\n";
try {
    print_r(Schema::connection('yisic_db_lama')->getColumnListing('member'));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nPPAB:\n";
try {
    print_r(Schema::connection('ppab')->getColumnListing('ppab_member'));
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
