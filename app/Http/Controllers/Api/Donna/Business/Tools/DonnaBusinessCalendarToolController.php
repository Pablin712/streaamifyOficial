<?php

namespace App\Http\Controllers\Api\Donna\Business\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaAgentConfig;
use App\Models\DonnaIntegration;
use App\Services\Donna\DonnaToolLogger;
use App\Services\Donna\Google\DonnaCalendarService;
use App\Services\Donna\Google\DonnaGoogleTokenService;
use Illuminate\Http\Request;

class DonnaBusinessCalendarToolController extends Controller
{
    public function __construct(
        private DonnaGoogleTokenService $tokenService,
        private DonnaCalendarService    $calendar,
        private DonnaToolLogger         $logger,
    ) {}

    private function resolveToken(Request $request): array
    {
        $clientId  = (int) $request->input('client_id');
        $serviceId = (int) $request->input('service_id');
        $channelId = (int) $request->input('channel_id');

        $config = DonnaAgentConfig::where('client_id', $clientId)
            ->where('service_type', 'business')
            ->where('is_active', true)
            ->first();

        if (!$config?->calendar_enabled) {
            return ['error' => [
                'success' => false,
                'error'   => 'calendar_not_enabled',
                'message' => 'Este negocio no tiene habilitado Google Calendar.',
            ]];
        }

        $integ = DonnaIntegration::where('client_id', $clientId)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->first();

        if (!$integ) {
            return ['error' => ['success' => false, 'error' => 'google_not_connected', 'message' => 'Google no está conectado.']];
        }

        $token = $this->tokenService->getValidAccessToken($integ);
        if (!$token) {
            return ['error' => ['success' => false, 'error' => 'google_token_revoked', 'message' => 'Token de Google expirado.']];
        }

        return [
            'token'       => $token,
            'calendar_id' => $config->calendar_id ?? 'primary',
            'clientId'    => $clientId,
            'serviceId'   => $serviceId,
            'channelId'   => $channelId,
            'convId'      => (int) $request->input('conversation_id'),
        ];
    }

    private function calendarError(array $googleError, string $toolName, int $clientId, int $serviceId, int $channelId, array $req): \Illuminate\Http\JsonResponse
    {
        $code    = $googleError['code'] ?? 0;
        $message = $googleError['message'] ?? 'Error de Google Calendar.';
        $result  = ['success' => false, 'error' => 'google_api_error', 'message' => "Error de Google Calendar: {$message}", 'error_code' => "google_{$code}"];
        $this->logger->log($toolName, $clientId, $req, $result, $serviceId, $channelId);
        return response()->json($result, 422);
    }

    public function listEvents(Request $request)
    {
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);
        $start  = microtime(true);
        $result = $this->calendar->listEvents($ctx['token'], $ctx['calendar_id'], $request->input('time_min'), $request->input('time_max'), (int) $request->input('max_results', 20));
        $ms     = (int) ((microtime(true) - $start) * 1000);
        if (!($result['success'] ?? false)) return $this->calendarError($result['google_error'] ?? [], 'donna_business_calendar_list_events', $ctx['clientId'], $ctx['serviceId'], $ctx['channelId'], $request->all());
        $this->logger->log('donna_business_calendar_list_events', $ctx['clientId'], $request->only(['time_min', 'time_max']), $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function freebusy(Request $request)
    {
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);
        $tz     = $request->input('timezone', 'America/Guayaquil');
        $start  = microtime(true);
        $result = $this->calendar->freebusy($ctx['token'], $request->input('calendar_id', $ctx['calendar_id']), $request->input('time_min'), $request->input('time_max'), $tz);
        $ms     = (int) ((microtime(true) - $start) * 1000);
        if (!($result['success'] ?? false)) return $this->calendarError($result['google_error'] ?? [], 'donna_business_calendar_freebusy', $ctx['clientId'], $ctx['serviceId'], $ctx['channelId'], $request->all());
        $this->logger->log('donna_business_calendar_freebusy', $ctx['clientId'], $request->only(['time_min', 'time_max']), $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function createEvent(Request $request)
    {
        $request->validate(['summary' => 'required|string', 'start' => 'required|string', 'end' => 'required|string']);
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);
        $start  = microtime(true);
        $result = $this->calendar->createEvent($ctx['token'], $request->input('calendar_id', $ctx['calendar_id']), $request->all());
        $ms     = (int) ((microtime(true) - $start) * 1000);
        if (!($result['success'] ?? false)) return $this->calendarError($result['google_error'] ?? [], 'donna_business_calendar_create_event', $ctx['clientId'], $ctx['serviceId'], $ctx['channelId'], $request->all());
        $this->logger->log('donna_business_calendar_create_event', $ctx['clientId'], $request->only(['summary', 'start', 'end']), $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function updateEvent(Request $request)
    {
        $request->validate(['event_id' => 'required|string']);
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);
        $start  = microtime(true);
        $result = $this->calendar->updateEvent($ctx['token'], $request->input('calendar_id', $ctx['calendar_id']), $request->input('event_id'), $request->all());
        $ms     = (int) ((microtime(true) - $start) * 1000);
        if (!($result['success'] ?? false)) return $this->calendarError($result['google_error'] ?? [], 'donna_business_calendar_update_event', $ctx['clientId'], $ctx['serviceId'], $ctx['channelId'], $request->all());
        $this->logger->log('donna_business_calendar_update_event', $ctx['clientId'], $request->only(['event_id', 'summary']), $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function deleteEvent(Request $request)
    {
        $request->validate(['event_id' => 'required|string']);
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);
        $start  = microtime(true);
        $result = $this->calendar->deleteEvent($ctx['token'], $request->input('calendar_id', $ctx['calendar_id']), $request->input('event_id'));
        $ms     = (int) ((microtime(true) - $start) * 1000);
        if (!($result['success'] ?? false)) return $this->calendarError($result['google_error'] ?? [], 'donna_business_calendar_delete_event', $ctx['clientId'], $ctx['serviceId'], $ctx['channelId'], $request->all());
        $this->logger->log('donna_business_calendar_delete_event', $ctx['clientId'], ['event_id' => $request->input('event_id')], $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }
}
