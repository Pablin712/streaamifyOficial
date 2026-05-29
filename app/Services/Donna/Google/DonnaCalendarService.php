<?php

namespace App\Services\Donna\Google;

use Illuminate\Support\Facades\Http;

class DonnaCalendarService
{
    private const BASE = 'https://www.googleapis.com/calendar/v3';

    private function http(string $token)
    {
        return Http::withToken($token)->acceptJson();
    }

    private function googleError(\Illuminate\Http\Client\Response $response): array
    {
        $body = $response->json();
        return $body['error'] ?? ['code' => $response->status(), 'message' => $body['message'] ?? 'Error desconocido de Google.'];
    }

    public function listEvents(string $token, string $calendarId, ?string $timeMin, ?string $timeMax, int $maxResults = 20): array
    {
        if (!$timeMin || !$timeMax) {
            return ['success' => false, 'google_error' => ['code' => 400, 'message' => 'Se requieren time_min y time_max en formato ISO 8601 con timezone (ej: 2026-05-29T00:00:00-05:00).']];
        }

        $response = $this->http($token)->get(self::BASE . "/calendars/{$calendarId}/events", [
            'timeMin'      => $timeMin,
            'timeMax'      => $timeMax,
            'singleEvents' => true,
            'orderBy'      => 'startTime',
            'maxResults'   => $maxResults,
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $this->googleError($response)];
        }

        $events = collect($response->json('items', []))->map(fn ($e) => [
            'id'          => $e['id'],
            'summary'     => $e['summary'] ?? '(sin título)',
            'description' => $e['description'] ?? null,
            'start'       => $e['start']['dateTime'] ?? $e['start']['date'] ?? null,
            'end'         => $e['end']['dateTime'] ?? $e['end']['date'] ?? null,
            'location'    => $e['location'] ?? null,
            'html_link'   => $e['htmlLink'] ?? null,
        ])->values()->toArray();

        return ['success' => true, 'events' => $events];
    }

    public function freebusy(string $token, string $calendarId, ?string $timeMin, ?string $timeMax, string $timezone): array
    {
        if (!$timeMin || !$timeMax) {
            return ['success' => false, 'google_error' => ['code' => 400, 'message' => 'Se requieren time_min y time_max en formato ISO 8601 con timezone (ej: 2026-05-29T00:00:00-05:00).']];
        }

        $response = $this->http($token)->post(self::BASE . '/freeBusy', [
            'timeMin'  => $timeMin,
            'timeMax'  => $timeMax,
            'timeZone' => $timezone,
            'items'    => [['id' => $calendarId]],
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $this->googleError($response)];
        }

        $busy = $response->json("calendars.{$calendarId}.busy", []);
        return [
            'success'      => true,
            'calendar_id'  => $calendarId,
            'busy'         => $busy,
            'is_available' => empty($busy),
            'message'      => empty($busy) ? 'No hay eventos en ese rango.' : 'Hay eventos ocupados en ese rango.',
        ];
    }

    public function createEvent(string $token, string $calendarId, array $data): array
    {
        $body = [
            'summary'     => $data['summary'],
            'description' => $data['description'] ?? 'Agendada por Donna Personal',
            'location'    => $data['location'] ?? null,
            'start'       => ['dateTime' => $data['start'], 'timeZone' => $data['timezone'] ?? 'America/Guayaquil'],
            'end'         => ['dateTime' => $data['end'],   'timeZone' => $data['timezone'] ?? 'America/Guayaquil'],
            'colorId'     => '2',
        ];

        if (!empty($data['attendees'])) {
            $body['attendees'] = array_map(fn ($e) => ['email' => $e], $data['attendees']);
        }

        $response = $this->http($token)->post(self::BASE . "/calendars/{$calendarId}/events", $body);

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $this->googleError($response)];
        }

        $e = $response->json();
        return [
            'success' => true,
            'event'   => [
                'id'        => $e['id'],
                'summary'   => $e['summary'],
                'start'     => $e['start']['dateTime'] ?? $e['start']['date'],
                'end'       => $e['end']['dateTime'] ?? $e['end']['date'],
                'html_link' => $e['htmlLink'] ?? null,
            ],
            'message' => 'Evento creado correctamente.',
        ];
    }

    public function updateEvent(string $token, string $calendarId, string $eventId, array $data): array
    {
        $body = array_filter([
            'summary'     => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'location'    => $data['location'] ?? null,
        ]);

        if (isset($data['start'])) {
            $body['start'] = ['dateTime' => $data['start'], 'timeZone' => $data['timezone'] ?? 'America/Guayaquil'];
        }
        if (isset($data['end'])) {
            $body['end'] = ['dateTime' => $data['end'], 'timeZone' => $data['timezone'] ?? 'America/Guayaquil'];
        }

        $response = $this->http($token)->patch(self::BASE . "/calendars/{$calendarId}/events/{$eventId}", $body);

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $this->googleError($response)];
        }

        $e = $response->json();
        return [
            'success' => true,
            'event'   => [
                'id'        => $e['id'],
                'summary'   => $e['summary'],
                'start'     => $e['start']['dateTime'] ?? $e['start']['date'],
                'end'       => $e['end']['dateTime'] ?? $e['end']['date'],
                'html_link' => $e['htmlLink'] ?? null,
            ],
            'message' => 'Evento actualizado correctamente.',
        ];
    }

    public function deleteEvent(string $token, string $calendarId, string $eventId): array
    {
        $response = $this->http($token)->delete(self::BASE . "/calendars/{$calendarId}/events/{$eventId}");

        if ($response->status() === 204 || $response->successful()) {
            return ['success' => true, 'deleted' => true, 'event_id' => $eventId, 'message' => 'Evento eliminado correctamente.'];
        }

        return ['success' => false, 'google_error' => $this->googleError($response)];
    }
}
