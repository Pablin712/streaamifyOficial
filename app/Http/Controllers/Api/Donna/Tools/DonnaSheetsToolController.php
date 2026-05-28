<?php

namespace App\Http\Controllers\Api\Donna\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaAgentConfig;
use App\Models\DonnaIntegration;
use App\Services\Donna\DonnaServiceValidator;
use App\Services\Donna\DonnaToolLogger;
use App\Services\Donna\Google\DonnaGoogleTokenService;
use App\Services\Donna\Google\DonnaSheetsTaskService;
use Illuminate\Http\Request;

class DonnaSheetsToolController extends Controller
{
    public function __construct(
        private DonnaServiceValidator   $validator,
        private DonnaGoogleTokenService $tokenService,
        private DonnaSheetsTaskService  $sheets,
        private DonnaToolLogger         $logger
    ) {}

    private function resolveContext(Request $request): array
    {
        $clientId       = (int) $request->input('client_id');
        $subscriptionId = (int) $request->input('service_id');
        $channelId      = (int) $request->input('channel_id');

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
                'message' => 'La conexión con Google expiró. Vuelve a conectar desde Streamify.',
            ]];
        }

        $config = DonnaAgentConfig::where('client_id', $clientId)->where('service_type', 'personal')->first();
        $spreadsheetId = $config?->spreadsheet_id;

        if (!$spreadsheetId) {
            return ['error' => [
                'success' => false, 'allowed' => true,
                'reason' => 'spreadsheet_not_configured',
                'message' => 'El cliente no tiene una hoja de tareas configurada. Contacta a soporte.',
            ]];
        }

        return [
            'token'          => $token,
            'spreadsheet_id' => $spreadsheetId,
            'clientId'       => $clientId,
            'subscriptionId' => $subscriptionId,
            'channelId'      => $channelId,
        ];
    }

    private function sheetsError(array $googleError, string $toolName, int $clientId, array $req): \Illuminate\Http\JsonResponse
    {
        $result = [
            'success' => false, 'allowed' => true,
            'reason'  => 'google_api_error',
            'message' => 'Google devolvió un error al ejecutar la acción.',
            'error_code' => 'google_' . ($googleError['code'] ?? '?'),
        ];
        $this->logger->log($toolName, $clientId, $req, $result);
        return response()->json($result, 422);
    }

    public function getTasks(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $start  = microtime(true);
        $result = $this->sheets->getTasks($ctx['token'], $ctx['spreadsheet_id'], (bool) $request->input('include_completed', false));
        $ms     = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->sheetsError($result['google_error'] ?? [], 'donna_sheets_get_tasks', $ctx['clientId'], $request->all());
        $this->logger->log('donna_sheets_get_tasks', $ctx['clientId'], [], $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function createTask(Request $request)
    {
        $request->validate(['title' => 'required|string']);
        $ctx = $this->resolveContext($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $start  = microtime(true);
        $result = $this->sheets->createTask($ctx['token'], $ctx['spreadsheet_id'], $request->all());
        $ms     = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->sheetsError($result['google_error'] ?? [], 'donna_sheets_create_task', $ctx['clientId'], $request->all());
        $this->logger->log('donna_sheets_create_task', $ctx['clientId'], $request->only(['title','due_date','hours_per_day']), $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function updateTask(Request $request)
    {
        $request->validate(['row_number' => 'required|integer']);
        $ctx = $this->resolveContext($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $start  = microtime(true);
        $result = $this->sheets->updateTask($ctx['token'], $ctx['spreadsheet_id'], (int) $request->input('row_number'), $request->all());
        $ms     = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->sheetsError($result['google_error'] ?? [], 'donna_sheets_update_task', $ctx['clientId'], $request->all());
        $this->logger->log('donna_sheets_update_task', $ctx['clientId'], $request->only(['row_number','title','due_date']), $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function completeTask(Request $request)
    {
        $request->validate(['row_number' => 'required|integer']);
        $ctx = $this->resolveContext($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 403);

        $start  = microtime(true);
        $result = $this->sheets->completeTask($ctx['token'], $ctx['spreadsheet_id'], (int) $request->input('row_number'));
        $ms     = (int) ((microtime(true) - $start) * 1000);

        if (!($result['success'] ?? false)) return $this->sheetsError($result['google_error'] ?? [], 'donna_sheets_complete_task', $ctx['clientId'], $request->all());
        $this->logger->log('donna_sheets_complete_task', $ctx['clientId'], $request->only(['row_number']), $result, $ctx['subscriptionId'], $ctx['channelId'], $ms);
        return response()->json(array_merge($result, ['task_id' => $request->input('task_id')]));
    }
}
