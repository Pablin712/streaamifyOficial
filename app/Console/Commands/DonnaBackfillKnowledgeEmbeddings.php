<?php

namespace App\Console\Commands;

use App\Models\DonnaKnowledgeItem;
use App\Services\Donna\DonnaEmbeddingService;
use Illuminate\Console\Command;

class DonnaBackfillKnowledgeEmbeddings extends Command
{
    protected $signature = 'donna:knowledge:backfill-embeddings
        {--client= : Limitar a un client_id específico}
        {--force : Regenerar también los ítems que ya tienen embedding}
        {--chunk=20 : Cuántos ítems embeber por llamada batch a OpenAI}';

    protected $description = 'Genera embeddings semánticos para los ítems de la base de conocimientos de Donna Business (requiere OPENAI_API_KEY configurada)';

    public function handle(DonnaEmbeddingService $embeddings): int
    {
        if (!$embeddings->enabled()) {
            $this->error('OPENAI_API_KEY no está configurada en .env — no se puede generar embeddings.');
            return self::FAILURE;
        }

        $query = DonnaKnowledgeItem::where('is_active', true);

        if ($clientId = $this->option('client')) {
            $query->where('client_id', (int) $clientId);
        }

        if (!$this->option('force')) {
            $query->whereNull('embedding_json');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No hay ítems pendientes de embedding. Usa --force para regenerar todos.');
            return self::SUCCESS;
        }

        $this->info("Generando embeddings para {$total} ítem(s) con modelo '{$embeddings->model()}'...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $chunkSize = max(1, (int) $this->option('chunk'));
        $ok = 0;
        $failed = 0;

        $query->orderBy('id')->chunkById($chunkSize, function ($items) use ($embeddings, &$ok, &$failed, $bar) {
            $texts = $items->mapWithKeys(fn (DonnaKnowledgeItem $item) => [$item->id => $item->embeddingSourceText()]);
            $vectors = $embeddings->embedBatch($texts->toArray());

            foreach ($items as $item) {
                $vector = $vectors[$item->id] ?? null;
                if ($vector !== null) {
                    $item->forceFill([
                        'embedding_json'       => $vector,
                        'embedding_model'      => $embeddings->model(),
                        'embedding_updated_at' => now(),
                    ])->save();
                    $ok++;
                } else {
                    $failed++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Listo: {$ok} embebidos correctamente, {$failed} fallidos.");

        if ($failed > 0) {
            $this->warn('Los ítems fallidos seguirán respondiendo por búsqueda de palabras clave (fallback automático). Reintenta más tarde con el mismo comando.');
        }

        return self::SUCCESS;
    }
}
