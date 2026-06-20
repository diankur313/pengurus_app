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

// PPAB Ticket Locks (Reminders and Release)
Schedule::call(function () {
    // 1. Send reminders (30 minutes before expiration)
    $reminders = \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_ticket_locks')
        ->where('expires_at', '<=', now()->addMinutes(30))
        ->where('reminder_sent', false)
        ->get();

    foreach ($reminders as $lock) {
        $user = \Illuminate\Support\Facades\DB::table('ppab_member')->find($lock->user_id);
        if ($user && $user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\TicketReminderMail($user, $lock->ticket_type));
            } catch (\Exception $e) {
                // Ignore mail errors
            }
        }
        \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_ticket_locks')->where('id', $lock->id)->update(['reminder_sent' => true]);
    }

    // 2. Release expired locks
    $expiredLocks = \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_ticket_locks')
        ->where('expires_at', '<=', now())
        ->get();

    foreach ($expiredLocks as $lock) {
        if ($lock->ticket_type === 'paket-full') {
            \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_sessions')->where('id', $lock->session_id)->increment('quota_full', 1);
        } elseif ($lock->ticket_type === 'paket-dp') {
            \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_sessions')->where('id', $lock->session_id)->increment('quota_dp', 1);
        } elseif ($lock->ticket_type === 'paket-eb') {
            \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_sessions')->where('id', $lock->session_id)->increment('quota_early_bird', 1);
        } elseif ($lock->ticket_type === 'paket-b2') {
            \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_sessions')->where('id', $lock->session_id)->increment('quota_full', 2);
        } elseif ($lock->ticket_type === 'paket-b3') {
            \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_sessions')->where('id', $lock->session_id)->increment('quota_full', 3);
        }
        \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_ticket_locks')->where('id', $lock->id)->delete();
    }
})->everyMinute();
