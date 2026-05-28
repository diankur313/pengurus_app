<?php

namespace App\Services;

use App\Models\EducationSchedule;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventReminders;

class GoogleMeetService
{
    protected GoogleClient $client;
    protected Calendar $calendarService;

    public function __construct()
    {
        $this->client = new GoogleClient();
        $this->client->setAuthConfig(base_path(config('google.service_account_path')));
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->addScope('https://www.googleapis.com/auth/meetings.space.created');

        $this->calendarService = new Calendar($this->client);
    }

    /**
     * Buat Google Calendar event + Meet link.
     * Return: [meeting_link, event_id, space_name]
     */
    public function createMeeting(EducationSchedule $schedule): array
    {
        $event = $this->buildCalendarEvent($schedule);

        $createdEvent = $this->calendarService->events->insert(
            config('google.calendar_id'),
            $event,
            ['conferenceDataVersion' => 1]
        );

        $meetLink = $createdEvent->getHangoutLink() ?? '';
        $eventId  = $createdEvent->getId();

        // Patch space settings via Meet REST API (jika meet link tersedia)
        $spaceName = '';
        if ($meetLink) {
            $spaceName = $this->patchSpaceSettings($meetLink, $schedule);
        }

        return [$meetLink, $eventId, $spaceName];
    }

    /**
     * Update Calendar event (waktu, judul, co-host) + patch space settings.
     */
    public function updateMeeting(EducationSchedule $schedule): void
    {
        if (!$schedule->google_event_id) {
            return;
        }

        $event = $this->buildCalendarEvent($schedule);

        $this->calendarService->events->update(
            config('google.calendar_id'),
            $schedule->google_event_id,
            $event,
            ['conferenceDataVersion' => 1]
        );

        if ($schedule->meeting_link && $schedule->google_space_name) {
            $this->patchSpaceSettings($schedule->meeting_link, $schedule);
        }
    }

    /**
     * Hapus Calendar event.
     */
    public function deleteMeeting(string $eventId): void
    {
        try {
            $this->calendarService->events->delete(
                config('google.calendar_id'),
                $eventId
            );
        } catch (Exception $e) {
            // Silent fail — event sudah dihapus manual atau expired
            logger()->warning('GoogleMeetService::deleteMeeting failed', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * Build Google Calendar Event object dari EducationSchedule.
     */
    protected function buildCalendarEvent(EducationSchedule $schedule): Event
    {
        $teacherName = $schedule->teacher?->name ?? 'Pengajar';
        $angkatan    = $schedule->level === 'dasar' ? 'Angkatan Dasar' : 'Angkatan Lanjutan';

        $description = $schedule->meet_description
            ?? "Sesi {$schedule->type} - {$angkatan}\nUstadz: {$teacherName}";

        $event = new Event([
            'summary'     => $schedule->title,
            'description' => $description,
            'start'       => new EventDateTime([
                'dateTime' => $schedule->start_at->toRfc3339String(),
                'timeZone' => 'Asia/Jakarta',
            ]),
            'end'         => new EventDateTime([
                'dateTime' => $schedule->end_at->toRfc3339String(),
                'timeZone' => 'Asia/Jakarta',
            ]),
            'conferenceData' => new ConferenceData([
                'createRequest' => new CreateConferenceRequest([
                    'requestId'             => 'esii-' . $schedule->uuid . '-' . time(),
                    'conferenceSolutionKey' => new ConferenceSolutionKey([
                        'type' => 'hangoutsMeet',
                    ]),
                ]),
            ]),
            'reminders' => new EventReminders([
                'useDefault' => false,
                'overrides'  => [],
            ]),
        ]);

        // Co-host sebagai attendee
        if ($schedule->meet_co_host_email) {
            $event->setAttendees([
                new EventAttendee([
                    'email'          => $schedule->meet_co_host_email,
                    'responseStatus' => 'accepted',
                    'organizer'      => false,
                ]),
            ]);
        }

        return $event;
    }

    /**
     * Patch Meet space settings via Google Meet REST API.
     * Return: space name (ID)
     */
    protected function patchSpaceSettings(string $meetLink, EducationSchedule $schedule): string
    {
        try {
            // Extract meeting code dari URL (format: meet.google.com/xxx-yyyy-zzz)
            $code = basename(parse_url($meetLink, PHP_URL_PATH));
            if (!$code) {
                return '';
            }

            // Get access token from Google Client
            $token = $this->client->fetchAccessTokenWithAssertion();
            if (empty($token['access_token'])) {
                return '';
            }
            $accessToken = $token['access_token'];

            $httpClient = new \GuzzleHttp\Client();
            $apiBase    = 'https://meet.googleapis.com/v2/spaces/';
            $headers    = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ];

            // GET space by meeting code
            $getResp = $httpClient->request('GET', $apiBase . $code, ['headers' => $headers]);
            if ($getResp->getStatusCode() !== 200) {
                return '';
            }
            $spaceData = json_decode($getResp->getBody()->getContents(), true);
            $spaceName = $spaceData['name'] ?? '';

            if (!$spaceName) {
                return '';
            }

            // PATCH space config
            $body = json_encode([
                'config' => [
                    'accessType'       => $schedule->meet_access_type ?? 'OPEN',
                    'entryPointAccess' => $schedule->meet_entry_point_access ?? 'ALL',
                    'moderation'       => $schedule->meet_moderation ?? 'OFF',
                ],
            ]);

            $httpClient->request('PATCH', $apiBase . $spaceName, [
                'headers' => $headers,
                'body'    => $body,
                'query'   => ['updateMask' => 'config.accessType,config.entryPointAccess,config.moderation'],
            ]);

            return $spaceName;
        } catch (Exception $e) {
            logger()->warning('GoogleMeetService::patchSpaceSettings failed', [
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }


    /**
     * Parse reminder HH:MM ke total menit.
     */
    public static function parseReminderToMinutes(string $hhMm): int
    {
        $parts   = explode(':', $hhMm);
        $hours   = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        return ($hours * 60) + $minutes;
    }
}
