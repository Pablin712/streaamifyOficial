<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TareaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleados = DB::table('empleados')->pluck('idemp')->toArray();
        
        $tareas = [
            [
                'nombretarea' => 'Revisar cuentas vencidas',
                'descripcion' => 'Verificar todas las cuentas que vencen en los próximos 3 días',
                'prioridad' => 'alta',
                'completada' => 0,
                'fechalimit' => Carbon::now()->addDays(2),
                'completada_por' => null,
                'fecha_completada' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombretarea' => 'Actualizar precios de servicios',
                'descripcion' => 'Revisar y actualizar los precios de Netflix y Disney+',
                'prioridad' => 'media',
                'completada' => 1,
                'fechalimit' => Carbon::now()->subDays(1),
                'completada_por' => $empleados[0] ?? null,
                'fecha_completada' => Carbon::now()->subHours(5),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subHours(5),
            ],
            [
                'nombretarea' => 'Backup de base de datos',
                'descripcion' => 'Realizar backup semanal de la base de datos',
                'prioridad' => 'alta',
                'completada' => 0,
                'fechalimit' => Carbon::now()->addDays(1),
                'completada_por' => null,
                'fecha_completada' => null,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'nombretarea' => 'Contactar proveedores nuevos',
                'descripcion' => 'Buscar nuevos proveedores para servicios de streaming',
                'prioridad' => 'baja',
                'completada' => 0,
                'fechalimit' => Carbon::now()->addWeek(),
                'completada_por' => null,
                'fecha_completada' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nombretarea' => 'Generar reporte mensual',
                'descripcion' => 'Crear reporte de ventas y ganancias del mes',
                'prioridad' => 'media',
                'completada' => 1,
                'fechalimit' => Carbon::now()->subDays(2),
                'completada_por' => $empleados[1] ?? null,
                'fecha_completada' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];

        DB::table('tareas')->insert($tareas);
        
        echo "✅ TareaSeeder: Se crearon " . count($tareas) . " tareas de ejemplo.\n";
    }
}