<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = \Illuminate\Support\Facades\DB::connection('ppab')->select('SHOW COLUMNS FROM ppab_sessions');
foreach ($columns as $column) {
    echo $column->Field . PHP_EOL;
}
