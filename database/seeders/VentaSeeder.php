<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleados = DB::table('empleados')->pluck('idemp')->toArray();
        $clientes = DB::table('clientes')->pluck('idcli')->toArray();
        $cuentas = DB::table('cuentas as c')
            ->join('valores as v', 'v.idval', '=', 'c.idval')
            ->select('c.idcue', 'c.fechavencue', 'c.caidacue', 'v.idser', 'v.pantminval', 'v.pantmaxval')
            ->where('c.activocue', true)
            ->orderBy('c.idcue')
            ->get();
        
        if (empty($empleados) || empty($clientes) || $cuentas->isEmpty()) {
            echo "⚠️  No hay empleados, clientes o cuentas para crear ventas.\n";
            return;
        }

        $ventasCreadas = 0;
        $detallesCreados = 0;
        $asignaciones = [];

        $healthyAccounts = $cuentas->where('caidacue', false)->values();
        $damagedAccounts = $cuentas->where('caidacue', true)->values();

        $healthyIds = $healthyAccounts->pluck('idcue')->shuffle()->values()->all();
        $overloadedCount = max(1, (int) floor(count($healthyIds) * 0.25));
        $availableCount = max(2, (int) floor(count($healthyIds) * 0.35));

        $overloadedIds = array_slice($healthyIds, 0, $overloadedCount);
        $availableIds = array_slice(
            array_values(array_diff($healthyIds, $overloadedIds)),
            0,
            $availableCount
        );

        foreach ($cuentas as $cuenta) {
            $perfiles = DB::table('perfiles')
                ->where('idcue', $cuenta->idcue)
                ->orderBy('numeroper')
                ->get();

            $cantidadPerfiles = $perfiles->count();
            if ($cantidadPerfiles === 0) {
                continue;
            }

            $pantMin = max(1, (int) ($cuenta->pantminval ?? 1));
            $pantMax = max($pantMin, (int) ($cuenta->pantmaxval ?? $cantidadPerfiles));

            if (in_array($cuenta->idcue, $overloadedIds, true)) {
                // Sobrecargar algunas cuentas para pruebas de acciones de rescate.
                $ocupacionObjetivo = min(
                    $cantidadPerfiles * 2,
                    $pantMax + rand(1, 3)
                );
            } elseif (in_array($cuenta->idcue, $availableIds, true)) {
                // Dejar cuentas disponibles para que mudaciones funcionen.
                $ocupacionObjetivo = rand(0, max(0, $pantMin - 1));
            } elseif ((bool) $cuenta->caidacue) {
                // Cuentas dañadas con algunos usuarios para simulación operativa.
                $ocupacionObjetivo = rand(
                    max(1, $pantMin - 1),
                    min($cantidadPerfiles + 1, max($pantMin, $pantMax - 1))
                );
            } else {
                // Cuentas normales: ocupación estable, sin llegar al límite.
                $ocupacionMaxima = min(max(1, $pantMax - 1), $cantidadPerfiles + 1);
                $ocupacionMinima = min($ocupacionMaxima, max(1, $pantMin - 1));
                $ocupacionObjetivo = rand($ocupacionMinima, $ocupacionMaxima);
            }

            for ($n = 0; $n < $ocupacionObjetivo; $n++) {
                // Si excede el número de perfiles, se reutilizan perfiles para simular sobrecarga.
                $perfil = $perfiles[$n % $cantidadPerfiles];
                $fechaVenta = now()->subDays(rand(0, 15))->setTime(rand(8, 20), rand(0, 59));
                $fechaVencimiento = Carbon::parse($cuenta->fechavencue);

                if ($fechaVencimiento->lessThanOrEqualTo($fechaVenta)) {
                    $fechaVencimiento = (clone $fechaVenta)->addDays(rand(7, 30));
                }

                $asignaciones[] = [
                    'idper' => $perfil->idper,
                    'fecha_venta' => $fechaVenta,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'monto' => $this->generarMontoAleatorio(),
                    'descripcion' => $this->generarDescripcionDetalle($cuenta->idser),
                ];
            }
        }

        if (empty($asignaciones)) {
            echo "⚠️  No se generaron asignaciones de perfiles para las ventas.\n";
            return;
        }

        shuffle($asignaciones);

        // Usar transacción para asegurar consistencia
        DB::beginTransaction();

        try {
            while (!empty($asignaciones)) {
                $cantidadDetalles = min(rand(1, 3), count($asignaciones));
                $detallesVenta = array_splice($asignaciones, 0, $cantidadDetalles);
                $fechaVenta = $detallesVenta[0]['fecha_venta'];

                DB::table('ventas')->insert([
                    'idemp' => $empleados[array_rand($empleados)],
                    'idcli' => $clientes[array_rand($clientes)],
                    'fechaven' => $fechaVenta->format('Y-m-d H:i:s'),
                    'totalpagoven' => null,
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta,
                ]);

                $venta = DB::table('ventas')
                    ->where('created_at', $fechaVenta)
                    ->orderBy('idven', 'desc')
                    ->first();

                $ventaId = $venta->idven;
                $ventasCreadas++;

                foreach ($detallesVenta as $detalle) {
                    DB::table('detalles_venta')->insert([
                        'idven' => $ventaId,
                        'idper' => $detalle['idper'],
                        'descripciondet' => $detalle['descripcion'],
                        'fechavendet' => $detalle['fecha_vencimiento']->format('Y-m-d'),
                        'montodet' => $detalle['monto'],
                        'activodet' => 1,
                        'created_at' => $detalle['fecha_venta'],
                        'updated_at' => $detalle['fecha_venta'],
                    ]);
                    $detallesCreados++;
                }
            }

            DB::commit();
            echo "✅ VentaSeeder: Se crearon $ventasCreadas ventas con $detallesCreados detalles.\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "❌ Error en VentaSeeder: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    /**
     * Generar descripción aleatoria para detalles de venta
     */
    private function generarDescripcionDetalle(?string $servicio = null): string
    {
        $descripcionesPorServicio = [
            'NETFLIX' => 'Netflix Premium - 1 mes',
            'DISNEYP' => 'Disney+ Premium - 1 mes',
            'DISNEYS' => 'Disney+ Standard - 1 mes',
            'MAX' => 'Max - 1 mes',
            'PRIME' => 'Prime Video - 1 mes',
            'SPOTIFY' => 'Spotify Premium - 1 mes',
            'PARAMOUNT' => 'Paramount+ - 1 mes',
            'CRUNCHY' => 'Crunchyroll - 1 mes',
            'MAGIS' => 'Flujo TV - 1 mes',
        ];

        return $descripcionesPorServicio[$servicio] ?? 'Servicio de streaming - 1 mes';
    }

    /**
     * Generar monto aleatorio para detalles de venta
     */
    private function generarMontoAleatorio(): float
    {
        $precios = [1.50, 2.00, 2.50, 3.00, 3.50, 4.00, 5.00, 6.00, 8.00, 10.00, 12.50, 15.00];
        return $precios[array_rand($precios)];
    }
}
