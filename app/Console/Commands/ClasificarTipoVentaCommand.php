<?php

namespace App\Console\Commands;

use App\Models\Historial;
use App\Models\Venta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClasificarTipoVentaCommand extends Command
{
    /**
     * Clasifica retroactivamente ventas.tipo_venta para ventas que ya existían
     * antes de que VentaController empezara a llenarlo. Best-effort:
     *
     * 1) Si el historial tiene un registro "Renovación-Venta {idvenPasado}" cuya
     *    descripción incluye la venta nueva creada (JSON de $ventaNueva), esa
     *    venta nueva se marca 'renovacion' con certeza.
     * 2) Para el resto, se recorre cada cliente en orden cronológico de sus
     *    ventas: la primera es 'nueva'; las siguientes son 'ampliacion' si el
     *    cliente ya tenía un detalle de venta con fechavendet posterior a la
     *    fecha de esa venta (o sea, algo vigente), o 'reactivacion' si no.
     */
    protected $signature = 'ventas:clasificar-tipo
        {--dry-run : Solo mostrar cuantas ventas se clasificarian en cada tipo, sin guardar}';

    protected $description = 'Backfill de ventas.tipo_venta para ventas historicas sin clasificar';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('=== Paso 1: renovaciones confirmadas via historial ===');
        $renovaciones = $this->mapearRenovacionesDesdeHistorial();
        $this->line('Ventas identificadas como renovacion via historial: ' . count($renovaciones));

        if (!$dryRun && !empty($renovaciones)) {
            Venta::whereIn('idven', $renovaciones)
                ->whereNull('tipo_venta')
                ->update(['tipo_venta' => 'renovacion']);
        }

        $this->newLine();
        $this->info('=== Paso 2: resto por orden cronologico por cliente ===');

        $pendientes = Venta::whereNull('tipo_venta')
            ->orderBy('idcli')
            ->orderBy('fechaven')
            ->orderBy('idven')
            ->get(['idven', 'idcli', 'fechaven']);

        if ($pendientes->isEmpty()) {
            $this->line('No quedan ventas sin clasificar.');
            return self::SUCCESS;
        }

        $idvens = $pendientes->pluck('idven');
        $vencimientosPorVenta = DB::table('detalles_venta')
            ->whereIn('idven', $idvens)
            ->select('idven', DB::raw('MAX(fechavendet) as max_venc'))
            ->groupBy('idven')
            ->pluck('max_venc', 'idven');

        $contadores = ['nueva' => 0, 'ampliacion' => 0, 'reactivacion' => 0];
        $updates = []; // tipo_venta => [idven, ...]

        foreach ($pendientes->groupBy('idcli') as $ventasCliente) {
            $vencimientoMasReciente = null; // el mas alto visto hasta ahora en el recorrido

            foreach ($ventasCliente as $venta) {
                $fechaVentaStr = $venta->fechaven->format('Y-m-d');

                if ($vencimientoMasReciente === null) {
                    $tipo = 'nueva';
                } else {
                    $tipo = ($vencimientoMasReciente > $fechaVentaStr) ? 'ampliacion' : 'reactivacion';
                }

                $contadores[$tipo]++;
                $updates[$tipo][] = $venta->idven;

                $vencDeEsta = $vencimientosPorVenta[$venta->idven] ?? null;
                if ($vencDeEsta && ($vencimientoMasReciente === null || $vencDeEsta > $vencimientoMasReciente)) {
                    $vencimientoMasReciente = $vencDeEsta;
                }
            }
        }

        $this->table(
            ['Tipo', 'Cantidad'],
            collect($contadores)->map(fn ($v, $k) => [$k, $v])->values()
        );

        if ($dryRun) {
            $this->newLine();
            $this->comment('Modo dry-run: no se escribio nada.');
            return self::SUCCESS;
        }

        foreach ($updates as $tipo => $idvenList) {
            foreach (array_chunk($idvenList, 500) as $chunk) {
                Venta::whereIn('idven', $chunk)->update(['tipo_venta' => $tipo]);
            }
        }

        $this->newLine();
        $this->info('Backfill completado.');
        return self::SUCCESS;
    }

    /**
     * Devuelve la lista de idven (venta NUEVA creada al renovar) que se pueden
     * identificar con certeza a partir del JSON guardado en el historial.
     */
    private function mapearRenovacionesDesdeHistorial(): array
    {
        $idvens = [];

        Historial::where('accion', 'like', 'Renovación-Venta%')
            ->whereNotNull('descripcion')
            ->chunkById(500, function ($rows) use (&$idvens) {
                foreach ($rows as $row) {
                    $prefijo = 'Nueva venta creada: ';
                    $pos = strpos($row->descripcion, $prefijo);
                    if ($pos === false) {
                        continue;
                    }
                    $json = substr($row->descripcion, $pos + strlen($prefijo));
                    $data = json_decode($json, true);
                    if (is_array($data) && !empty($data['idven'])) {
                        $idvens[] = $data['idven'];
                    }
                }
            }, 'id');

        return array_unique($idvens);
    }
}
