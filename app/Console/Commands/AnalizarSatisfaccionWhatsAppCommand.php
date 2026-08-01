<?php

namespace App\Console\Commands;

use App\Models\Conversacion;
use App\Models\WhatsappAnalisisConversacion;
use App\Services\WhatsApp\ConversacionSatisfaccionAnalyzer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Fase 1 (prototipo) del plan en docs/optimizacion/idea-soporte.md.
 * Analiza conversaciones de WhatsApp con Claude y guarda el resultado en
 * whatsapp_analisis_conversacion. NO toca el sistema de puntos/rendimiento.
 *
 * En produccion casi ninguna conversacion pasa por el flujo formal de "cerrado"
 * (closed_at): de 5419 conversaciones solo 3 lo tienen seteado. Por eso una
 * conversacion se considera "terminada" para efectos de analisis si tiene
 * closed_at (cuando existe) O si no tuvo actividad en las ultimas N horas
 * (--inactividad-horas), usando last_message_at/ultima_actividad como proxy.
 */
class AnalizarSatisfaccionWhatsAppCommand extends Command
{
    protected $signature = 'whatsapp:analizar-satisfaccion
        {--desde= : Fecha desde (Y-m-d), default hace 7 dias. Ignorado si se usa --todo}
        {--hasta= : Fecha hasta (Y-m-d), default hoy. Ignorado si se usa --todo}
        {--todo : Ignora --desde/--hasta, considera TODO el historico de conversaciones terminadas}
        {--limit=0 : Maximo de conversaciones a procesar en esta corrida (0 = sin limite)}
        {--idconv= : Analizar solo esta conversacion puntual (ignora los demas filtros)}
        {--inactividad-horas=6 : Horas sin actividad para considerar una conversacion "terminada"}
        {--reanalizar : Volver a analizar conversaciones que ya tienen un analisis guardado (por defecto se saltan)}
        {--pausa-ms=250 : Milisegundos de espera entre llamadas a la IA (evita rate limits en corridas grandes)}
        {--dry-run : Solo listar que conversaciones entrarian, sin llamar a la IA ni guardar nada}
        {--solo-reporte : No analiza nada, solo muestra el resumen estadistico de lo ya analizado}';

    protected $description = 'Analiza con IA (Claude) la calidad de atencion de conversaciones de WhatsApp cerradas';

    public function handle(ConversacionSatisfaccionAnalyzer $analyzer): int
    {
        if ($this->option('solo-reporte')) {
            $this->mostrarResumen();
            return self::SUCCESS;
        }

        $conversaciones = $this->resolverConversaciones();

        if ($conversaciones->isEmpty()) {
            $this->info('No hay conversaciones pendientes de analizar con esos filtros.');
            $this->mostrarResumen();
            return self::SUCCESS;
        }

        $total = $conversaciones->count();
        $this->info("Conversaciones a procesar: {$total}");

        if ($this->option('dry-run')) {
            $this->table(
                ['idconv', 'idcli', 'estado', 'closed_at', 'ultima_actividad'],
                $conversaciones->map(fn (Conversacion $c) => [
                    $c->idconv,
                    $c->idcli,
                    $c->estado,
                    optional($c->closed_at)->format('Y-m-d H:i') ?? '-',
                    optional($c->ultima_actividad)->format('Y-m-d H:i') ?? '-',
                ])
            );
            $this->comment('Dry-run: no se llamo a la IA ni se guardo nada.');
            return self::SUCCESS;
        }

        $pausaMs = (int) $this->option('pausa-ms');
        $analizadas = 0;
        $saltadas = 0;
        $errores = 0;

        foreach ($conversaciones as $i => $conversacion) {
            try {
                $resultado = $analyzer->analizar($conversacion);

                if ($resultado === null) {
                    $saltadas++;
                    continue;
                }

                $analizadas++;
                $tiempoTxt = $resultado->tiempo_respuesta_promedio_segundos !== null
                    ? round($resultado->tiempo_respuesta_promedio_segundos / 60, 1) . 'min'
                    : '-';

                $this->line(sprintf(
                    '[%d/%d] idconv=%s satisfaccion=%s tiempo_resp=%s cruce=%s servicio=%s motivo=%s',
                    $i + 1,
                    $total,
                    $conversacion->idconv,
                    $resultado->satisfaccion_score,
                    $tiempoTxt,
                    $resultado->cruce_empleados,
                    $resultado->servicio_idser ?? '-',
                    $resultado->motivo_contacto
                ));
            } catch (\Throwable $e) {
                $errores++;
                Log::error('Error analizando conversacion WhatsApp con IA', [
                    'idconv' => $conversacion->idconv,
                    'error' => $e->getMessage(),
                ]);
                $this->error(sprintf('[%d/%d] idconv %s: %s', $i + 1, $total, $conversacion->idconv, $e->getMessage()));
            }

            if ($pausaMs > 0) {
                usleep($pausaMs * 1000);
            }
        }

        $this->newLine();
        $this->info("Analizadas: {$analizadas} | Saltadas (sin mensajes de cliente): {$saltadas} | Errores: {$errores}");

        $this->mostrarResumen();

        return $errores > 0 && $analizadas === 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolverConversaciones()
    {
        if ($idconv = $this->option('idconv')) {
            return Conversacion::where('idconv', $idconv)->get();
        }

        $query = Conversacion::query();

        if ($this->option('todo')) {
            $query->whereRaw('COALESCE(closed_at, last_message_at, ultima_actividad) IS NOT NULL');
        } else {
            $desde = $this->option('desde')
                ? Carbon::parse($this->option('desde'))->startOfDay()
                : now()->subDays(7)->startOfDay();

            $hasta = $this->option('hasta')
                ? Carbon::parse($this->option('hasta'))->endOfDay()
                : now()->endOfDay();

            $query->whereRaw('COALESCE(closed_at, last_message_at, ultima_actividad) BETWEEN ? AND ?', [$desde, $hasta]);
        }

        $limiteInactividad = now()->subHours((int) $this->option('inactividad-horas'));

        // "Terminada" = closed_at seteado (poco comun en la practica) O sin
        // actividad desde hace --inactividad-horas (proxy de sesion terminada).
        $query->where(function ($q) use ($limiteInactividad) {
            $q->whereIn('estado', ['cerrado', 'cerrada'])
                ->orWhereRaw('COALESCE(last_message_at, ultima_actividad) <= ?', [$limiteInactividad]);
        });

        if (! $this->option('reanalizar')) {
            $query->whereNotIn('idconv', function ($sub) {
                $sub->select('idconv')->from('whatsapp_analisis_conversacion');
            });
        }

        $query->orderByRaw('COALESCE(closed_at, last_message_at, ultima_actividad)');

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    private function mostrarResumen(): void
    {
        $total = WhatsappAnalisisConversacion::count();

        if ($total === 0) {
            $this->comment('Todavia no hay conversaciones analizadas para mostrar un resumen.');
            return;
        }

        $distribucion = WhatsappAnalisisConversacion::selectRaw('satisfaccion_score, count(*) as total')
            ->groupBy('satisfaccion_score')
            ->pluck('total', 'satisfaccion_score');

        $promedio = round(WhatsappAnalisisConversacion::avg('satisfaccion_score'), 2);
        $moda = $distribucion->sortDesc()->keys()->first();

        $buenas = WhatsappAnalisisConversacion::whereIn('satisfaccion_score', [4, 5])->count();
        $regulares = WhatsappAnalisisConversacion::where('satisfaccion_score', 3)->count();
        $malas = WhatsappAnalisisConversacion::whereIn('satisfaccion_score', [1, 2])->count();

        $this->newLine();
        $this->info("=== Resumen general — {$total} conversaciones analizadas (historico completo) ===");
        $this->table(
            ['Score', 'Cantidad', '%'],
            collect(range(1, 5))->map(fn ($s) => [
                $s,
                $distribucion[$s] ?? 0,
                round((($distribucion[$s] ?? 0) / $total) * 100, 1) . '%',
            ])
        );
        $this->line("Promedio: {$promedio}/5  |  Moda: {$moda}/5");
        $this->line(sprintf(
            'Buena (4-5★): %d (%s%%)  |  Regular (3★): %d (%s%%)  |  Mala (1-2★): %d (%s%%)',
            $buenas,
            round($buenas / $total * 100, 1),
            $regulares,
            round($regulares / $total * 100, 1),
            $malas,
            round($malas / $total * 100, 1)
        ));
    }
}
