<?php

namespace App\Console\Commands;

use App\Models\Conversacion;
use App\Services\WhatsApp\ConversacionSatisfaccionAnalyzer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Fase 1 (prototipo) del plan en docs/optimizacion/idea-soporte.md.
 * Analiza conversaciones de WhatsApp ya cerradas con Claude y guarda el resultado
 * en whatsapp_analisis_conversacion. NO toca el sistema de puntos/rendimiento.
 */
class AnalizarSatisfaccionWhatsAppCommand extends Command
{
    protected $signature = 'whatsapp:analizar-satisfaccion
        {--desde= : Fecha desde (Y-m-d), default hace 7 dias}
        {--hasta= : Fecha hasta (Y-m-d), default hoy}
        {--limit=50 : Maximo de conversaciones a procesar}
        {--idconv= : Analizar solo esta conversacion puntual (ignora --desde/--hasta/--limit)}
        {--dry-run : Solo listar que conversaciones entrarian, sin llamar a la IA ni guardar nada}';

    protected $description = 'Analiza con IA (Claude) la calidad de atencion de conversaciones de WhatsApp cerradas';

    public function handle(ConversacionSatisfaccionAnalyzer $analyzer): int
    {
        $conversaciones = $this->resolverConversaciones();

        if ($conversaciones->isEmpty()) {
            $this->info('No hay conversaciones cerradas que analizar en ese rango.');
            return self::SUCCESS;
        }

        $this->info("Conversaciones encontradas: {$conversaciones->count()}");

        if ($this->option('dry-run')) {
            $this->table(
                ['idconv', 'idcli', 'cerrada'],
                $conversaciones->map(fn (Conversacion $c) => [
                    $c->idconv,
                    $c->idcli,
                    optional($c->closed_at)->format('Y-m-d H:i'),
                ])
            );
            $this->comment('Dry-run: no se llamo a la IA ni se guardo nada.');
            return self::SUCCESS;
        }

        $filas = [];
        $saltadas = 0;
        $errores = 0;

        foreach ($conversaciones as $conversacion) {
            try {
                $resultado = $analyzer->analizar($conversacion);

                if ($resultado === null) {
                    $saltadas++;
                    continue;
                }

                $filas[] = [
                    $conversacion->idconv,
                    $conversacion->idcli,
                    $resultado->satisfaccion_score,
                    $resultado->tiempo_respuesta_promedio_segundos !== null
                        ? round($resultado->tiempo_respuesta_promedio_segundos / 60, 1) . 'min'
                        : '-',
                    $resultado->cruce_empleados,
                    $resultado->motivo_perdida,
                ];
            } catch (\Throwable $e) {
                $errores++;
                Log::error('Error analizando conversacion WhatsApp con IA', [
                    'idconv' => $conversacion->idconv,
                    'error' => $e->getMessage(),
                ]);
                $this->error("idconv {$conversacion->idconv}: {$e->getMessage()}");
            }
        }

        if (! empty($filas)) {
            $this->table(
                ['idconv', 'idcli', 'satisfaccion', 'tiempo resp.', 'cruce', 'motivo perdida'],
                $filas
            );
        }

        $this->newLine();
        $this->info("Analizadas: " . count($filas) . " | Saltadas (sin mensajes de cliente): {$saltadas} | Errores: {$errores}");

        return $errores > 0 && count($filas) === 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolverConversaciones()
    {
        if ($idconv = $this->option('idconv')) {
            return Conversacion::where('idconv', $idconv)->get();
        }

        $desde = $this->option('desde')
            ? Carbon::parse($this->option('desde'))->startOfDay()
            : now()->subDays(7)->startOfDay();

        $hasta = $this->option('hasta')
            ? Carbon::parse($this->option('hasta'))->endOfDay()
            : now()->endOfDay();

        return Conversacion::whereIn('estado', ['cerrado', 'cerrada'])
            ->whereNotNull('closed_at')
            ->whereBetween('closed_at', [$desde, $hasta])
            ->orderBy('closed_at')
            ->limit((int) $this->option('limit'))
            ->get();
    }
}
