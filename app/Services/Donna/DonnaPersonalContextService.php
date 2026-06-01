<?php

namespace App\Services\Donna;

use App\Models\DonnaAgentConfig;
use App\Models\DonnaIntegration;
use App\Models\DonnaSubscription;
use App\Models\DonnaChannel;

class DonnaPersonalContextService
{
    public function build(DonnaChannel $channel, DonnaSubscription $sub): array
    {
        $clientId = $channel->client_id;

        $config = DonnaAgentConfig::where('client_id', $clientId)
            ->where('service_type', 'personal')
            ->where('is_active', true)
            ->first();

        $integ = DonnaIntegration::where('client_id', $clientId)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->first();

        $agentName   = $config?->agent_name ?? 'Donna';
        $timezone    = $config?->timezone ?? config('services.donna.google_default_timezone', 'America/Guayaquil');
        $language    = $config?->language ?? 'es';
        $tone        = $config?->tone ?? 'profesional, amable y directa';
        $calendarId  = $config?->calendar_id ?? 'primary';
        $spreadsheetId   = $config?->spreadsheet_id;
        $spreadsheetName = $config?->spreadsheet_name ?? 'Tareas';

        $googleEmail = $integ?->metadata_json['email'] ?? null;

        $wh = $config?->working_hours_json ?? [];
        $workingHours = ($wh['start'] ?? '09:00') . ' a ' . ($wh['end'] ?? '20:00');
        $lunchBreak   = $wh['lunch'] ?? '13:00';

        $systemMessage = $this->buildSystemMessage(
            $agentName, $timezone, $googleEmail,
            $config?->main_prompt, $config?->personal_context,
            $spreadsheetId !== null, $workingHours, $lunchBreak
        );

        $googleConnected     = $integ !== null;
        $calendarEnabled     = $googleConnected;
        $sheetsEnabled       = $googleConnected && $spreadsheetId !== null;

        $daysRemaining = $sub->expires_at
            ? max(0, (int) now()->diffInDays($sub->expires_at, false))
            : null;

        return [
            'success' => true,
            'allowed' => true,
            'reason'  => 'allowed',
            'client'  => [
                'id'    => $clientId,
                'name'  => $channel->cliente?->nombrecli ?? 'Cliente',
                'email' => $channel->cliente?->email ?? null,
            ],
            'service' => [
                'id'            => $sub->id,
                'type'          => 'personal',
                'code'          => 'donna_personal',
                'status'        => $sub->status,
                'expires_at'    => $sub->expires_at?->toDateString(),
                'days_remaining'=> $daysRemaining,
            ],
            'channel' => [
                'id'               => $channel->id,
                'type'             => 'telegram',
                'telegram_chat_id' => $channel->owner_identifier,
                'status'           => $channel->status,
            ],
            'agent' => [
                'name'               => $agentName,
                'timezone'           => $timezone,
                'language'           => $language,
                'tone'               => $tone,
                'prompt'             => $config?->main_prompt,
                'system_message'     => $systemMessage,
                'max_actions_per_run'=> 6,
            ],
            'memory' => [
                'session_key' => "donna_personal:{$clientId}:{$channel->owner_identifier}",
                'window_size' => 20,
            ],
            'google' => [
                'connected'       => $googleConnected,
                'calendar_enabled'=> $calendarEnabled,
                'sheets_enabled'  => $sheetsEnabled,
                'calendar_id'     => $calendarId,
                'spreadsheet_id'  => $spreadsheetId,
                'spreadsheet_name'=> $spreadsheetName,
                'tokens_exposed'  => false,
            ],
            'tools' => [
                'calendar' => [
                    'list_events'  => $calendarEnabled,
                    'freebusy'     => $calendarEnabled,
                    'create_event' => $calendarEnabled,
                    'update_event' => $calendarEnabled,
                    'delete_event' => $calendarEnabled,
                ],
                'sheets' => [
                    'get_tasks'    => $sheetsEnabled,
                    'create_task'  => $sheetsEnabled,
                    'update_task'  => $sheetsEnabled,
                    'complete_task'=> $sheetsEnabled,
                ],
            ],
        ];
    }

    public function getSystemMessagePreview(?DonnaAgentConfig $config, ?string $googleEmail): string
    {
        $agentName    = $config?->agent_name ?? 'Donna';
        $timezone     = $config?->timezone ?? config('services.donna.google_default_timezone', 'America/Guayaquil');
        $sheetsEnabled = $config?->spreadsheet_id !== null;

        $wh = $config?->working_hours_json ?? [];
        $workingHours = ($wh['start'] ?? '09:00') . ' a ' . ($wh['end'] ?? '20:00');
        $lunchBreak   = $wh['lunch'] ?? '13:00';

        return $this->buildSystemMessage(
            $agentName, $timezone, $googleEmail,
            $config?->main_prompt, $config?->personal_context,
            $sheetsEnabled, $workingHours, $lunchBreak
        );
    }

    private function buildSystemMessage(
        string $agentName,
        string $timezone,
        ?string $googleEmail,
        ?string $customPrompt,
        ?string $personalContext,
        bool $sheetsEnabled,
        string $workingHours = '09:00 a 20:00',
        string $lunchBreak = '13:00'
    ): string {
        if ($customPrompt) {
            return str_replace(
                ['{{now}}', '{{timezone}}', '{{agent_name}}', '{{working_hours}}', '{{lunch_break}}'],
                [now()->setTimezone($timezone)->format('Y-m-d H:i'), $timezone, $agentName, $workingHours, $lunchBreak],
                $customPrompt
            );
        }

        $toolsList = "- donna_calendar_list_events\n- donna_calendar_freebusy\n- donna_calendar_create_event\n- donna_calendar_update_event\n- donna_calendar_delete_event";
        if ($sheetsEnabled) {
            $toolsList .= "\n- donna_sheets_get_tasks\n- donna_sheets_create_task\n- donna_sheets_update_task\n- donna_sheets_complete_task";
        }

        $context = $personalContext ? "\nCONTEXTO PERSONAL:\n{$personalContext}\n" : '';

        return "Eres {$agentName}, una secretaria personal inteligente.\n\nFecha actual: " . now()->setTimezone($timezone)->format('Y-m-d H:i') . "\nZona horaria: {$timezone}\n" . ($googleEmail ? "Cuenta Google: {$googleEmail}\n" : '') . "{$context}\nFUNCIONES:\n- Gestionar agenda desde Google Calendar.\n- Gestionar tareas desde Google Sheets.\n- Organizar el día del usuario.\n- Responder por Telegram de forma breve y clara.\n\nREGLAS:\n- Usa herramientas solo cuando sea necesario.\n- Consulta disponibilidad antes de agendar.\n- Horario permitido: {$workingHours}.\n- Nunca usar {$lunchBreak} (almuerzo).\n- No solapar eventos existentes.\n- No confirmes eventos si no fueron creados correctamente.\n- Si no tienes datos suficientes, pregunta.\n- Máximo 6 acciones por ejecución.\n- Sé breve y directo en las respuestas.\n\nHERRAMIENTAS DISPONIBLES:\n{$toolsList}";
    }
}
