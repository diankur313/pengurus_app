<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\KtaController;
use App\Http\Controllers\GoogleAuthController;

// Serve profile pictures from local (private) or external storage
Route::get('/profile-picture/{filename}', function (string $filename) {
    // 1. Cek di penyimpanan lokal app2 (Folder Private)
    $localPath = storage_path('app/private/profile_pictures/' . $filename);
    if (file_exists($localPath)) {
        return response()->file($localPath);
    }

    // 2. Fallback ke penyimpanan awalan (eksternal)
    $externalPath = '/www/wwwroot/ppab.yiscalazhar.web.id/frontend/storage/app/private/profil_picture/' . $filename;
    if (file_exists($externalPath)) {
        return response()->file($externalPath);
    }

    // 3. Fallback ke project join-ppab (eksternal baru)
    $joinPpabPath = '/www/wwwroot/join-ppab.yiscalazhar.web.id/storage/app/public/avatars/' . $filename;
    if (file_exists($joinPpabPath)) {
        return response()->file($joinPpabPath);
    }
    
    // 4. Jika nama file mengandung avatars/ 
    if (str_starts_with($filename, 'avatars/')) {
        $joinPpabPathWithDir = '/www/wwwroot/join-ppab.yiscalazhar.web.id/storage/app/public/' . $filename;
        if (file_exists($joinPpabPathWithDir)) {
            return response()->file($joinPpabPathWithDir);
        }
    }

    abort(404);
})->middleware('auth')->name('profile.picture')->where('filename', '.*');

// Download KTA (Kartu Tanda Anggota)
Route::get('/kta-download/{source}/{id}', [KtaController::class, 'download'])
    ->middleware('auth')
    ->name('kta.download');

// Google OAuth2 — one-time authorization untuk Meet API
Route::get('/google/auth', [GoogleAuthController::class, 'redirect'])
    ->middleware('auth')
    ->name('google.auth');

Route::get('/google/auth/callback', [GoogleAuthController::class, 'callback'])
    ->name('google.auth.callback');

Route::get('/google/auth/status', [GoogleAuthController::class, 'status'])
    ->middleware('auth')
    ->name('google.auth.status');
