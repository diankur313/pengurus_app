<?php

namespace App\Console\Commands;

use App\Models\EducationSchedule;
use App\Services\GoogleMeetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EndExpiredMeetSpaces extends Command
{
    protected $signature = 'meet:end-expired';

    protected $description = 'End active Google Meet spaces for schedules that have already ended';

    public function handle(): int
    {
        $thirtyMinsAgo = now()->subMinutes(30);
        $twoHoursAgo   = now()->subHours(2);

        $service = new GoogleMeetService();
        $restrictedCount = 0;
        $expiredCount    = 0;

        // Fase 1: 30 Menit setelah jadwal selesai -> RESTRICTED & Kick
        $schedulesToRestrict = EducationSchedule::query()
            ->where('attendance_mode', 'online')
            ->whereNotNull('google_space_name')
            ->whereNotNull('meeting_link')
            ->where('end_at', '<=', $thirtyMinsAgo)
            ->get();

        foreach ($schedulesToRestrict as $schedule) {
            try {
                // Ubah akses ke RESTRICTED agar jika ada yang mencoba masuk lagi,
                // mereka akan nyangkut di "Ask to join" (menunggu diizinkan host).
                $schedule->meet_access_type = 'RESTRICTED';
                $service->patchSpaceSettings($schedule->meeting_link, $schedule);

                // Keluarkan peserta yang sedang aktif di dalam (jika ada)
                $service->endMeetSpace($schedule->google_space_name);

                // Hapus space_name agar command ini tidak proses ulang di Fase 1
                $schedule->update(['google_space_name' => null]);

                $restrictedCount++;
                $this->info("✓ Meet space RESTRICTED (T+30m): {$schedule->title}");

                Log::info('EndExpiredMeetSpaces: restricted', [
                    'schedule_id' => $schedule->id,
                ]);
            } catch (\Exception $e) {
                $this->error("✗ Gagal restrict space: {$schedule->title} — {$e->getMessage()}");
            }
        }

        // Fase 2: 2 Jam setelah jadwal selesai -> Benar-benar Expired (Hapus event kalender & link)
        $schedulesToExpire = EducationSchedule::query()
            ->where('attendance_mode', 'online')
            ->whereNotNull('meeting_link')
            ->whereNull('google_space_name') // Pastikan sudah melewati Fase 1
            ->where('end_at', '<=', $twoHoursAgo)
            ->get();

        foreach ($schedulesToExpire as $schedule) {
            try {
                if ($schedule->google_event_id) {
                    // Ini akan menghapus event dari Google Calendar, mematikan link secara permanen
                    $service->deleteMeeting($schedule->google_event_id, null);
                }

                // Hapus link dari sistem kita agar tidak bisa di-klik lagi
                $schedule->update([
                    'meeting_link'    => null,
                    'google_event_id' => null,
                ]);

                $expiredCount++;
                $this->info("✓ Meet space EXPIRED (T+2h): {$schedule->title}");

                Log::info('EndExpiredMeetSpaces: expired completely', [
                    'schedule_id' => $schedule->id,
                ]);
            } catch (\Exception $e) {
                $this->error("✗ Gagal expire space: {$schedule->title} — {$e->getMessage()}");
            }
        }

        if ($restrictedCount === 0 && $expiredCount === 0) {
            $this->line('Tidak ada Meet space yang perlu diproses.');
        } else {
            $this->info("Selesai. {$restrictedCount} di-restrict, {$expiredCount} di-expired.");
        }

        return self::SUCCESS;
    }
}
