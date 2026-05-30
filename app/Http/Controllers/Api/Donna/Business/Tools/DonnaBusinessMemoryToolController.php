<?php

namespace App\Http\Controllers\Api\Donna\Business\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaConversation;
use App\Models\DonnaMessage;
use App\Services\Donna\DonnaToolLogger;
use Illuminate\Http\Request;

class DonnaBusinessMemoryToolController extends Controller
{
    public function __construct(private DonnaToolLogger $logger) {}

    /**
     * Recupera mensajes históricos de la conversación filtrados por query.
     * Búsqueda simple por palabras clave sobre el contenido almacenado.
     */
    public function search(Request $request)
    {
        $clientId  = (int) $request->input('client_id');
        $serviceId = (int) $request->input('service_id');
        $channelId = (int) $request->input('channel_id');
        $convId    = (int) $request->input('conversation_id');
        $query     = $request->input('query', '');
        $limit     = (int) $request->input('limit', 10);

        $conversation = DonnaConversation::where('id', $convId)
            ->where('client_id', $clientId)
            ->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
        }

        $words    = array_filter(explode(' ', strtolower(trim($query))), fn ($w) => strlen($w) > 2);
        $start    = microtime(true);

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

        $results = $messages->take($limit)->map(fn ($m) => [
            'role'       => $m->sender_type === 'final_customer' ? 'user' : 'assistant',
            'content'    => $m->transcription_text ?? $m->content_text ?? '',
            'created_at' => $m->created_at->toDateTimeString(),
        ])->values()->toArray();

        $ms = (int) ((microtime(true) - $start) * 1000);

        $result = [
            'success'         => true,
            'query'           => $query,
            'results_count'   => count($results),
            'messages'        => $results,
            'memory_summary'  => $conversation->memory_summary,
        ];

        $this->logger->log('donna_business_memory_search', $clientId,
            ['query' => $query], $result, $serviceId, $channelId, $ms);

        return response()->json($result);
    }
}
