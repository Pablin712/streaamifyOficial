<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Proveedor;
use App\Models\Valor;
use App\Models\Cuenta;
use App\Models\Perfil;

class LimpiezaTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crea datos de prueba para testar el comando db:limpiar-inactivos
     */
    public function run(): void
    {
        echo "🧪 Iniciando seeder de datos de prueba para limpieza...\n\n";

        // Obtener datos existentes necesarios
        $empleados = DB::table('empleados')->pluck('idemp')->toArray();
        $clientes = DB::table('clientes')->pluck('idcli')->toArray();
        $perfiles = DB::table('perfiles')->limit(1)->get();

        if (empty($empleados) || empty($clientes) || $perfiles->isEmpty()) {
            echo "❌ No hay empleados, clientes o perfiles. Ejecute los seeders básicos primero.\n";
            echo "   Necesita:\n";
            echo "   - Al menos 1 empleado\n";
            echo "   - Al menos 1 cliente\n";
            echo "   - Al menos 1 perfil\n\n";
            return;
        }

        $emp = $empleados[0];
        $cli = $clientes[0];
        $perfil = $perfiles[0];

        DB::beginTransaction();

        try {
            echo "🛒 Creando ventas de prueba...\n\n";

            // ==================================================
            // CASO 1: Venta antigua (>2 años) con TODOS los detalles inactivos
            // DEBE ELIMINARSE ✅
            // ==================================================
            echo "   📌 CASO 1: Venta antigua con TODOS los detalles inactivos (>2 años)...\n";
            $fecha1 = Carbon::now()->subYears(2)->subDays(10);
            Venta::create([
                'idemp' => $emp,
                'idcli' =>$cli,
                'fechaven' => $fecha1,
            ]);

            // Pequeño delay para asegurar created_at diferente
            usleep(100000); // 0.1 segundos

            // Obtener la última venta creada
            $venta1 = Venta::latest('created_at')->first();

            DetalleVenta::insert([
                [
                    'idven' => $venta1->idven,
                    'idper' => $perfil->idper,
                    'descripciondet' => 'TEST - Detalle 1 Inactivo',
                    'fechavendet' => Carbon::now()->subYears(2),
                    'montodet' => 5.00,
                    'activodet' => 0,
                    'estado' => 'COBRADO',
                    'created_at' => Carbon::now()->subYears(2),
                    'updated_at' => Carbon::now()->subYears(2),
                ],
                [
                    'idven' => $venta1->idven,
                    'idper' => $perfil->idper,
                    'descripciondet' => 'TEST - Detalle 2 Inactivo',
                    'fechavendet' => Carbon::now()->subYears(2),
                    'montodet' => 5.00,
                    'activodet' => 0,
                    'estado' => 'COBRADO',
                    'created_at' => Carbon::now()->subYears(2),
                    'updated_at' => Carbon::now()->subYears(2),
                ],
            ]);

            echo "      🗑️  Venta {$venta1->idven} - 2 detalles TODOS inactivos → SÍ debe eliminarse\n\n";

            // ==================================================
            // CASO 2: Venta antigua (>2 años) con MEZCLA de detalles
            // NO DEBE ELIMINARSE ⚠️
            // ==================================================
            echo "   📌 CASO 2: Venta antigua con MEZCLA de detalles (>2 años)...\n";
            $fecha2 = Carbon::now()->subYears(2)->subDays(5);
            Venta::create([
                'idemp' => $emp,
                'idcli' => $cli,
                'fechaven' => $fecha2,
            ]);

            // Pequeño delay para asegurar created_at diferente
            usleep(100000); // 0.1 segundos

            // Obtener la última venta creada
            $venta2 = Venta::latest('created_at')->first();

            DetalleVenta::insert([
                [
                    'idven' => $venta2->idven,
                    'idper' => $perfil->idper,
                    'descripciondet' => 'TEST - Detalle Activo',
                    'fechavendet' => Carbon::now()->subYears(2),
                    'montodet' => 5.00,
                    'activodet' => 1,
                    'estado' => 'PENDIENTE',
                    'created_at' => Carbon::now()->subYears(2),
                    'updated_at' => Carbon::now()->subYears(2),
                ],
                [
                    'idven' => $venta2->idven,
                    'idper' => $perfil->idper,
                    'descripciondet' => 'TEST - Detalle Inactivo',
                    'fechavendet' => Carbon::now()->subYears(2),
                    'montodet' => 5.00,
                    'activodet' => 0,
                    'estado' => 'COBRADO',
                    'created_at' => Carbon::now()->subYears(2),
                    'updated_at' => Carbon::now()->subYears(2),
                ],
            ]);

            echo "      ⚠️  Venta {$venta2->idven} - 1 activo, 1 inactivo → NO debe eliminarse\n\n";

            // ==================================================
            // CASO 3: Venta reciente (<1 año) con TODOS los detalles inactivos
            // NO DEBE ELIMINARSE ⚠️
            // ==================================================
            echo "   📌 CASO 3: Venta reciente con TODOS los detalles inactivos (<1 año)...\n";
            $fecha3 = Carbon::now()->subMonths(6);
            Venta::create([
                'idemp' => $emp,
                'idcli' => $cli,
                'fechaven' => $fecha3,
            ]);

            // Pequeño delay para asegurar created_at diferente
            usleep(100000); // 0.1 segundos

            // Obtener la última venta creada
            $venta3 = Venta::latest('created_at')->first();

            DetalleVenta::insert([
                [
                    'idven' => $venta3->idven,
                    'idper' => $perfil->idper,
                    'descripciondet' => 'TEST - Reciente Inactivo 1',
                    'fechavendet' => Carbon::now()->subMonths(6),
                    'montodet' => 5.00,
                    'activodet' => 0,
                    'estado' => 'COBRADO',
                    'created_at' => Carbon::now()->subMonths(6),
                    'updated_at' => Carbon::now()->subMonths(6),
                ],
                [
                    'idven' => $venta3->idven,
                    'idper' => $perfil->idper,
                    'descripciondet' => 'TEST - Reciente Inactivo 2',
                    'fechavendet' => Carbon::now()->subMonths(6),
                    'montodet' => 5.00,
                    'activodet' => 0,
                    'estado' => 'COBRADO',
                    'created_at' => Carbon::now()->subMonths(6),
                    'updated_at' => Carbon::now()->subMonths(6),
                ],
            ]);

            echo "      ⚠️  Venta {$venta3->idven} - TODOS inactivos pero reciente → NO debe eliminarse\n\n";

            // ==================================================
            // CASO 4: Venta SIN detalles
            // DEBE ELIMINARSE con --ventas-vacias ✅
            // ==================================================
            echo "   📌 CASO 4: Venta sin detalles...\n";
            $fecha4 = Carbon::now()->subDays(10);
            Venta::create([
                'idemp' => $emp,
                'idcli' => $cli,
                'fechaven' => $fecha4,
            ]);

            // Pequeño delay para asegurar created_at diferente
            usleep(100000); // 0.1 segundos

            // Obtener la última venta creada
            $venta4 = Venta::latest('created_at')->first();

            echo "      🗑️  Venta {$venta4->idven} - SIN detalles → SÍ debe eliminarse con --ventas-vacias\n\n";

            // ==================================================
            // CASO 5: Venta antigua (>1 año) con TODOS los detalles inactivos
            // DEBE ELIMINARSE ✅ (con --anos=1 por defecto)
            // ==================================================
            echo "   📌 CASO 5: Venta con 1.5 años y TODOS los detalles inactivos...\n";
            $fecha5 = Carbon::now()->subYears(1)->subMonths(6);
            Venta::create([
                'idemp' => $emp,
                'idcli' => $cli,
                'fechaven' => $fecha5,
            ]);

            // Pequeño delay para asegurar created_at diferente
            usleep(100000); // 0.1 segundos

            // Obtener la última venta creada
            $venta5 = Venta::latest('created_at')->first();

            DetalleVenta::insert([
                [
                    'idven' => $venta5->idven,
                    'idper' => $perfil->idper,
                    'descripciondet' => 'TEST - 1.5 años Inactivo',
                    'fechavendet' => Carbon::now()->subYears(1)->subMonths(6),
                    'montodet' => 5.00,
                    'activodet' => 0,
                    'estado' => 'COBRADO',
                    'created_at' => Carbon::now()->subYears(1)->subMonths(6),
                    'updated_at' => Carbon::now()->subYears(1)->subMonths(6),
                ],
            ]);

            echo "      🗑️  Venta {$venta5->idven} - 1 detalle inactivo, >1 año → SÍ debe eliminarse\n\n";

            DB::commit();

            // ========================================
            // RESUMEN
            // ========================================
            echo "\n" . str_repeat("=", 80) . "\n";
            echo "✅ DATOS DE PRUEBA CREADOS EXITOSAMENTE\n";
            echo str_repeat("=", 80) . "\n\n";

            echo "📊 RESUMEN DE LO QUE DEBE ELIMINARSE:\n\n";

            echo "Con --ventas-antiguas (default --anos=1):\n";
            echo "  ✅ Venta {$venta1->idven} (2 años, TODOS inactivos) + 2 detalles\n";
            echo "  ✅ Venta {$venta5->idven} (1.5 años, TODOS inactivos) + 1 detalle\n";
            echo "  ❌ Venta {$venta2->idven} (2 años, mezcla) - NO se elimina\n";
            echo "  ❌ Venta {$venta3->idven} (6 meses, TODOS inactivos) - NO se elimina\n\n";

            echo "Con --ventas-vacias:\n";
            echo "  ✅ Venta {$venta4->idven} (sin detalles)\n\n";

            echo "Total esperado con limpieza completa (--ventas-antiguas --ventas-vacias):\n";
            echo "  - 2 ventas antiguas + 3 detalles\n";
            echo "  - 1 venta vacía\n";
            echo "  = 6 registros\n\n";

            echo "💡 Ahora ejecute:\n";
            echo "   php artisan db:limpiar-inactivos --dry-run\n\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "\n❌ Error: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}
