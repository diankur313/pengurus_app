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
        $now = now();

        // Jadwal online yang sudah berakhir, masih punya google_space_name, belum di-end
        $schedules = EducationSchedule::query()
            ->where('attendance_mode', 'online')
            ->whereNotNull('google_space_name')
            ->whereNotNull('meeting_link')
            ->where('end_at', '<', $now)
            ->get();

        if ($schedules->isEmpty()) {
            $this->line('Tidak ada Meet space yang perlu di-end.');
            return self::SUCCESS;
        }

        $service = new GoogleMeetService();
        $ended   = 0;

        foreach ($schedules as $schedule) {
            try {
                $service->endMeetSpace($schedule->google_space_name);

                // Hapus space_name agar command ini tidak proses ulang
                $schedule->update(['google_space_name' => null]);

                $ended++;
                $this->info("✓ Meet space di-end: {$schedule->title} (ID: {$schedule->id})");

                Log::info('EndExpiredMeetSpaces: ended', [
                    'schedule_id' => $schedule->id,
                    'title'       => $schedule->title,
                    'end_at'      => $schedule->end_at,
                ]);
            } catch (\Exception $e) {
                $this->error("✗ Gagal end space: {$schedule->title} — {$e->getMessage()}");
                Log::warning('EndExpiredMeetSpaces: failed', [
                    'schedule_id' => $schedule->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $this->info("Selesai. {$ended} Meet space di-end.");
        return self::SUCCESS;
    }
}
