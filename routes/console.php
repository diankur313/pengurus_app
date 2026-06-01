<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim email reminder sebelum jadwal
Schedule::command('meet:dispatch-reminders')->everyMinute();

// End Meet space yang jadwalnya sudah berakhir (jalankan setiap 5 menit)
Schedule::command('meet:end-expired')->everyFiveMinutes();

// Cek dan update withdrawable payment (cukup jalankan sekali sehari di jam 00:01)
Schedule::command('payment:update-withdrawable')->dailyAt('00:01');
