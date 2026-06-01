<?php

namespace App\Http\Controllers\Api\Donna\Business\Tools;

use App\Http\Controllers\Controller;
use App\Models\DonnaAgentConfig;
use App\Services\Donna\DonnaKnowledgeService;
use App\Services\Donna\DonnaToolLogger;
use Illuminate\Http\Request;

class DonnaBusinessKnowledgeToolController extends Controller
{
    public function __construct(
        private DonnaKnowledgeService $knowledge,
        private DonnaToolLogger       $logger,
    ) {}

    public function search(Request $request)
    {
        $clientId  = (int) $request->input('client_id');
        $serviceId = (int) $request->input('service_id');
        $channelId = (int) $request->input('channel_id');
        $convId    = (int) $request->input('conversation_id');

        $config = DonnaAgentConfig::where('client_id', $clientId)
            ->where('service_type', 'business')
            ->where('is_active', true)
            ->first();

        if (!$config?->knowledge_enabled) {
            return response()->json([
                'success'        => true,
                'results_count'  => 0,
                'results'        => [],
                'message'        => 'Base de conocimientos no habilitada. Informa al cliente que no tienes esa información disponible.',
            ]);
        }

        $query  = $request->input('query', '');
        $type   = $request->input('filters.type') ?: $request->input('type');
        $limit  = (int) $request->input('limit', 5);
        $start  = microtime(true);

        $result = $this->knowledge->search($clientId, $serviceId, $query, $limit, $type);
        $ms     = (int) ((microtime(true) - $start) * 1000);

        $this->logger->log('donna_business_knowledge_search', $clientId,
            ['query' => $query, 'type' => $type], $result, $serviceId, $channelId, $ms);

        return response()->json($result);
    }
}
