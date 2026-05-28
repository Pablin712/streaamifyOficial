<?php

namespace App\Http\Controllers\Api\Donna\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaAgentConfig;
use App\Models\DonnaIntegration;
use App\Services\Donna\DonnaServiceValidator;
use App\Services\Donna\DonnaToolLogger;
use App\Services\Donna\Google\DonnaCalendarService;
use App\Services\Donna\Google\DonnaGoogleTokenService;
use Illuminate\Http\Request;

class DonnaCalendarToolController extends Controller
{
    public function __construct(
        private DonnaServiceValidator   $validator,
        private DonnaGoogleTokenService $tokenService,
        private DonnaCalendarService    $calendar,
        private DonnaToolLogger         $logger
    ) {}

    private function resolveToken(Request $request): array
    {
        $clientId      = (int) $request->input('client_id');
        $subscriptionId= (int) $request->input('service_id');
        $channelId     = (int) $request->input('channel_id');

        $validation = $this->validator->validateForTool($clientId, $subscriptionId, $channelId);
        if (!$validation['allowed']) return ['error' => $validation];

        $integ = DonnaIntegration::where('client_id', $clientId)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->first();

        if (!$integ) {
            return ['error' => [
                'success' => false, 'allowed' => false,
                'reason' => 'google_not_connected',
                'message' => 'La cuenta Google del cliente no está conectada.',
            ]];
        }

        $token = $this->tokenService->getValidAccessToken($integ);
        if (!$token) {
            return ['error' => [
                'success' => false, 'allowed' => false,
                'reason' => 'google_token_revoked',
                'message' => 'La conexión con Google expiró o fue revocada. Vuelve a conectar Google desde Streamify.',
            ]];
        }

        $config = DonnaAgentConfig::where('client_id', $clientId)
            ->where('service_type', 'personal')
            ->first();

        return [
            'token'          => $token,
            'calendar_id'    => $config?->calendar_id ?? 'primary',
            'clientId'       => $clientId,
            'subscriptionId' => $subscriptionId,
            'channelId'      => $channelId,
            'validation'     => $validation,
        ];
    }

    private function toolError(array $googleError, string $toolName, int $clientId, array $request): \Illuminate\Http\JsonResponse
    {
        $result = [
            'success' => false, 'allowed' => true,
            'reason'  => 'google_api_error',
            'message' => 'Google devolvió un error al ejecutar la acción.',
            'error_code' => 'google_' . ($googleError['code'] ?? '?'),
        ];
        $this->logger->log($toolName, $clientId, $request, $result);
        return response()->json($result, 422);
    }

    public function listEvents(Request $request)
    {
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $start = microtime(true);
        $result = $this->calendar->listEvents(
            $ctx['token'], $ctx['calendar_id'],
            $request->input('time_min'),
            $request->input('time_max'),
            (int) $request->input('max_results', 20)
        );
        $ms = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->toolError($result['google_error'] ?? [], 'donna_calendar_list_events', $ctx['clientId'], $request->all());
        $this->logger->log('donna_calendar_list_events', $ctx['clientId'], $request->only(['time_min','time_max']), $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function freebusy(Request $request)
    {
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $tz     = $request->input('timezone', config('services.donna.google_default_timezone', 'America/Guayaquil'));
        $calId  = $request->input('calendar_id', $ctx['calendar_id']);
        $start  = microtime(true);
        $result = $this->calendar->freebusy($ctx['token'], $calId, $request->input('time_min'), $request->input('time_max'), $tz);
        $ms     = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->toolError($result['google_error'] ?? [], 'donna_calendar_freebusy', $ctx['clientId'], $request->all());
        $this->logger->log('donna_calendar_freebusy', $ctx['clientId'], $request->only(['time_min','time_max']), $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function createEvent(Request $request)
    {
        $request->validate(['summary' => 'required|string', 'start' => 'required|string', 'end' => 'required|string']);
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $calId  = $request->input('calendar_id', $ctx['calendar_id']);
        $start  = microtime(true);
        $result = $this->calendar->createEvent($ctx['token'], $calId, $request->all());
        $ms     = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->toolError($result['google_error'] ?? [], 'donna_calendar_create_event', $ctx['clientId'], $request->all());
        $this->logger->log('donna_calendar_create_event', $ctx['clientId'], $request->only(['summary','start','end','location']), $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function updateEvent(Request $request)
    {
        $request->validate(['event_id' => 'required|string']);
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $calId   = $request->input('calendar_id', $ctx['calendar_id']);
        $eventId = $request->input('event_id');
        $start   = microtime(true);
        $result  = $this->calendar->updateEvent($ctx['token'], $calId, $eventId, $request->all());
        $ms      = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->toolError($result['google_error'] ?? [], 'donna_calendar_update_event', $ctx['clientId'], $request->all());
        $this->logger->log('donna_calendar_update_event', $ctx['clientId'], $request->only(['event_id','summary','start','end']), $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function deleteEvent(Request $request)
    {
        $request->validate(['event_id' => 'required|string']);
        $ctx = $this->resolveToken($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $calId   = $request->input('calendar_id', $ctx['calendar_id']);
        $eventId = $request->input('event_id');
        $start   = microtime(true);
        $result  = $this->calendar->deleteEvent($ctx['token'], $calId, $eventId);
        $ms      = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->toolError($result['google_error'] ?? [], 'donna_calendar_delete_event', $ctx['clientId'], $request->all());
        $this->logger->log('donna_calendar_delete_event', $ctx['clientId'], ['event_id' => $eventId], $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }
}
