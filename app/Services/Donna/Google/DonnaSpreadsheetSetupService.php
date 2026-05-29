<?php

namespace App\Services\Donna\Google;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DonnaSpreadsheetSetupService
{
    private const BASE = 'https://sheets.googleapis.com/v4/spreadsheets';

    private function http(string $token)
    {
        return Http::withToken($token)->acceptJson();
    }

    /**
     * Crea el spreadsheet "Lista de Tareas" con la hoja "Tareas" formateada
     * y lista para usarse con DonnaSheetsTaskService.
     */
    public function createTasksSpreadsheet(string $token): array
    {
        // 1. Crear el spreadsheet con la hoja "Tareas"
        $create = $this->http($token)->post(self::BASE, [
            'properties' => ['title' => 'Lista de Tareas'],
            'sheets' => [
                ['properties' => ['title' => 'Tareas', 'index' => 0, 'sheetId' => 0]],
            ],
        ]);

        if (!$create->successful()) {
            Log::warning('DonnaSpreadsheetSetup: error al crear spreadsheet', ['error' => $create->json()]);
            return ['success' => false, 'error' => $create->json()];
        }

        $spreadsheetId = $create->json('spreadsheetId');
        $sheetId       = $create->json('sheets.0.properties.sheetId', 0);

        // 2. Escribir fila de encabezado
        $this->http($token)->put(
            self::BASE . "/{$spreadsheetId}/values/Tareas!A1:G1?valueInputOption=RAW",
            ['values' => [['ID', '✓', 'Fecha', 'Tarea', 'Horas al día', 'Creado por', 'Actualizado en']]]
        );

        // 3. Formato, validaciones y filtros en una sola llamada batchUpdate
        $this->http($token)->post(self::BASE . "/{$spreadsheetId}:batchUpdate", [
            'requests' => [
                // Encabezado: fondo verde oscuro + texto blanco negrita centrado
                [
                    'repeatCell' => [
                        'range' => ['sheetId' => $sheetId, 'startRowIndex' => 0, 'endRowIndex' => 1],
                        'cell'  => [
                            'userEnteredFormat' => [
                                'backgroundColor'    => ['red' => 0.118, 'green' => 0.420, 'blue' => 0.235],
                                'textFormat'         => [
                                    'bold'            => true,
                                    'foregroundColor' => ['red' => 1.0, 'green' => 1.0, 'blue' => 1.0],
                                    'fontSize'        => 10,
                                ],
                                'horizontalAlignment' => 'CENTER',
                                'verticalAlignment'   => 'MIDDLE',
                            ],
                        ],
                        'fields' => 'userEnteredFormat(backgroundColor,textFormat,horizontalAlignment,verticalAlignment)',
                    ],
                ],
                // Congelar fila 1
                [
                    'updateSheetProperties' => [
                        'properties' => [
                            'sheetId'         => $sheetId,
                            'gridProperties'  => ['frozenRowCount' => 1],
                        ],
                        'fields' => 'gridProperties.frozenRowCount',
                    ],
                ],
                // Checkbox en columna B (✓/completed) desde fila 2 hasta 1000
                [
                    'setDataValidation' => [
                        'range' => [
                            'sheetId'          => $sheetId,
                            'startRowIndex'    => 1,
                            'endRowIndex'      => 1000,
                            'startColumnIndex' => 1,
                            'endColumnIndex'   => 2,
                        ],
                        'rule' => [
                            'condition' => ['type' => 'BOOLEAN'],
                            'strict'    => true,
                            'showCustomUi' => true,
                        ],
                    ],
                ],
                // Formato condicional: tachado en toda la fila cuando ✓ = TRUE
                [
                    'addConditionalFormatRule' => [
                        'rule' => [
                            'ranges' => [
                                ['sheetId' => $sheetId, 'startRowIndex' => 1, 'endRowIndex' => 1000],
                            ],
                            'booleanRule' => [
                                'condition' => [
                                    'type'   => 'CUSTOM_FORMULA',
                                    'values' => [['userEnteredValue' => '=$B2=TRUE']],
                                ],
                                'format' => [
                                    'textFormat'      => ['strikethrough' => true],
                                    'backgroundColor' => ['red' => 0.949, 'green' => 0.949, 'blue' => 0.949],
                                ],
                            ],
                        ],
                        'index' => 0,
                    ],
                ],
                // Anchos de columna
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 0, 'endIndex' => 1], 'properties' => ['pixelSize' => 80],  'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 1, 'endIndex' => 2], 'properties' => ['pixelSize' => 42],  'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 2, 'endIndex' => 3], 'properties' => ['pixelSize' => 80],  'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 3, 'endIndex' => 4], 'properties' => ['pixelSize' => 310], 'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 4, 'endIndex' => 5], 'properties' => ['pixelSize' => 95],  'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 5, 'endIndex' => 6], 'properties' => ['pixelSize' => 100], 'fields' => 'pixelSize']],
                ['updateDimensionProperties' => ['range' => ['sheetId' => $sheetId, 'dimension' => 'COLUMNS', 'startIndex' => 6, 'endIndex' => 7], 'properties' => ['pixelSize' => 150], 'fields' => 'pixelSize']],
                // Activar filtros en la fila de encabezado
                [
                    'setBasicFilter' => [
                        'filter' => [
                            'range' => [
                                'sheetId'          => $sheetId,
                                'startRowIndex'    => 0,
                                'endRowIndex'      => 1,
                                'startColumnIndex' => 0,
                                'endColumnIndex'   => 7,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return [
            'success'         => true,
            'spreadsheet_id'  => $spreadsheetId,
            'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/{$spreadsheetId}",
        ];
    }
}
