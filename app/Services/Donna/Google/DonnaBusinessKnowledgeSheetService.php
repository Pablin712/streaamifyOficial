<?php

namespace App\Services\Donna\Google;

use App\Models\DonnaAgentConfig;
use App\Models\DonnaKnowledgeItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DonnaBusinessKnowledgeSheetService
{
    private const BASE = 'https://sheets.googleapis.com/v4/spreadsheets';
    private const SHEET_NAME = 'Conocimientos';
    private const HEADERS = ['ID', 'Tipo', 'Título', 'Contenido', 'URL Fuente', 'Activo', 'Creado'];

    private function http(string $token)
    {
        return Http::withToken($token)->acceptJson();
    }

    public function createAndSync(string $token, DonnaAgentConfig $config, string $businessName): array
    {
        $title = 'Base de Conocimientos' . ($businessName ? " — {$businessName}" : '');

        $create = $this->http($token)->post(self::BASE, [
            'properties' => ['title' => $title],
            'sheets' => [
                ['properties' => ['title' => self::SHEET_NAME, 'index' => 0, 'sheetId' => 0]],
            ],
        ]);

        if (!$create->successful()) {
            Log::warning('DonnaBusinessKnowledgeSheet: error al crear spreadsheet', ['error' => $create->json()]);
            return ['success' => false, 'error' => $create->json()];
        }

        $spreadsheetId = $create->json('spreadsheetId');
        $sheetId       = $create->json('sheets.0.properties.sheetId', 0);

        // Encabezado
        $this->http($token)->put(
            self::BASE . "/{$spreadsheetId}/values/" . self::SHEET_NAME . "!A1:G1?valueInputOption=RAW",
            ['values' => [self::HEADERS]]
        );

        // Formato del encabezado
        $this->http($token)->post(self::BASE . "/{$spreadsheetId}:batchUpdate", [
            'requests' => [
                [
                    'repeatCell' => [
                        'range' => ['sheetId' => $sheetId, 'startRowIndex' => 0, 'endRowIndex' => 1],
                        'cell'  => [
                            'userEnteredFormat' => [
                                'backgroundColor'     => ['red' => 0.882, 'green' => 0.698, 'blue' => 0.0],
                                'textFormat'          => ['bold' => true, 'fontSize' => 10, 'foregroundColor' => ['red' => 0.11, 'green' => 0.09, 'blue' => 0.0]],
                                'horizontalAlignment' => 'CENTER',
                                'verticalAlignment'   => 'MIDDLE',
                            ],
                        ],
                        'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment,verticalAlignment)',
                    ],
                ],
                [
                    'updateSheetProperties' => [
                        'properties' => ['sheetId' => $sheetId, 'gridProperties' => ['frozenRowCount' => 1]],
                        'fields'     => 'gridProperties.frozenRowCount',
                    ],
                ],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 0, 'endIndex' => 1], 'properties' => ['pixelSize' => 60],  'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 1, 'endIndex' => 2], 'properties' => ['pixelSize' => 90],  'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 2, 'endIndex' => 3], 'properties' => ['pixelSize' => 200], 'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 3, 'endIndex' => 4], 'properties' => ['pixelSize' => 400], 'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 4, 'endIndex' => 5], 'properties' => ['pixelSize' => 200], 'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 5, 'endIndex' => 6], 'properties' => ['pixelSize' => 60],  'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 6, 'endIndex' => 7], 'properties' => ['pixelSize' => 130], 'fields' => 'pixelSize']],
            ],
        ]);

        // Guardar spreadsheet_id antes de sincronizar
        $config->update(['spreadsheet_id' => $spreadsheetId, 'spreadsheet_name' => self::SHEET_NAME]);

        // Importar items existentes
        $items = DonnaKnowledgeItem::where('client_id', $config->client_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $rowsWritten = 0;
        if ($items->isNotEmpty()) {
            $rows    = $items->map(fn ($item) => $this->itemToRow($item))->all();
            $written = $this->http($token)->post(
                self::BASE . "/{$spreadsheetId}/values/" . self::SHEET_NAME . "!A2:G?valueInputOption=USER_ENTERED",
                ['values' => $rows]
            );
            if (!$written->successful()) {
                Log::warning('DonnaBusinessKnowledgeSheet: error al escribir filas', [
                    'spreadsheet_id' => $spreadsheetId,
                    'status'         => $written->status(),
                    'body'           => $written->json(),
                ]);
            } else {
                $rowsWritten = count($rows);
            }
        }

        return [
            'success'         => true,
            'spreadsheet_id'  => $spreadsheetId,
            'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}",
            'rows_written'    => $rowsWritten,
        ];
    }

    public function appendItem(string $token, string $spreadsheetId, DonnaKnowledgeItem $item): bool
    {
        $response = $this->http($token)->post(
            self::BASE . "/{$spreadsheetId}/values/" . self::SHEET_NAME . "!A:G:append?valueInputOption=USER_ENTERED",
            ['values' => [$this->itemToRow($item)]]
        );
        return $response->successful();
    }

    public function syncAllItems(string $token, string $spreadsheetId, int $clientId): bool
    {
        // Borra desde fila 2 y reescribe todo
        $this->http($token)->post(self::BASE . "/{$spreadsheetId}/values:batchClear", [
            'ranges' => [self::SHEET_NAME . '!A2:G'],
        ]);

        $items = DonnaKnowledgeItem::where('client_id', $clientId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            return true;
        }

        $rows     = $items->map(fn ($item) => $this->itemToRow($item))->all();
        $response = $this->http($token)->post(
            self::BASE . "/{$spreadsheetId}/values/" . self::SHEET_NAME . "!A2:G?valueInputOption=USER_ENTERED",
            ['values' => $rows]
        );
        return $response->successful();
    }

    private function itemToRow(DonnaKnowledgeItem $item): array
    {
        return [
            $item->id,
            ucfirst($item->type),
            $item->title,
            $item->content_text,
            $item->source_url ?? '',
            $item->is_active ? 'Sí' : 'No',
            $item->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }
}
