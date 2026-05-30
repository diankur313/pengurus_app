<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Semua cron dikelola langsung via OS crontab di sisi server.
// Lihat dokumentasi: /www/wwwroot/app2.yiscalazhar.web.id
