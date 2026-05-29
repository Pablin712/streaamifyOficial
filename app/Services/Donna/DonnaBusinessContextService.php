<?php

namespace App\Services\Donna;

use App\Models\DonnaAgentConfig;
use App\Models\DonnaChannel;
use App\Models\DonnaConversation;
use App\Models\DonnaIntegration;
use App\Models\DonnaMessage;
use App\Models\DonnaSubscription;

class DonnaBusinessContextService
{
    public function build(
        DonnaChannel $channel,
        DonnaSubscription $sub,
        DonnaConversation $conversation,
        ?DonnaMessage $message = null
    ): array {
        $clientId = $channel->client_id;

        $config = DonnaAgentConfig::where('client_id', $clientId)
            ->where('service_type', 'business')
            ->where('is_active', true)
            ->first();

        $integ = DonnaIntegration::where('client_id', $clientId)
            ->where('integration_type', 'google')
            ->where('status', 'active')
            ->first();

        $googleConnected  = $integ !== null;
        $calendarEnabled  = $googleConnected && ($config?->calendar_enabled ?? false);
        $sheetsEnabled    = $googleConnected && ($config?->sheets_enabled ?? false);
        $knowledgeEnabled = $config?->knowledge_enabled ?? false;

        $agentName    = $config?->agent_name ?? 'Donna';
        $businessName = $config?->business_name ?? $channel->cliente?->nombrecli ?? 'el negocio';
        $timezone     = $config?->timezone ?? config('services.donna.google_default_timezone', 'America/Guayaquil');
        $language     = $config?->language ?? 'es';
        $tone         = $config?->tone ?? 'profesional, amable y directa';
        $maxTools     = $config?->max_tool_calls ?? 8;

        $systemMessage = $this->buildSystemMessage(
            $agentName, $businessName, $timezone, $language, $tone,
            $config, $calendarEnabled, $sheetsEnabled, $knowledgeEnabled
        );

        $daysRemaining = $sub->expires_at
            ? max(0, (int) now()->diffInDays($sub->expires_at, false))
            : null;

        $recentMessages = $conversation->recentMessages(20);

        return [
            'success' => true,
            'allowed' => true,
            'client'  => [
                'id'            => $clientId,
                'name'          => $channel->cliente?->nombrecli ?? 'Cliente',
                'business_name' => $businessName,
            ],
            'service' => [
                'id'             => $sub->id,
                'type'           => 'business',
                'status'         => $sub->status,
                'expires_at'     => $sub->expires_at?->toDateString(),
                'days_remaining' => $daysRemaining,
            ],
            'channel' => [
                'id'            => $channel->id,
                'type'          => 'whatsapp',
                'provider'      => $channel->provider,
                'instance_name' => $channel->instance_name,
                'server_url'    => $channel->api_base_url,
                // Clave de Evo API desencriptada para que n8n pueda enviar mensajes
                'evo_api_key'   => $channel->getApiKey(),
            ],
            'conversation' => [
                'id'               => $conversation->id,
                'external_chat_id' => $conversation->external_chat_id,
                'sender_name'      => $conversation->sender_name,
                'status'           => $conversation->status,
                'memory_summary'   => $conversation->memory_summary,
            ],
            'message' => $message ? [
                'id'           => $message->id,
                'type'         => $message->message_type,
                'content_text' => $message->effective_text,
            ] : null,
            'agent' => [
                'name'           => $agentName,
                'timezone'       => $timezone,
                'language'       => $language,
                'tone'           => $tone,
                'system_message' => $systemMessage,
                'max_tool_calls' => $maxTools,
            ],
            'memory' => [
                'session_key'     => "donna-business:{$clientId}:{$conversation->id}",
                'recent_messages' => $recentMessages,
                'window_size'     => 20,
            ],
            'integrations' => [
                'google' => [
                    'connected'         => $googleConnected,
                    'calendar_connected' => $calendarEnabled,
                    'sheets_connected'   => $sheetsEnabled,
                ],
            ],
            'tools' => [
                'knowledge_search' => $knowledgeEnabled,
                'memory_search'    => true,
                'calendar' => [
                    'enabled'      => $calendarEnabled,
                    'list_events'  => $calendarEnabled,
                    'freebusy'     => $calendarEnabled,
                    'create_event' => $calendarEnabled,
                    'update_event' => $calendarEnabled,
                    'delete_event' => $calendarEnabled,
                ],
                'sheets' => [
                    'enabled'    => $sheetsEnabled,
                    'get_rows'   => $sheetsEnabled,
                    'append_row' => $sheetsEnabled,
                    'update_row' => $sheetsEnabled,
                ],
                'business_data' => [
                    'enabled'     => true,
                    'query'       => true,
                    'upsert_lead' => true,
                ],
            ],
            'response_rules' => [
                'human_takeover' => $conversation->isHumanTakeover(),
                'max_tool_calls' => $maxTools,
            ],
        ];
    }

    private function buildSystemMessage(
        string $agentName,
        string $businessName,
        string $timezone,
        string $language,
        string $tone,
        ?DonnaAgentConfig $config,
        bool $calendarEnabled,
        bool $sheetsEnabled,
        bool $knowledgeEnabled
    ): string {
        if ($config?->main_prompt) {
            return str_replace(
                ['{{now}}', '{{timezone}}', '{{agent_name}}', '{{business_name}}'],
                [now()->setTimezone($timezone)->format('Y-m-d H:i'), $timezone, $agentName, $businessName],
                $config->main_prompt
            );
        }

        $now = now()->setTimezone($timezone)->format('Y-m-d H:i');

        $toolsSection = "HERRAMIENTAS DISPONIBLES:\n";
        $toolsSection .= "- donna_business_data_query: consultar tablas internas del negocio\n";
        $toolsSection .= "- donna_business_upsert_lead: registrar o actualizar lead/cliente final\n";
        $toolsSection .= "- donna_business_memory_search: consultar historial previo de este chat\n";

        if ($knowledgeEnabled) {
            $toolsSection .= "- donna_business_knowledge_search: buscar en la base de conocimientos del negocio\n";
        }
        if ($calendarEnabled) {
            $toolsSection .= "- donna_business_calendar_freebusy: consultar disponibilidad antes de agendar\n";
            $toolsSection .= "- donna_business_calendar_list_events: listar eventos del calendario\n";
            $toolsSection .= "- donna_business_calendar_create_event: crear evento (solo tras verificar disponibilidad)\n";
            $toolsSection .= "- donna_business_calendar_update_event: actualizar evento existente\n";
            $toolsSection .= "- donna_business_calendar_delete_event: eliminar evento\n";
        }
        if ($sheetsEnabled) {
            $toolsSection .= "- donna_business_sheets_get_rows: leer filas de Google Sheets\n";
            $toolsSection .= "- donna_business_sheets_append_row: agregar fila (leads, pedidos, citas)\n";
            $toolsSection .= "- donna_business_sheets_update_row: actualizar fila existente\n";
        }

        $rulesSection = "REGLAS:\n";
        $rulesSection .= "- Responde de forma breve, clara y en tono: {$tone}.\n";
        $rulesSection .= "- No inventes precios, horarios ni información que no tengas.\n";
        if ($knowledgeEnabled) {
            $rulesSection .= "- Ante preguntas sobre productos, precios o servicios usa donna_business_knowledge_search.\n";
        }
        if ($calendarEnabled) {
            $rulesSection .= "- Antes de agendar, usa donna_business_calendar_freebusy para verificar disponibilidad.\n";
            $rulesSection .= "- Confirma la cita solo si create_event devuelve success=true.\n";
        }
        if (!$calendarEnabled) {
            $rulesSection .= "- Google Calendar NO está habilitado. Si piden cita, toma los datos y ofrece derivar a un asesor.\n";
        }
        $rulesSection .= "- Si detectas reclamo, caso delicado o usuario muy molesto, deriva a humano.\n";
        $rulesSection .= "- No digas que eres ChatGPT, DeepSeek ni ningún otro modelo.\n";
        $rulesSection .= "- Máximo {$config?->max_tool_calls ?? 8} llamadas a herramientas por ejecución.\n";

        $contextSection = '';
        if ($config?->business_description) {
            $contextSection .= "\nDESCRIPCIÓN DEL NEGOCIO:\n{$config->business_description}\n";
        }
        if ($config?->business_context) {
            $contextSection .= "\nCONTEXTO ADICIONAL:\n{$config->business_context}\n";
        }
        if ($config?->sales_rules) {
            $contextSection .= "\nREGLAS DE VENTAS:\n{$config->sales_rules}\n";
        }
        if ($config?->support_rules) {
            $contextSection .= "\nREGLAS DE SOPORTE:\n{$config->support_rules}\n";
        }

        return "Eres {$agentName}, asistente virtual de {$businessName}. " .
            "Atiendes a clientes finales por WhatsApp en nombre del negocio.\n\n" .
            "Fecha actual: {$now}\n" .
            "Zona horaria: {$timezone}\n" .
            "Idioma: {$language}\n" .
            "{$contextSection}\n" .
            "{$rulesSection}\n" .
            "{$toolsSection}";
    }
}
