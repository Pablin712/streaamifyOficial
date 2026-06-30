<?php

namespace App\Http\Controllers\Api\Donna\Business\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaConversation;
use App\Models\DonnaKnowledgeItem;
use App\Services\Donna\DonnaToolLogger;
use Illuminate\Http\Request;

class DonnaBusinessDataToolController extends Controller
{
    public function __construct(private DonnaToolLogger $logger) {}

    /**
     * Consulta datos internos del negocio: knowledge items por tipo específico.
     * En el futuro puede conectarse a tablas propias del cliente SaaS.
     */
    public function query(Request $request)
    {
        $clientId  = (int) $request->input('client_id');
        $serviceId = (int) $request->input('service_id');
        $channelId = (int) $request->input('channel_id');
        $type      = $request->input('type');       // product, service, faq, policy, table
        $query     = $request->input('query', '');
        $maxLimit  = max(1, (int) config('services.donna.knowledge_max_limit', 15));
        $limit     = max(1, min((int) $request->input('limit', 10), $maxLimit));
        $start     = microtime(true);

        $items = DonnaKnowledgeItem::where('client_id', $clientId)
            ->where('is_active', true)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($query, fn ($q) => $q->where(function ($inner) use ($query) {
                $inner->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('content_text', 'LIKE', "%{$query}%");
            }))
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'id'      => $item->id,
                'title'   => $item->title,
                'type'    => $item->type,
                'content' => $item->content_text,
            ])
            ->toArray();

        $ms     = (int) ((microtime(true) - $start) * 1000);
        $result = ['success' => true, 'query' => $query, 'type' => $type, 'count' => count($items), 'data' => $items];
        $this->logger->log('donna_business_data_query', $clientId, ['query' => $query, 'type' => $type], $result, $serviceId, $channelId, $ms);
        return response()->json($result);
    }

    /**
     * Registra o actualiza un lead (cliente final) en la conversación.
     * Guarda los datos en el metadata de la conversación.
     */
    public function upsertLead(Request $request)
    {
        $clientId  = (int) $request->input('client_id');
        $serviceId = (int) $request->input('service_id');
        $channelId = (int) $request->input('channel_id');
        $convId    = (int) $request->input('conversation_id');
        $start     = microtime(true);

        $leadData = $request->only([
            'name', 'phone', 'email', 'interest', 'notes',
            'product_interest', 'budget', 'source', 'status',
        ]);

        $conversation = DonnaConversation::where('id', $convId)
            ->where('client_id', $clientId)
            ->first();

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
        }

        $existing = $conversation->metadata_json ?? [];
        $existing['lead'] = array_merge($existing['lead'] ?? [], array_filter($leadData));
        $existing['lead']['updated_at'] = now()->toDateTimeString();

        $conversation->update(['metadata_json' => $existing]);

        $ms     = (int) ((microtime(true) - $start) * 1000);
        $result = ['success' => true, 'conversation_id' => $convId, 'lead' => $existing['lead'], 'message' => 'Lead actualizado correctamente.'];
        $this->logger->log('donna_business_upsert_lead', $clientId, $leadData, $result, $serviceId, $channelId, $ms);
        return response()->json($result);
    }
}
