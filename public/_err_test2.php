<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Step 1: autoload... ";
require __DIR__.'/../vendor/autoload.php';
echo "OK. Step 2: app.php... ";
$app = require_once __DIR__.'/../bootstrap/app.php';
echo "OK. Step 3: make kernel... ";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
echo "OK. Step 4: handle request... ";
try {
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "OK status " . $response->getStatusCode();
} catch (\Throwable $e) {
    echo "EX: " . $e->getMessage() . " IN " . $e->getFile() . ":" . $e->getLine();
}
