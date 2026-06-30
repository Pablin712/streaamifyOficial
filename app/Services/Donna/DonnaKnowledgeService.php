<?php

namespace App\Services\Donna;

use App\Models\DonnaKnowledgeBase;
use App\Models\DonnaKnowledgeItem;
use Illuminate\Support\Collection;

class DonnaKnowledgeService
{
    public function __construct(private DonnaEmbeddingService $embeddings) {}

    /**
     * Búsqueda híbrida sobre los ítems de conocimiento de un cliente:
     *  - Semántica (embeddings + similitud de coseno) cuando el ítem ya tiene
     *    embedding guardado y el servicio de OpenAI está configurado. Esto es
     *    lo que permite entender sinónimos/paráfrasis ("dolor de cabeza" →
     *    "analgésico") en vez de exigir coincidencia literal de palabras.
     *  - Por palabras clave (substring) como fallback automático para ítems
     *    que todavía no tienen embedding generado (recién creados antes del
     *    backfill, o si OPENAI_API_KEY no está configurada) — así la
     *    búsqueda nunca deja de funcionar, solo se vuelve menos precisa.
     *
     * El límite de resultados tiene un techo duro en servidor
     * (services.donna.knowledge_max_limit) para que un límite alto pedido
     * por el agente de IA no infle el payload/costo de tokens en bases de
     * conocimiento grandes.
     */
    public function search(int $clientId, int $serviceId, string $query, int $limit = 5, ?string $type = null): array
    {
        $maxLimit = max(1, (int) config('services.donna.knowledge_max_limit', 15));
        $limit    = max(1, min($limit, $maxLimit));

        $base = DonnaKnowledgeBase::where('client_id', $clientId)
            ->where('status', 'active')
            ->first();

        if (!$base) {
            return ['success' => true, 'results' => [], 'message' => 'Sin base de conocimientos configurada.'];
        }

        $itemsQuery = DonnaKnowledgeItem::where('knowledge_base_id', $base->id)
            ->where('is_active', true);

        if ($type) {
            $itemsQuery->where('type', $type);
        }

        $items = $itemsQuery->get();

        $semanticEnabled = $this->embeddings->enabled();
        $queryVector     = $semanticEnabled ? $this->embeddings->embed($query) : null;
        $minScore        = (float) config('services.donna.embedding_min_score', 0.15);
        $words           = array_filter(explode(' ', strtolower(trim($query))), fn ($w) => strlen($w) > 2);

        $scored = $this->scoreItems($items, $queryVector, $words, $minScore);

        $results = $scored
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->map(fn ($r) => [
                'id'         => $r['item']->id,
                'title'      => $r['item']->title,
                'type'       => $r['item']->type,
                'content'    => $r['item']->content_text,
                'score'      => round($r['score'], 4),
                'match_type' => $r['method'],
            ])
            ->toArray();

        return [
            'success'         => true,
            'query'           => $query,
            'knowledge_base'  => $base->name,
            'semantic_search' => $queryVector !== null,
            'results_count'   => count($results),
            'results'         => $results,
        ];
    }

    /**
     * @param Collection<int, DonnaKnowledgeItem> $items
     */
    private function scoreItems(Collection $items, ?array $queryVector, array $words, float $minScore): Collection
    {
        return $items->map(function (DonnaKnowledgeItem $item) use ($queryVector, $words, $minScore) {
            $keywordRaw   = $this->keywordHits($item, $words);
            $keywordScore = $keywordRaw > 0 ? min(1.0, $keywordRaw / 4) : 0.0;

            if ($queryVector !== null && !empty($item->embedding_json)) {
                $semantic = DonnaEmbeddingService::cosineSimilarity($queryVector, $item->embedding_json);
                // Boost leve por coincidencia literal exacta: los embeddings a veces
                // subponderan nombres propios/códigos de producto cortos.
                $score = $semantic + min(0.10, $keywordRaw * 0.02);

                return ['item' => $item, 'score' => $score, 'method' => 'semantic', 'passes' => $semantic >= $minScore];
            }

            return ['item' => $item, 'score' => $keywordScore, 'method' => 'keyword', 'passes' => $keywordRaw > 0];
        })->filter(fn ($r) => $r['passes']);
    }

    private function keywordHits(DonnaKnowledgeItem $item, array $words): int
    {
        if (empty($words)) {
            return 0;
        }

        $haystack = strtolower(($item->title ?? '') . ' ' . ($item->content_text ?? ''));
        $score    = 0;
        foreach ($words as $word) {
            $score += substr_count($haystack, $word);
        }
        return $score;
    }
}
