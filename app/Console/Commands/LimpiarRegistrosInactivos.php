<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cuenta;
use App\Models\Perfil;
use App\Models\Valor;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;

class LimpiarRegistrosInactivos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:limpiar-inactivos
                            {--dry-run : Simular sin eliminar registros}
                            {--ventas-antiguas : Solo limpiar ventas antiguas con todos los detalles inactivos}
                            {--ventas-vacias : Solo limpiar ventas sin detalles}
                            {--cuentas : Solo limpiar cuentas inactivas y sus perfiles}
                            {--valores : Solo limpiar valores inactivos sin cuentas}
                            {--proveedores : Solo limpiar proveedores inactivos sin valores}
                            {--anos=1 : Años de antigüedad mínima para ventas (default: 1)}
                            {--force : No pedir confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia registros inactivos de la base de datos (ventas antiguas con detalles inactivos, cuentas, perfiles, valores, proveedores)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Determinar qué limpiar
        $limpiarVentasAntiguas = $this->option('ventas-antiguas');
        $limpiarVentasVacias = $this->option('ventas-vacias');
        $limpiarCuentas = $this->option('cuentas');
        $limpiarValores = $this->option('valores');
        $limpiarProveedores = $this->option('proveedores');
        $anosAntiguedad = (int) $this->option('anos');

        // Si no se especificó ninguna opción, limpiar todo
        $limpiarTodo = !($limpiarVentasAntiguas || $limpiarVentasVacias || $limpiarCuentas || $limpiarValores || $limpiarProveedores);

        if ($dryRun) {
            $this->warn('⚠️  MODO SIMULACIÓN - No se eliminarán registros');
            $this->newLine();
        }

        // Confirmación si no es dry-run y no se forzó
        if (!$dryRun && !$force) {
            $this->warn('⚠️  ADVERTENCIA: Esta operación eliminará registros de forma permanente.');
            if (!$this->confirm('¿Desea continuar?')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $estadisticas = [
            'ventas_antiguas' => 0,
            'detalles_eliminados' => 0,
            'ventas_vacias' => 0,
            'perfiles' => 0,
            'cuentas' => 0,
            'valores' => 0,
            'proveedores' => 0,
        ];

        try {
            // 1. Limpiar Ventas Antiguas con TODOS los detalles inactivos
            if ($limpiarTodo || $limpiarVentasAntiguas) {
                $this->info("🔍 Buscando ventas con más de {$anosAntiguedad} año(s) con TODOS los detalles inactivos...");
                [$ventas, $detalles] = $this->limpiarVentasAntiguasInactivas($dryRun, $anosAntiguedad);
                $estadisticas['ventas_antiguas'] = $ventas;
                $estadisticas['detalles_eliminados'] = $detalles;
            }

            // 2. Limpiar Ventas sin detalles
            if ($limpiarTodo || $limpiarVentasVacias) {
                $this->info('🔍 Buscando ventas sin detalles asociados...');
                $estadisticas['ventas_vacias'] = $this->limpiarVentasVacias($dryRun);
            }

            // 3. Limpiar Cuentas inactivas y sus perfiles
            if ($limpiarTodo || $limpiarCuentas) {
                $this->info('🔍 Buscando cuentas inactivas...');
                [$cuentas, $perfiles] = $this->limpiarCuentasInactivas($dryRun);
                $estadisticas['cuentas'] = $cuentas;
                $estadisticas['perfiles'] = $perfiles;
            }

            // 4. Limpiar Valores inactivos sin cuentas
            if ($limpiarTodo || $limpiarValores) {
                $this->info('🔍 Buscando valores inactivos sin cuentas...');
                $estadisticas['valores'] = $this->limpiarValoresInactivos($dryRun);
            }

            // 5. Limpiar Proveedores inactivos sin valores
            if ($limpiarTodo || $limpiarProveedores) {
                $this->info('🔍 Buscando proveedores inactivos sin valores...');
                $estadisticas['proveedores'] = $this->limpiarProveedoresInactivos($dryRun);
            }

            // Mostrar resumen
            $this->newLine();
            $this->mostrarResumen($estadisticas, $dryRun);

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error durante la limpieza: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Limpiar ventas antiguas donde TODOS los detalles sean inactivos
     */
    private function limpiarVentasAntiguasInactivas($dryRun, $anosAntiguedad)
    {
        $fechaLimite = now()->subYears($anosAntiguedad);

        // Obtener ventas antiguas con sus detalles
        $ventasAntiguas = Venta::where('fechaven', '<', $fechaLimite)
            ->with('detalles_venta')
            ->get();

        $ventasAEliminar = [];
        $totalDetalles = 0;

        foreach ($ventasAntiguas as $venta) {
            $detalles = $venta->detalles_venta;

            // Si no tiene detalles, omitir (se maneja en otra función)
            if ($detalles->isEmpty()) {
                continue;
            }

            // Verificar si TODOS los detalles son inactivos
            $todosInactivos = $detalles->every(function ($detalle) {
                // activodet es booleano: 1 = activo, 0 = inactivo
                return $detalle->activodet == 0 || $detalle->activodet === false;
            });

            if ($todosInactivos) {
                $ventasAEliminar[] = $venta;
                $totalDetalles += $detalles->count();
            }
        }

        $countVentas = count($ventasAEliminar);

        if ($countVentas > 0) {
            $this->line("   Encontradas: {$countVentas} ventas antiguas (>{$anosAntiguedad} año(s)) con TODOS los detalles inactivos");
            $this->line("   Total de detalles a eliminar: {$totalDetalles}");

            if (!$dryRun) {
                DB::beginTransaction();
                try {
                    foreach ($ventasAEliminar as $venta) {
                        // Eliminar primero los detalles
                        $venta->detalles_venta()->delete();
                        // Luego la venta
                        $venta->delete();
                    }
                    DB::commit();
                    $this->info("   ✅ Eliminadas: {$countVentas} ventas con {$totalDetalles} detalles");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("   ❌ Error al eliminar: " . $e->getMessage());
                    throw $e;
                }
            }
        } else {
            $this->line("   ✅ No hay ventas antiguas con todos los detalles inactivos");
        }

        return [$countVentas, $totalDetalles];
    }

    /**
     * Limpiar ventas sin detalles asociados
     */
    private function limpiarVentasVacias($dryRun)
    {
        // Obtener IDs de ventas que no tienen detalles de venta
        $ventasVaciasIds = Venta::whereDoesntHave('detalles_venta')->pluck('idven');

        $count = $ventasVaciasIds->count();

        if ($count > 0) {
            $this->line("   Encontradas: {$count} ventas sin detalles");

            if (!$dryRun) {
                Venta::whereIn('idven', $ventasVaciasIds)->delete();
                $this->info("   ✅ Eliminadas: {$count} ventas");
            }
        } else {
            $this->line("   ✅ No hay ventas sin detalles");
        }

        return $count;
    }

    /**
     * Limpiar cuentas inactivas y sus perfiles asociados
     */
    private function limpiarCuentasInactivas($dryRun)
    {
        $cuentasInactivas = Cuenta::where('activocue', 0)
            ->orWhere('activocue', false)
            ->with('perfiles')
            ->get();

        $countCuentas = $cuentasInactivas->count();
        $countPerfiles = 0;

        if ($countCuentas > 0) {
            $this->line("   Encontradas: {$countCuentas} cuentas inactivas");

            // Contar perfiles antes de eliminar
            foreach ($cuentasInactivas as $cuenta) {
                $countPerfiles += $cuenta->perfiles->count();
            }

            if ($countPerfiles > 0) {
                $this->line("   Encontrados: {$countPerfiles} perfiles asociados a cuentas inactivas");
            }

            if (!$dryRun) {
                foreach ($cuentasInactivas as $cuenta) {
                    // Eliminar perfiles primero
                    $cuenta->perfiles()->delete();
                    // Luego eliminar la cuenta
                    $cuenta->delete();
                }
                $this->info("   ✅ Eliminados: {$countPerfiles} perfiles y {$countCuentas} cuentas");
            }
        } else {
            $this->line("   ✅ No hay cuentas inactivas");
        }

        return [$countCuentas, $countPerfiles];
    }

    /**
     * Limpiar valores inactivos que no tienen cuentas asociadas
     */
    private function limpiarValoresInactivos($dryRun)
    {
        $valoresInactivos = Valor::where(function($query) {
                $query->where('activoval', 0)
                      ->orWhere('activoval', false);
            })
            ->whereDoesntHave('cuentas')
            ->get();

        $count = $valoresInactivos->count();

        if ($count > 0) {
            $this->line("   Encontrados: {$count} valores inactivos sin cuentas");

            if (!$dryRun) {
                foreach ($valoresInactivos as $valor) {
                    $valor->delete();
                }
                $this->info("   ✅ Eliminados: {$count} valores");
            }
        } else {
            $this->line("   ✅ No hay valores inactivos sin cuentas");
        }

        return $count;
    }

    /**
     * Limpiar proveedores inactivos que no tienen valores asociados
     */
    private function limpiarProveedoresInactivos($dryRun)
    {
        $proveedoresInactivos = Proveedor::where(function($query) {
                $query->where('activopro', 0)
                      ->orWhere('activopro', false);
            })
            ->whereDoesntHave('valores')
            ->get();

        $count = $proveedoresInactivos->count();

        if ($count > 0) {
            $this->line("   Encontrados: {$count} proveedores inactivos sin valores");

            if (!$dryRun) {
                foreach ($proveedoresInactivos as $proveedor) {
                    $proveedor->delete();
                }
                $this->info("   ✅ Eliminados: {$count} proveedores");
            }
        } else {
            $this->line("   ✅ No hay proveedores inactivos sin valores");
        }

        return $count;
    }

    /**
     * Mostrar resumen de la limpieza
     */
    private function mostrarResumen($estadisticas, $dryRun)
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RESUMEN DE LIMPIEZA');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->table(
            ['Tipo', 'Cantidad'],
            [
                ['Ventas antiguas (todos detalles inactivos)', $estadisticas['ventas_antiguas']],
                ['Detalles eliminados', $estadisticas['detalles_eliminados']],
                ['Ventas vacías', $estadisticas['ventas_vacias']],
                ['Perfiles', $estadisticas['perfiles']],
                ['Cuentas', $estadisticas['cuentas']],
                ['Valores', $estadisticas['valores']],
                ['Proveedores', $estadisticas['proveedores']],
            ]
        );

        $total = array_sum($estadisticas);

        if ($dryRun) {
            $this->warn("⚠️  MODO SIMULACIÓN: {$total} registros serían eliminados");
            $this->info('💡 Ejecute sin --dry-run para eliminar realmente');
        } else {
            $this->info("✅ Total eliminado: {$total} registros");
        }
    }
}
