<?php

namespace App\Mail;

use App\Models\EducationSchedule;
use App\Services\GoogleMeetService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public EducationSchedule $schedule,
        public string $recipientName,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📅 Pengingat Jadwal Pembelajaran Online — ' . $this->schedule->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $reminderMinutes = $this->schedule->reminder_before
            ? GoogleMeetService::parseReminderToMinutes($this->schedule->reminder_before)
            : 15;

        $hours   = intdiv($reminderMinutes, 60);
        $minutes = $reminderMinutes % 60;

        $reminderLabel = match (true) {
            $hours > 0 && $minutes > 0 => "{$hours} jam {$minutes} menit",
            $hours > 0                 => "{$hours} jam",
            default                    => "{$minutes} menit",
        };

        $teacherName = $this->schedule->teacher?->name ?? '-';
        $angkatan    = $this->schedule->levelLabel();
        $tipe        = ucfirst($this->schedule->type ?? 'pembelajaran');

        return new Content(
            view: 'emails.meet-reminder',
            with: [
                'recipientName'   => $this->recipientName,
                'reminderLabel'   => $reminderLabel,
                'scheduleTitle'   => $this->schedule->title,
                'scheduleType'    => $tipe,
                'scheduleLevel'   => $angkatan,
                'teacherName'     => $teacherName,
                'startAt'         => $this->schedule->start_at->format('d M Y, H:i'),
                'endAt'           => $this->schedule->end_at->format('H:i'),
                'portalUrl'       => 'https://e-sii.yiscalazhar.web.id',
                'appName'         => 'e-SII Pendidikan YISC Al Azhar',
                'logoUrl'         => config('app.url') . '/yisclogo.png',
            ]
        );
    }
}
