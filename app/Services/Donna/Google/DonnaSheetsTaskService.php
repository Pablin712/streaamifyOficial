<?php

namespace App\Services\Donna\Google;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DonnaSheetsTaskService
{
    private const BASE = 'https://sheets.googleapis.com/v4/spreadsheets';
    private const SHEET = 'Tareas';
    // Columns: ID | ✓ | Fecha | Tarea | Horas al día | Creado por | Actualizado en
    private const COLS = ['id', 'completed', 'date', 'title', 'hours_per_day', 'created_by', 'updated_at'];

    private function http(string $token)
    {
        return Http::withToken($token)->acceptJson();
    }

    public function getTasks(string $token, string $spreadsheetId, bool $includeCompleted = false): array
    {
        $response = $this->http($token)->get(
            self::BASE . "/{$spreadsheetId}/values/" . self::SHEET
        );

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $response->json()];
        }

        $rows   = $response->json('values', []);
        $header = array_shift($rows) ?? [];

        $tasks = [];
        foreach ($rows as $i => $row) {
            $task = $this->mapRow($row, $i + 2); // +2 because row 1 = header, rows start at 2
            if (!$includeCompleted && $task['completed']) continue;
            $tasks[] = $task;
        }

        return ['success' => true, 'spreadsheet_id' => $spreadsheetId, 'sheet_name' => self::SHEET, 'tasks' => $tasks];
    }

    public function createTask(string $token, string $spreadsheetId, array $data): array
    {
        $id      = 'T-' . strtoupper(Str::random(5));
        $now     = now()->toDateTimeString();
        $values  = [[
            $id,
            'FALSE',
            $data['due_date'] ?? '',
            $data['title'],
            $data['hours_per_day'] ?? '',
            'Donna',
            $now,
        ]];

        $response = $this->http($token)->post(
            self::BASE . "/{$spreadsheetId}/values/" . self::SHEET . ":append?valueInputOption=USER_ENTERED",
            ['values' => $values]
        );

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $response->json()];
        }

        $updatedRange = $response->json('updates.updatedRange', '');
        $rowNum       = (int) filter_var($updatedRange, FILTER_SANITIZE_NUMBER_INT);

        return [
            'success' => true,
            'task'    => [
                'id'          => $id,
                'completed'   => false,
                'date'        => $data['due_date'] ?? null,
                'title'       => $data['title'],
                'hours_per_day' => $data['hours_per_day'] ?? null,
                'row_number'  => $rowNum,
            ],
            'message' => 'Tarea creada correctamente.',
        ];
    }

    public function updateTask(string $token, string $spreadsheetId, int $rowNumber, array $data): array
    {
        $now    = now()->toDateTimeString();
        $range  = self::SHEET . "!A{$rowNumber}:G{$rowNumber}";

        // Get current row first
        $current = $this->http($token)->get(self::BASE . "/{$spreadsheetId}/values/{$range}");
        $row = $current->json('values.0', array_fill(0, 7, ''));

        if (isset($data['title']))       $row[3] = $data['title'];
        if (isset($data['due_date']))    $row[2] = $data['due_date'];
        if (isset($data['hours_per_day'])) $row[4] = $data['hours_per_day'];
        if (isset($data['completed']))   $row[1] = $data['completed'] ? 'TRUE' : 'FALSE';
        $row[6] = $now;

        $response = $this->http($token)->put(
            self::BASE . "/{$spreadsheetId}/values/{$range}?valueInputOption=USER_ENTERED",
            ['values' => [$row]]
        );

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $response->json()];
        }

        return [
            'success' => true,
            'task'    => array_merge($this->mapRow($row, $rowNumber), ['row_number' => $rowNumber]),
            'message' => 'Tarea actualizada correctamente.',
        ];
    }

    public function completeTask(string $token, string $spreadsheetId, int $rowNumber): array
    {
        $range    = self::SHEET . "!B{$rowNumber}:B{$rowNumber}";
        $response = $this->http($token)->put(
            self::BASE . "/{$spreadsheetId}/values/{$range}?valueInputOption=USER_ENTERED",
            ['values' => [['TRUE']]]
        );

        if (!$response->successful()) {
            return ['success' => false, 'google_error' => $response->json()];
        }

        return ['success' => true, 'completed' => true, 'message' => 'Tarea marcada como completada.'];
    }

    private function mapRow(array $row, int $rowNumber): array
    {
        return [
            'id'            => $row[0] ?? null,
            'completed'     => strtolower($row[1] ?? 'false') === 'true',
            'date'          => $row[2] ?? null,
            'title'         => $row[3] ?? '',
            'hours_per_day' => isset($row[4]) && $row[4] !== '' ? (float) $row[4] : null,
            'row_number'    => $rowNumber,
        ];
    }
}
