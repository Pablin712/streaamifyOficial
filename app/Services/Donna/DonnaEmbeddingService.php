<?php

namespace App\Services\Donna;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Genera embeddings (vectores) con la API de OpenAI y calcula similitud de
 * coseno entre vectores. Se usa para que la búsqueda en la base de
 * conocimientos de Donna Business entienda significado (sinónimos,
 * paráfrasis) en vez de solo coincidencia literal de palabras.
 *
 * Diseño deliberadamente simple (sin BD vectorial dedicada): los vectores se
 * guardan como JSON en donna_knowledge_items.embedding_json y la similitud
 * se calcula en PHP en el momento de la búsqueda. Es suficiente para bases
 * de conocimiento de hasta unos miles de ítems por cliente; si algún cliente
 * crece mucho más, ahí sí conviene migrar a un motor vectorial dedicado.
 */
class DonnaEmbeddingService
{
    private const ENDPOINT = 'https://api.openai.com/v1/embeddings';

    public function enabled(): bool
    {
        return filled(config('services.openai.api_key'));
    }

    public function model(): string
    {
        return config('services.donna.embedding_model', 'text-embedding-3-small');
    }

    /**
     * Genera el embedding de un texto. Devuelve null si el servicio no está
     * configurado o si la llamada falla (nunca debe tumbar el flujo que la
     * invoca: guardar conocimiento o responder un mensaje siempre debe
     * poder seguir funcionando aunque OpenAI esté caído).
     */
    public function embed(string $text): ?array
    {
        $text = trim($text);
        if ($text === '' || !$this->enabled()) {
            return null;
        }

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(15)
                ->post(self::ENDPOINT, [
                    'model' => $this->model(),
                    'input' => mb_substr($text, 0, 8000), // margen de sobra bajo el límite de tokens del modelo
                ]);

            if (!$response->successful()) {
                Log::warning('DonnaEmbeddingService: fallo al generar embedding', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $vector = $response->json('data.0.embedding');

            return is_array($vector) && !empty($vector) ? $vector : null;
        } catch (\Throwable $e) {
            Log::warning('DonnaEmbeddingService: excepción al generar embedding', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Embeddings en batch (una sola llamada a la API para varios textos).
     * Devuelve un array indexado igual que $texts; entradas vacías o que
     * fallen quedan en null.
     */
    public function embedBatch(array $texts): array
    {
        $clean = array_map(fn ($t) => mb_substr(trim((string) $t), 0, 8000), $texts);
        $nonEmptyIndexes = array_keys(array_filter($clean, fn ($t) => $t !== ''));

        if (empty($nonEmptyIndexes) || !$this->enabled()) {
            return array_fill_keys(array_keys($texts), null);
        }

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(30)
                ->post(self::ENDPOINT, [
                    'model' => $this->model(),
                    'input' => array_values(array_intersect_key($clean, array_flip($nonEmptyIndexes))),
                ]);

            if (!$response->successful()) {
                Log::warning('DonnaEmbeddingService: fallo al generar embeddings en batch', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 500),
                ]);
                return array_fill_keys(array_keys($texts), null);
            }

            $data    = $response->json('data', []);
            $results = array_fill_keys(array_keys($texts), null);

            foreach (array_values($nonEmptyIndexes) as $i => $originalKey) {
                $results[$originalKey] = $data[$i]['embedding'] ?? null;
            }

            return $results;
        } catch (\Throwable $e) {
            Log::warning('DonnaEmbeddingService: excepción en embedBatch', ['error' => $e->getMessage()]);
            return array_fill_keys(array_keys($texts), null);
        }
    }

    public static function cosineSimilarity(array $a, array $b): float
    {
        $n = min(count($a), count($b));
        if ($n === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $magA = 0.0;
        $magB = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $dot  += $a[$i] * $b[$i];
            $magA += $a[$i] * $a[$i];
            $magB += $b[$i] * $b[$i];
        }

        if ($magA <= 0.0 || $magB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($magA) * sqrt($magB));
    }
}
