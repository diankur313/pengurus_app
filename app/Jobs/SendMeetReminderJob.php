<?php

namespace App\Jobs;

use App\Mail\MeetReminderMail;
use App\Models\EducationSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMeetReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max retry attempts.
     */
    public int $tries = 3;

    /**
     * Delay (seconds) before retry.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public EducationSchedule $schedule,
        public string $recipientEmail,
        public string $recipientName,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->recipientEmail) {
            return;
        }

        Mail::to($this->recipientEmail)
            ->send(new MeetReminderMail(
                $this->schedule,
                $this->recipientName,
            ));
    }
}
