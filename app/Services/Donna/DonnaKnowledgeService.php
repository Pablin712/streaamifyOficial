<?php

namespace App\Services\Donna;

use App\Models\DonnaKnowledgeBase;
use App\Models\DonnaKnowledgeItem;

class DonnaKnowledgeService
{
    /**
     * Búsqueda semántica simple por palabras clave sobre los items de conocimiento.
     * En el futuro puede reemplazarse por embeddings/vector search.
     */
    public function search(int $clientId, int $serviceId, string $query, int $limit = 5, ?string $type = null): array
    {
        $base = DonnaKnowledgeBase::where('client_id', $clientId)
            ->where('status', 'active')
            ->first();

        if (!$base) {
            return ['success' => true, 'results' => [], 'message' => 'Sin base de conocimientos configurada.'];
        }

        $words = array_filter(explode(' ', strtolower(trim($query))), fn ($w) => strlen($w) > 2);

        $itemsQuery = DonnaKnowledgeItem::where('knowledge_base_id', $base->id)
            ->where('is_active', true);

        if ($type) {
            $itemsQuery->where('type', $type);
        }

        $items = $itemsQuery->get();

        // Score simple: contar cuántas palabras del query aparecen en título + contenido
        $scored = $items->map(function ($item) use ($words) {
            $haystack = strtolower(($item->title ?? '') . ' ' . ($item->content_text ?? ''));
            $score = 0;
            foreach ($words as $word) {
                $score += substr_count($haystack, $word);
            }
            return ['item' => $item, 'score' => $score];
        })
        ->filter(fn ($r) => $r['score'] > 0)
        ->sortByDesc('score')
        ->take($limit)
        ->values();

        $results = $scored->map(fn ($r) => [
            'id'      => $r['item']->id,
            'title'   => $r['item']->title,
            'type'    => $r['item']->type,
            'content' => $r['item']->content_text,
            'score'   => $r['score'],
        ])->toArray();

        return [
            'success'          => true,
            'query'            => $query,
            'knowledge_base'   => $base->name,
            'results_count'    => count($results),
            'results'          => $results,
        ];
    }
}
