<?php

namespace App\Http\Controllers\Api\Donna\Business\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaConversation;
use App\Models\DonnaMessage;
use App\Services\Donna\DonnaToolLogger;
use Illuminate\Http\Request;

class DonnaBusinessCustomerContextController extends Controller
{
    public function __construct(private DonnaToolLogger $logger) {}

    /**
     * POST /api/donna/business/tools/customer/context-search
     *
     * Busca datos históricos del cliente final en esta conversación:
     * mensajes pasados, datos de lead guardados, preferencias detectadas.
     * Útil cuando el cliente dice "lo de antes", "mi pedido", "mi cita", etc.
     */
    public function search(Request $request)
    {
        $clientId  = (int) $request->input('client_id');
        $serviceId = (int) $request->input('service_id');
        $channelId = (int) $request->input('channel_id');
        $convId    = (int) $request->input('conversation_id');
        $extChatId = $request->input('external_chat_id');
        $senderNum = $request->input('sender_identifier');
        $query     = $request->input('query', '');
        $limit     = (int) $request->input('limit', 10);
        $start     = microtime(true);

        // 1. Obtener la conversación y su metadata (lead data)
        $conversation = DonnaConversation::where('id', $convId)
            ->where('client_id', $clientId)
            ->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
        }

        $leadData = $conversation->metadata_json['lead'] ?? null;

        // 2. Buscar otras conversaciones del mismo número (historial multi-sesión)
        $previousConvIds = DonnaConversation::where('client_id', $clientId)
            ->where('sender_identifier', $senderNum ?: $conversation->sender_identifier)
            ->where('id', '!=', $convId)
            ->orderByDesc('last_message_at')
            ->limit(3)
            ->pluck('id');

        // 3. Mensajes relevantes de esta conversación
        $words    = array_filter(explode(' ', strtolower(trim($query))), fn ($w) => strlen($w) > 2);
        $messages = DonnaMessage::where('conversation_id', $convId)
            ->whereIn('sender_type', ['final_customer', 'ai_agent'])
            ->orderBy('created_at')
            ->get();

        if (!empty($words)) {
            $messages = $messages->filter(function ($m) use ($words) {
                $text = strtolower($m->transcription_text ?? $m->content_text ?? '');
                foreach ($words as $word) {
                    if (str_contains($text, $word)) return true;
                }
                return false;
            });
        }

        $currentHistory = $messages->take($limit)->map(fn ($m) => [
            'role'    => $m->sender_type === 'final_customer' ? 'user' : 'assistant',
            'content' => $m->transcription_text ?? $m->content_text ?? '',
            'date'    => $m->created_at->toDateTimeString(),
        ])->values()->toArray();

        // 4. Mensajes relevantes de conversaciones anteriores (contexto histórico)
        $previousHistory = [];
        if ($previousConvIds->isNotEmpty()) {
            $prevMessages = DonnaMessage::whereIn('conversation_id', $previousConvIds)
                ->whereIn('sender_type', ['final_customer', 'ai_agent'])
                ->when(!empty($words), function ($q) use ($words) {
                    $q->where(function ($inner) use ($words) {
                        foreach ($words as $word) {
                            $inner->orWhere('content_text', 'LIKE', "%{$word}%")
                                  ->orWhere('transcription_text', 'LIKE', "%{$word}%");
                        }
                    });
                })
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn ($m) => [
                    'role'    => $m->sender_type === 'final_customer' ? 'user' : 'assistant',
                    'content' => $m->transcription_text ?? $m->content_text ?? '',
                    'date'    => $m->created_at->toDateTimeString(),
                ])->toArray();

            $previousHistory = array_reverse($prevMessages);
        }

        $ms = (int) ((microtime(true) - $start) * 1000);

        $result = [
            'success'          => true,
            'query'            => $query,
            'sender_name'      => $conversation->sender_name,
            'sender_number'    => $senderNum ?: $conversation->sender_identifier,
            'lead_data'        => $leadData,
            'memory_summary'   => $conversation->memory_summary,
            'current_history'  => $currentHistory,
            'previous_history' => $previousHistory,
            'total_found'      => count($currentHistory) + count($previousHistory),
        ];

        $this->logger->log('donna_business_customer_context_search', $clientId,
            ['query' => $query, 'conversation_id' => $convId], $result, $serviceId, $channelId, $ms);

        return response()->json($result);
    }
}
