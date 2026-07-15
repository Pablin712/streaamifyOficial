<?php

namespace App\Console\Commands;

use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GuardarEstadisticasDiarias extends Command
{
    /**
     * Nombre y firma del comando.
     */
    protected $signature = 'estadisticas:guardar
                            {--date= : Fecha específica (formato: Y-m-d). Por defecto: hoy}
                            {--start= : Fecha inicial para rango}
                            {--end= : Fecha final para rango}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Guarda las estadísticas diarias del dashboard en la tabla daily_statistics';

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        parent::__construct();
        $this->dashboardService = $dashboardService;
    }

    /**
     * Ejecutar el comando.
     */
    public function handle()
    {
        $this->info('=== Guardando Estadísticas Diarias ===');
        $this->newLine();

        // Verificar si es rango o fecha única
        if ($this->option('start') && $this->option('end')) {
            return $this->guardarRango();
        } else {
            return $this->guardarFechaUnica();
        }
    }

    /**
     * Guardar estadísticas de una fecha única
     */
    protected function guardarFechaUnica()
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $this->info("Fecha: {$date->format('Y-m-d')}");
        $this->newLine();

        try {
            $result = $this->dashboardService->guardar($date->format('Y-m-d'));

            // Mostrar resultados
            $this->components->info('✓ Estadísticas guardadas correctamente');
            $this->newLine();

            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Usuarios activos', number_format($result['data']['active_users'])],
                    ['Cuentas', number_format($result['data']['accounts'])],
                    ['Ingresos del día', '$' . number_format($result['data']['daily_revenue'], 2)],
                    ['Costos del día', '$' . number_format($result['data']['daily_cost'], 2)],
                    ['Gastos del día', '$' . number_format($result['data']['daily_bill'], 2)],
                    ['Ventas del día', number_format($result['data']['daily_sales'])],
                    ['Balance', '$' . number_format($result['data']['balance'], 2)],
                    ['Usuarios removidos', number_format($result['data']['usuarios_removidos'])],
                    ['Clientes perdidos', number_format($result['data']['clientes_perdidos'])],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->components->error('Error al guardar estadísticas');
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Guardar estadísticas de un rango de fechas
     */
    protected function guardarRango()
    {
        try {
            $startDate = Carbon::parse($this->option('start'));
            $endDate = Carbon::parse($this->option('end'));

            $this->info("Guardando estadísticas desde {$startDate->format('Y-m-d')} hasta {$endDate->format('Y-m-d')}");
            $this->newLine();

            $totalDias = $startDate->diffInDays($endDate) + 1;
            $this->info("Total de días: $totalDias");
            $this->newLine();

            $bar = $this->output->createProgressBar($totalDias);
            $bar->start();

            $saved = 0;
            $errors = 0;
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                try {
                    $this->dashboardService->guardar($currentDate->format('Y-m-d'));
                    $saved++;
                } catch (\Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("Error en {$currentDate->format('Y-m-d')}: {$e->getMessage()}");
                }

                $bar->advance();
                $currentDate->addDay();
            }

            $bar->finish();
            $this->newLine(2);

            $this->components->info("✓ Proceso completado");
            $this->info("Días guardados: $saved");
            if ($errors > 0) {
                $this->warn("Días con errores: $errors");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->components->error('Error en el proceso');
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
