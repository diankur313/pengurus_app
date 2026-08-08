<?php
register_shutdown_function(function() {
    $e = error_get_last();
    if ($e) {
        header('Content-Type: text/plain');
        echo "FATAL: " . json_encode($e);
    }
});
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

echo "autoload... ";
require __DIR__.'/../vendor/autoload.php';
echo "OK\n";

echo "bootstrap/app.php... ";
$app = require_once __DIR__.'/../bootstrap/app.php';
echo "OK\n";

echo "kernel... ";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
echo "OK\n";

echo "handle... ";
try {
    $res = $kernel->handle($req = Illuminate\Http\Request::capture());
    echo "OK status=" . $res->getStatusCode() . "\n";
} catch (\Throwable $ex) {
    echo "EXCEPTION: " . $ex->getMessage() . "\n";
    echo "FILE: " . $ex->getFile() . ":" . $ex->getLine() . "\n";
}
