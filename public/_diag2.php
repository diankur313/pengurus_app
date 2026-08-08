<?php
header('Content-Type: text/plain');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require __DIR__.'/../vendor/autoload.php';
    echo "AUTOLOAD OK\n";
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "APP OK\n";
} catch (\Throwable $ex) {
    echo "EXCEPTION: " . $ex->getMessage() . "\n" . $ex->getFile() . ":" . $ex->getLine();
}
