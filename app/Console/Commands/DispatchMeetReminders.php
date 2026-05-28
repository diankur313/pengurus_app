<?php

namespace App\Console\Commands;

use App\Jobs\SendMeetReminderJob;
use App\Models\CivitasPendidikan;
use App\Models\EducationSchedule;
use App\Services\GoogleMeetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchMeetReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'meet:dispatch-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Dispatch email reminder untuk jadwal Google Meet yang akan dimulai';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        // Cari semua jadwal online yang reminder belum terkirim
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\EducationSchedule> $schedules */
        $schedules = EducationSchedule::query()
            ->where('attendance_mode', 'online')
            ->where('send_reminder', true)
            ->where('reminder_sent', false)
            ->whereNotNull('reminder_before')
            ->whereNotNull('meeting_link')
            ->get();

        $dispatched = 0;

        foreach ($schedules as $schedule) {
            // Parse HH:MM → menit
            $totalMinutes = GoogleMeetService::parseReminderToMinutes($schedule->reminder_before);

            if ($totalMinutes <= 0) {
                continue;
            }

            // Hitung kapan reminder harus dikirim
            $reminderAt = $schedule->start_at->subMinutes($totalMinutes);

            // Cek apakah sudah waktunya (within current minute window)
            if ($now->lt($reminderAt) || $now->gt($schedule->start_at)) {
                continue;
            }

            // Ambil peserta berdasarkan level angkatan
            if (strtolower(trim($schedule->level)) === 'general') {
                $civitas = CivitasPendidikan::all();
            } else {
                $civitas = CivitasPendidikan::where('level_angkatan', $schedule->level)->get();
            }

            $emailCount = 0;
            foreach ($civitas as $peserta) {
                $email = $peserta->email;
                $name  = $peserta->name ?? 'Peserta';

                if (!$email) {
                    continue;
                }

                SendMeetReminderJob::dispatch($schedule, $email, $name);
                $emailCount++;
            }

            // Mark as sent
            $schedule->update(['reminder_sent' => true]);

            $dispatched++;
            $this->info("✓ Reminder dispatched untuk: {$schedule->title} ({$emailCount} email)");

            Log::info('DispatchMeetReminders: dispatched', [
                'schedule_id' => $schedule->id,
                'title'       => $schedule->title,
                'email_count' => $emailCount,
            ]);
        }

        if ($dispatched === 0) {
            $this->line('Tidak ada reminder yang perlu dikirim saat ini.');
        }

        return self::SUCCESS;
    }
}
