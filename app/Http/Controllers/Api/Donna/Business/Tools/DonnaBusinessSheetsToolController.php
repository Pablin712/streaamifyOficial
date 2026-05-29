<?php

namespace App\Http\Controllers\Api\Donna\Business\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaAgentConfig;
use App\Models\DonnaIntegration;
use App\Services\Donna\DonnaToolLogger;
use App\Services\Donna\Google\DonnaGoogleTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class DonnaBusinessSheetsToolController extends Controller
{
    private const BASE = 'https://sheets.googleapis.com/v4/spreadsheets';

    public function __construct(
        private DonnaGoogleTokenService $tokenService,
        private DonnaToolLogger         $logger,
    ) {}

    private function resolveContext(Request $request): array
    {
        $clientId  = (int) $request->input('client_id');
        $serviceId = (int) $request->input('service_id');
        $channelId = (int) $request->input('channel_id');

        $config = DonnaAgentConfig::where('client_id', $clientId)
            ->where('service_type', 'business')
            ->where('is_active', true)
            ->first();

        if (!$config?->sheets_enabled || !$config?->spreadsheet_id) {
            return ['error' => [
                'success' => false,
                'error'   => 'sheets_not_enabled',
                'message' => 'Este negocio no tiene habilitado Google Sheets.',
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
            'token'          => $token,
            'spreadsheet_id' => $config->spreadsheet_id,
            'sheet_name'     => $config->spreadsheet_name ?? 'Hoja1',
            'clientId'       => $clientId,
            'serviceId'      => $serviceId,
            'channelId'      => $channelId,
        ];
    }

    private function http(string $token)
    {
        return Http::withToken($token)->acceptJson();
    }

    private function googleError($response): array
    {
        $body = $response->json();
        return $body['error'] ?? ['code' => $response->status(), 'message' => 'Error desconocido de Google.'];
    }

    public function getRows(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);

        $range    = $request->input('range', $ctx['sheet_name']);
        $start    = microtime(true);
        $response = $this->http($ctx['token'])->get(self::BASE . "/{$ctx['spreadsheet_id']}/values/{$range}");
        $ms       = (int) ((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            $err = $this->googleError($response);
            return response()->json(['success' => false, 'error' => 'google_api_error', 'message' => $err['message']], 422);
        }

        $rows   = $response->json('values', []);
        $header = array_shift($rows) ?? [];
        $data   = array_map(fn ($row) => array_combine(
            array_slice($header, 0, count($row)),
            array_pad($row, count($header), '')
        ), $rows);

        $result = ['success' => true, 'sheet' => $ctx['sheet_name'], 'rows' => $data, 'count' => count($data)];
        $this->logger->log('donna_business_sheets_get_rows', $ctx['clientId'], ['range' => $range], $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function appendRow(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);

        $values = $request->input('values', []);
        if (empty($values) || !is_array($values)) {
            return response()->json(['success' => false, 'message' => 'Se requiere values como array.'], 422);
        }

        $sheet    = $request->input('sheet', $ctx['sheet_name']);
        $start    = microtime(true);
        $response = $this->http($ctx['token'])->post(
            self::BASE . "/{$ctx['spreadsheet_id']}/values/{$sheet}:append?valueInputOption=USER_ENTERED",
            ['values' => [array_values($values)]]
        );
        $ms = (int) ((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            $err = $this->googleError($response);
            return response()->json(['success' => false, 'error' => 'google_api_error', 'message' => $err['message']], 422);
        }

        $updatedRange = $response->json('updates.updatedRange', '');
        $result = ['success' => true, 'updated_range' => $updatedRange, 'message' => 'Fila agregada correctamente.'];
        $this->logger->log('donna_business_sheets_append_row', $ctx['clientId'], ['values' => $values], $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }

    public function updateRow(Request $request)
    {
        $request->validate(['row_number' => 'required|integer', 'values' => 'required|array']);
        $ctx = $this->resolveContext($request);
        if (isset($ctx['error'])) return response()->json($ctx['error'], 422);

        $rowNumber = (int) $request->input('row_number');
        $values    = $request->input('values');
        $sheet     = $request->input('sheet', $ctx['sheet_name']);
        $colCount  = count($values);
        $endCol    = chr(ord('A') + $colCount - 1);
        $range     = "{$sheet}!A{$rowNumber}:{$endCol}{$rowNumber}";

        $start    = microtime(true);
        $response = $this->http($ctx['token'])->put(
            self::BASE . "/{$ctx['spreadsheet_id']}/values/{$range}?valueInputOption=USER_ENTERED",
            ['values' => [array_values($values)]]
        );
        $ms = (int) ((microtime(true) - $start) * 1000);

        if (!$response->successful()) {
            $err = $this->googleError($response);
            return response()->json(['success' => false, 'error' => 'google_api_error', 'message' => $err['message']], 422);
        }

        $result = ['success' => true, 'row_number' => $rowNumber, 'message' => 'Fila actualizada correctamente.'];
        $this->logger->log('donna_business_sheets_update_row', $ctx['clientId'], ['row_number' => $rowNumber], $result, $ctx['serviceId'], $ctx['channelId'], $ms);
        return response()->json($result);
    }
}
