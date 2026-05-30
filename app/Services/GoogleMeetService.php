<?php

namespace App\Services;

use App\Models\EducationSchedule;
use Exception;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\EventReminders;
use Google\Service\Exception as GoogleServiceException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class GoogleMeetService
{
    protected GoogleClient $oauthClient;
    protected Calendar $calendarService;

    public function __construct()
    {
        $this->oauthClient = $this->buildOAuthClient();
        $this->calendarService = new Calendar($this->oauthClient);
    }

    /**
     * Build OAuth2 client with stored refresh token.
     */
    protected function buildOAuthClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId(config('google.oauth_client_id'));
        $client->setClientSecret(config('google.oauth_client_secret'));
        $client->setAccessType('offline');

        $tokenPath = config('google.oauth_token_path');

        if (!file_exists($tokenPath)) {
            throw new Exception(
                'Google OAuth belum dikonfigurasi. Admin harus kunjungi ' .
                url('/google/auth') . ' untuk setup pertama kali.'
            );
        }

        $token = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($token);

        // Refresh jika expired
        if ($client->isAccessTokenExpired()) {
            if (!empty($token['refresh_token'])) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);

                // Pertahankan refresh_token lama jika tidak dikembalikan
                if (empty($newToken['refresh_token'])) {
                    $newToken['refresh_token'] = $token['refresh_token'];
                }

                $client->setAccessToken($newToken);
                file_put_contents($tokenPath, json_encode($newToken, JSON_PRETTY_PRINT));
            } else {
                throw new Exception(
                    'Refresh token tidak tersedia. Admin harus re-authorize di ' .
                    url('/google/auth')
                );
            }
        }

        return $client;
    }

    /**
     * Buat Google Meet space + Calendar event.
     * Return: [meeting_link, event_id, space_name]
     */
    public function createMeeting(EducationSchedule $schedule): array
    {
        // 1. Buat Meet space via REST API
        [$meetLink, $spaceName] = $this->createMeetSpace($schedule);

        // 2. Buat Calendar event dengan Meet link di description
        $event = $this->buildCalendarEvent($schedule, $meetLink);

        $createdEvent = $this->calendarService->events->insert(
            config('google.calendar_id'),
            $event
        );

        $eventId = $createdEvent->getId();

        Log::info('GoogleMeetService::createMeeting — berhasil', [
            'schedule_id' => $schedule->id,
            'event_id'    => $eventId,
            'meet_link'   => $meetLink,
            'space_name'  => $spaceName,
        ]);

        return [$meetLink, $eventId, $spaceName];
    }

    /**
     * Update Calendar event.
     *
     * @return bool true jika berhasil, false jika event 404
     */
    public function updateMeeting(EducationSchedule $schedule): bool
    {
        if (!$schedule->google_event_id) {
            return false;
        }

        try {
            $event = $this->buildCalendarEvent($schedule, $schedule->meeting_link);

            $this->calendarService->events->update(
                config('google.calendar_id'),
                $schedule->google_event_id,
                $event
            );

            Log::info('GoogleMeetService::updateMeeting — berhasil', [
                'schedule_id'     => $schedule->id,
                'google_event_id' => $schedule->google_event_id,
            ]);
        } catch (GoogleServiceException $e) {
            if ($e->getCode() === 404) {
                Log::warning('GoogleMeetService::updateMeeting — event not found (404)', [
                    'schedule_id'     => $schedule->id,
                    'google_event_id' => $schedule->google_event_id,
                ]);
                return false;
            }
            throw $e;
        }

        return true;
    }

    /**
     * Hapus Calendar event + end Meet space.
     */
    public function deleteMeeting(string $eventId, ?string $spaceName = null): void
    {
        // 1. Hapus Calendar event
        try {
            $this->calendarService->events->delete(
                config('google.calendar_id'),
                $eventId
            );
            Log::info('GoogleMeetService::deleteMeeting — calendar event dihapus', ['event_id' => $eventId]);
        } catch (GoogleServiceException $e) {
            if ($e->getCode() === 404) {
                Log::info('GoogleMeetService::deleteMeeting — event sudah tidak ada (404), skip', [
                    'event_id' => $eventId,
                ]);
            } else {
                Log::warning('GoogleMeetService::deleteMeeting — gagal hapus calendar', [
                    'event_id' => $eventId,
                    'code'     => $e->getCode(),
                    'error'    => $e->getMessage(),
                ]);
            }
        } catch (Exception $e) {
            Log::warning('GoogleMeetService::deleteMeeting — unexpected error', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);
        }

        // 2. End Meet space (jika ada)
        if ($spaceName) {
            $this->endMeetSpace($spaceName);
        }
    }

    /**
     * End/terminate Meet space via REST API.
     * Setelah di-end, link Meet tidak bisa diakses lagi.
     */
    public function endMeetSpace(string $spaceName): void
    {
        try {
            $accessToken = $this->oauthClient->getAccessToken()['access_token'] ?? '';
            if (!$accessToken) {
                Log::warning('GoogleMeetService::endMeetSpace — no access token');
                return;
            }

            $httpClient = new HttpClient();
            $url = 'https://meet.googleapis.com/v2/' . $spaceName . ':endActiveConference';

            $httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'body' => '{}',
            ]);

            Log::info('GoogleMeetService::endMeetSpace — berhasil', ['space_name' => $spaceName]);
        } catch (ClientException $e) {
            $code = $e->getResponse()->getStatusCode();
            // 404 = space sudah tidak ada, aman
            if ($code === 404) {
                Log::info('GoogleMeetService::endMeetSpace — space sudah tidak ada (404)', ['space_name' => $spaceName]);
            } else {
                Log::warning('GoogleMeetService::endMeetSpace — HTTP error', [
                    'space_name'  => $spaceName,
                    'status_code' => $code,
                    'error'       => $e->getMessage(),
                ]);
            }
        } catch (Exception $e) {
            Log::warning('GoogleMeetService::endMeetSpace — error', [
                'space_name' => $spaceName,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Buat Meet space via Google Meet REST API (OAuth2).
     * Return: [meetLink, spaceName]
     */
    protected function createMeetSpace(EducationSchedule $schedule): array
    {
        $accessToken = $this->oauthClient->getAccessToken()['access_token'] ?? '';
        if (!$accessToken) {
            throw new Exception('Access token tidak tersedia untuk Meet API');
        }

        $httpClient = new HttpClient();
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $body = json_encode([
            'config' => [
                'accessType'       => $schedule->meet_access_type ?? 'OPEN',
                'entryPointAccess' => $schedule->meet_entry_point_access ?? 'ALL',
            ],
        ]);

        $response = $httpClient->request('POST', 'https://meet.googleapis.com/v2/spaces', [
            'headers' => $headers,
            'body'    => $body,
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $spaceName  = $data['name'] ?? '';
        $meetingUri = $data['meetingUri'] ?? '';

        if (!$meetingUri && !empty($data['meetingCode'])) {
            $meetingUri = 'https://meet.google.com/' . $data['meetingCode'];
        }

        Log::info('GoogleMeetService::createMeetSpace — berhasil', [
            'schedule_id' => $schedule->id,
            'space_name'  => $spaceName,
            'meeting_uri' => $meetingUri,
        ]);

        return [$meetingUri, $spaceName];
    }

    /**
     * Build Google Calendar Event object.
     */
    protected function buildCalendarEvent(EducationSchedule $schedule, ?string $meetLink = null): Event
    {
        $teacherName = $schedule->teacher?->name ?? 'Pengajar';
        $angkatan    = $schedule->level === 'dasar' ? 'Angkatan Dasar' : 'Angkatan Lanjutan';

        $description = $schedule->meet_description
            ?? "Sesi {$schedule->type} - {$angkatan}\nUstadz: {$teacherName}";

        if ($meetLink) {
            $description .= "\n\n🎥 Google Meet: {$meetLink}";
        }

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
     * Patch Meet space settings.
     */
    public function patchSpaceSettings(string $meetLink, EducationSchedule $schedule): string
    {
        try {
            $code = basename(parse_url($meetLink, PHP_URL_PATH));
            if (!$code) return '';

            $accessToken = $this->oauthClient->getAccessToken()['access_token'] ?? '';
            if (!$accessToken) return '';

            $httpClient = new HttpClient();
            $apiBase    = 'https://meet.googleapis.com/v2/spaces/';
            $headers    = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ];

            $getResp = $httpClient->request('GET', $apiBase . $code, ['headers' => $headers]);
            $spaceData = json_decode($getResp->getBody()->getContents(), true);
            $spaceName = $spaceData['name'] ?? '';
            if (!$spaceName) return '';

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
        } catch (ClientException $e) {
            Log::warning('GoogleMeetService::patchSpaceSettings — HTTP error', [
                'meet_link'   => $meetLink,
                'status_code' => $e->getResponse()->getStatusCode(),
                'error'       => $e->getMessage(),
            ]);
            return '';
        } catch (Exception $e) {
            Log::warning('GoogleMeetService::patchSpaceSettings — error', [
                'meet_link' => $meetLink,
                'error'     => $e->getMessage(),
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
