<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asistencia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LimpiarAsistenciasDuplicadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asistencias:limpiar-duplicadas
                            {--fecha= : Fecha específica en formato YYYY-MM-DD}
                            {--dias=7 : Número de días hacia atrás a limpiar}
                            {--dry-run : Simular sin eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina asistencias duplicadas (registros del mismo empleado en intervalos de 30 segundos)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fecha = $this->option('fecha');
        $dias = (int) $this->option('dias');
        $dryRun = $this->option('dry-run');

        if ($fecha) {
            $fechaInicio = Carbon::parse($fecha)->startOfDay();
            $fechaFin = Carbon::parse($fecha)->endOfDay();
            $this->info("Limpiando asistencias duplicadas del día: {$fecha}");
        } else {
            $fechaFin = Carbon::now();
            $fechaInicio = Carbon::now()->subDays($dias);
            $this->info("Limpiando asistencias duplicadas de los últimos {$dias} días");
        }

        $asistencias = Asistencia::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('empleado_id')
            ->orderBy('created_at')
            ->get();

        $totalEliminadas = 0;
        $eliminadas = [];
        $ultimaPorEmpleado = [];

        foreach ($asistencias as $asistencia) {
            $empleadoId = $asistencia->empleado_id;
            $created = Carbon::parse($asistencia->created_at);

            // Si hay una asistencia previa del mismo empleado
            if (isset($ultimaPorEmpleado[$empleadoId])) {
                $ultimaFecha = $ultimaPorEmpleado[$empleadoId]['fecha'];
                $segundosDiferencia = $created->diffInSeconds($ultimaFecha);

                // Si está dentro de 30 segundos, es duplicado
                if ($segundosDiferencia <= 30) {
                    $eliminadas[] = $asistencia->id;
                    $totalEliminadas++;

                    if (!$dryRun) {
                        $asistencia->delete();
                    }

                    continue;
                }
            }

            // Actualizar última asistencia del empleado
            $ultimaPorEmpleado[$empleadoId] = [
                'fecha' => $created,
                'id' => $asistencia->id
            ];
        }

        if ($dryRun) {
            $this->warn("=== MODO SIMULACIÓN (DRY-RUN) ===");
            $this->info("Se eliminarían {$totalEliminadas} asistencias duplicadas");

            if ($totalEliminadas > 0 && $this->option('verbose')) {
                $this->info("IDs que se eliminarían: " . implode(', ', array_slice($eliminadas, 0, 50)));
                if (count($eliminadas) > 50) {
                    $this->info("... y " . (count($eliminadas) - 50) . " más");
                }
            }
        } else {
            $this->info("✅ Se eliminaron {$totalEliminadas} asistencias duplicadas");
        }

        return 0;
    }
}
