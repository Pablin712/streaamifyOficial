<?php

namespace App\Console\Commands;

use App\Models\DailyStatistic;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillEstadisticasChurn extends Command
{
    /**
     * Reconstruye usuarios_removidos y clientes_perdidos en daily_statistics para fechas
     * pasadas, a partir de ventas/detalles_venta (no del historial, que no es confiable
     * antes de la corrección del bug de "Cuenta-Quitada"/"Cuenta-Reactivada").
     *
     * Metodología: un cliente se considera "perdido" si HOY no tiene ningún usuario activo
     * (activodet=1). Su "día de pérdida" es la fecha de vencimiento (fechavendet) más
     * reciente entre todos sus detalles de venta históricos. usuarios_removidos cuenta,
     * para ese mismo día, cuántos detalles de venta de ese cliente vencieron exactamente
     * ese día (pudo comprar varios perfiles en su última compra).
     *
     * Nota: esto no captura remociones parciales de clientes que siguen activos hoy
     * (downgrades sin churn total); eso sí queda cubierto hacia adelante por el historial.
     */
    protected $signature = 'estadisticas:backfill-churn
                            {--start=2026-01-01 : Fecha inicial (Y-m-d)}
                            {--end= : Fecha final (Y-m-d). Por defecto: ayer}
                            {--dry-run : Solo mostrar la tabla, no escribir en daily_statistics}';

    protected $description = 'Rellena usuarios_removidos y clientes_perdidos históricos usando ventas/detalles_venta';

    public function handle(): int
    {
        $start = Carbon::parse($this->option('start'))->startOfDay();
        $end = $this->option('end')
            ? Carbon::parse($this->option('end'))->startOfDay()
            : Carbon::yesterday();

        if ($start->gt($end)) {
            $this->error('--start no puede ser mayor que --end.');
            return self::FAILURE;
        }

        $this->info('=== Backfill de churn histórico (clientes_perdidos / usuarios_removidos) ===');
        $this->line("Rango: {$start->toDateString()} -> {$end->toDateString()}");
        $this->newLine();

        // Clientes que HOY no tienen ningún usuario activo, con su última fecha de vencimiento
        $clientesPerdidos = DB::table('detalles_venta as dv')
            ->join('ventas as v', 'dv.idven', '=', 'v.idven')
            ->whereNotIn('v.idcli', function ($q) {
                $q->select('idcli')->from('view_clientes_usuarios');
            })
            ->groupBy('v.idcli')
            ->select('v.idcli', DB::raw('MAX(dv.fechavendet) as ultima_fecha'))
            ->having('ultima_fecha', '>=', $start->toDateString())
            ->having('ultima_fecha', '<=', $end->toDateString())
            ->get();

        if ($clientesPerdidos->isEmpty()) {
            $this->warn('No se encontraron clientes perdidos en ese rango.');
            return self::SUCCESS;
        }

        // Contar usuarios (detalles) que vencieron exactamente en la última fecha de cada cliente perdido
        $porFecha = []; // 'Y-m-d' => ['clientes' => n, 'usuarios' => n]
        $idsPorFecha = $clientesPerdidos->groupBy(fn ($c) => Carbon::parse($c->ultima_fecha)->toDateString());

        foreach ($idsPorFecha as $fecha => $clientes) {
            $idclis = $clientes->pluck('idcli')->all();

            $usuarios = DB::table('detalles_venta as dv')
                ->join('ventas as v', 'dv.idven', '=', 'v.idven')
                ->whereIn('v.idcli', $idclis)
                ->whereDate('dv.fechavendet', $fecha)
                ->count();

            $porFecha[$fecha] = [
                'clientes' => $clientes->count(),
                'usuarios' => $usuarios,
            ];
        }

        ksort($porFecha);

        $rows = [];
        $totalClientes = 0;
        $totalUsuarios = 0;
        foreach ($porFecha as $fecha => $d) {
            $rows[] = [$fecha, $d['clientes'], $d['usuarios']];
            $totalClientes += $d['clientes'];
            $totalUsuarios += $d['usuarios'];
        }

        $this->table(['Fecha', 'Clientes perdidos', 'Usuarios removidos'], $rows);
        $this->newLine();
        $this->info("Total días con churn: " . count($porFecha));
        $this->info("Total clientes perdidos: $totalClientes");
        $this->info("Total usuarios removidos: $totalUsuarios");

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->comment('Modo dry-run: no se escribió nada en daily_statistics.');
            return self::SUCCESS;
        }

        $this->newLine();
        if (!$this->confirm('¿Escribir estos valores en daily_statistics? (se sobreescriben solo usuarios_removidos y clientes_perdidos, nada más)')) {
            $this->comment('Cancelado.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($porFecha));
        $bar->start();

        $actualizados = 0;
        $sinFila = [];

        foreach ($porFecha as $fecha => $d) {
            $stat = DailyStatistic::where('date', $fecha)->first();

            if (!$stat) {
                // No crear filas a medias: active_users es NOT NULL sin default.
                $sinFila[] = $fecha;
                $bar->advance();
                continue;
            }

            $stat->update([
                'usuarios_removidos' => $d['usuarios'],
                'clientes_perdidos' => $d['clientes'],
            ]);
            $actualizados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Backfill completado. Días actualizados: $actualizados.");

        if (!empty($sinFila)) {
            $this->warn('Fechas sin fila en daily_statistics (no se crearon, faltan otros campos): ' . implode(', ', $sinFila));
        }

        return self::SUCCESS;
    }
}
