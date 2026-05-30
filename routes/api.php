<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScannerController;

// Auth Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Cron endpoint — secured by CRON_SECRET token
Route::get('/cron/update-withdrawable', function (Request $request) {
    $secret = $request->header('X-Cron-Secret') ?? $request->query('secret');
    if ($secret !== env('CRON_SECRET', 'changeme')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    \Illuminate\Support\Facades\Artisan::call('payment:update-withdrawable');
    $output = \Illuminate\Support\Facades\Artisan::output();
    return response()->json(['status' => 'ok', 'output' => trim($output)]);
});

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/attendance/scan', [ScannerController::class, 'scan']);
    
    // Endpoint untuk mendapatkan profil user (opsional)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    });
});
