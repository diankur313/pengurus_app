<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->handle($request);
Artisan::call('route:cache');
Artisan::call('config:cache');
Artisan::call('view:clear');
echo json_encode(['status' => 'ok', 'msg' => 'route+config cached, view cleared']);
