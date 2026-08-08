<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScannerController;
use App\Http\Controllers\Api\PpabScannerController;
use App\Http\Controllers\Api\XenditWebhookController;
use App\Http\Controllers\Api\InternalCredentialController;
use App\Http\Controllers\Api\TeacherAttendanceController;
use App\Http\Controllers\Api\AppVersionController;

// Auth Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// App Version Check — dikonsumsi app buat force update, publik (dicek sebelum login)
Route::get('/app-version', [AppVersionController::class, 'check']);

// =====================================================================
// Xendit Webhook — Unified Entry Point (standar, didaftarkan di Xendit Dashboard)
// Deteksi tipe webhook (Invoice vs Disbursement) dilakukan di dalam controller.
// =====================================================================
Route::post('/webhook/xendit', [XenditWebhookController::class, 'handle']);

// Alias lama — tetap aktif agar tidak breaking untuk apps yang sudah pakai endpoint ini
Route::post('/webhook/xendit/invoice', [XenditWebhookController::class, 'handle']);
Route::post('/webhook/xendit/disbursement', [XenditWebhookController::class, 'handle']);
Route::post('/internal/webhook/invoice', [XenditWebhookController::class, 'handle']);

// Internal API untuk child apps — validasi Bearer Token di controller
Route::get('/internal/xendit-credentials', [InternalCredentialController::class, 'getCredentials']);

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

    // Education attendance (existing)
    Route::post('/attendance/scan', [ScannerController::class, 'scan']);

    // =====================================================================
    // PPAB Attendance Scanner — untuk aplikasi mobile/scanner QR
    // =====================================================================
    Route::post('/ppab/scan', [PpabScannerController::class, 'scan']);
    Route::get('/ppab/attendance/status', [PpabScannerController::class, 'checkStatus']);
    Route::get('/ppab/attendance/list', [PpabScannerController::class, 'listBySession']);

    // Endpoint untuk mendapatkan profil user (opsional)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    });

    // Auth user info (resmi, struktur konsisten)
    Route::get('/auth/user', [AuthController::class, 'user']);

    // =====================================================================
    // Teacher (Ustadz) Attendance — manual, untuk aplikasi mobile
    // =====================================================================
    Route::get('/teachers', [TeacherAttendanceController::class, 'teachers']);
    Route::post('/teacher-attendance', [TeacherAttendanceController::class, 'store']);
    Route::get('/teacher-attendance', [TeacherAttendanceController::class, 'index']);
    Route::delete('/teacher-attendance/{id}', [TeacherAttendanceController::class, 'destroy']);
});
