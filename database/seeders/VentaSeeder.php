<?php

namespace Database\Seeders;

use App\Models\Venta;
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
        $perfiles = DB::table('perfiles')->pluck('idper')->toArray();
        
        if (empty($empleados) || empty($clientes)) {
            echo "⚠️  No hay empleados o clientes para crear ventas.\n";
            return;
        }
        
        $ventasCreadas = 0;
        $detallesCreados = 0;
        
        // Crear 150 ventas
        for ($i = 1; $i <= 150; $i++) {
            // Insertar venta (el trigger generará automáticamente el idven)
            DB::table('ventas')->insert([
                'idemp' => $empleados[array_rand($empleados)],
                'idcli' => $clientes[array_rand($clientes)],
                'fechaven' => now()->subDays(rand(1, 90))->format('Y-m-d'),
                'totalpagoven' => null, // Se calculará automáticamente con el trigger
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
            
            // Obtener el ID de la venta recién creada
            $ventaId = DB::table('ventas')->orderBy('created_at', 'desc')->first()->idven;
            $ventasCreadas++;
            
            // Crear entre 1 y 4 detalles para esta venta
            $numDetalles = rand(1, 4);
            
            for ($j = 1; $j <= $numDetalles; $j++) {
                $fechaVenta = now()->subDays(rand(1, 90));
                
                DB::table('detalles_venta')->insert([
                    'idven' => $ventaId,
                    'idper' => !empty($perfiles) ? $perfiles[array_rand($perfiles)] : null,
                    'descripciondet' => $this->generarDescripcionDetalle(),
                    'fechavendet' => $fechaVenta->addMonths(rand(1, 12))->format('Y-m-d'), // Fecha de vencimiento
                    'montodet' => $this->generarMontoAleatorio(),
                    'activodet' => rand(0, 10) > 1 ? 1 : 0, // 90% activos, 10% inactivos
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta,
                ]);
                $detallesCreados++;
            }
        }
        
        echo "✅ VentaSeeder: Se crearon $ventasCreadas ventas con $detallesCreados detalles.\n";
    }

    /**
     * Generar descripción aleatoria para detalles de venta
     */
    private function generarDescripcionDetalle(): string
    {
        $servicios = [
            'Netflix Premium - 1 mes',
            'Netflix Standard - 1 mes', 
            'Disney Plus Premium - 1 mes',
            'Disney Plus Standard - 1 mes',
            'Prime Video - 1 mes',
            'Spotify Premium - 1 mes',
            'HBO Max - 1 mes',
            'Paramount Plus - 1 mes',
            'Crunchyroll - 1 mes',
            'Apple TV Plus - 1 mes',
            'YouTube Premium - 1 mes',
            'Plex - 1 mes'
        ];
        
        return $servicios[array_rand($servicios)];
    }

    /**
     * Generar monto aleatorio para detalles de venta
     */
    private function generarMontoAleatorio(): float
    {
        $precios = [1.50, 2.00, 2.50, 3.00, 3.50, 4.00, 5.00, 6.00, 8.00, 10.00, 12.50, 15.00];
        return $precios[array_rand($precios)];
        /*
        DB::table('ventas')->insert([
            ['idven' => 'FAC001-25112024', 'idemp' => 2, 'idcli' => 14, 'fechaven' => '2024-11-25', 'totalpagoven' => null],
            ['idven' => 'FAC002-26112024', 'idemp' => 2, 'idcli' => 45, 'fechaven' => '2024-11-26', 'totalpagoven' => null],
            ['idven' => 'FAC003-27112024', 'idemp' => 1, 'idcli' => 25, 'fechaven' => '2024-11-27', 'totalpagoven' => null],
            ['idven' => 'FAC004-28112024', 'idemp' => 2, 'idcli' => 13, 'fechaven' => '2024-11-28', 'totalpagoven' => null],
            ['idven' => 'FAC005-29112024', 'idemp' => 2, 'idcli' => 40, 'fechaven' => '2024-11-29', 'totalpagoven' => null],
            ['idven' => 'FAC006-30112024', 'idemp' => 1, 'idcli' => 54, 'fechaven' => '2024-11-30', 'totalpagoven' => null],
            ['idven' => 'FAC007-01122024', 'idemp' => 2, 'idcli' => 2, 'fechaven' => '2024-12-01', 'totalpagoven' => null],
            ['idven' => 'FAC008-02122024', 'idemp' => 1, 'idcli' => 6, 'fechaven' => '2024-12-02', 'totalpagoven' => null],
            ['idven' => 'FAC009-03122024', 'idemp' => 2, 'idcli' => 55, 'fechaven' => '2024-12-03', 'totalpagoven' => null],
            ['idven' => 'FAC010-04122024', 'idemp' => 1, 'idcli' => 49, 'fechaven' => '2024-12-04', 'totalpagoven' => null],
            ['idven' => 'FAC011-05122024', 'idemp' => 2, 'idcli' => 20, 'fechaven' => '2024-12-05', 'totalpagoven' => null],
            ['idven' => 'FAC012-06122024', 'idemp' => 1, 'idcli' => 24, 'fechaven' => '2024-12-06', 'totalpagoven' => null],
            ['idven' => 'FAC013-07122024', 'idemp' => 1, 'idcli' => 25, 'fechaven' => '2024-12-07', 'totalpagoven' => null],
            ['idven' => 'FAC014-08122024', 'idemp' => 1, 'idcli' => 42, 'fechaven' => '2024-12-08', 'totalpagoven' => null],
            ['idven' => 'FAC015-09122024', 'idemp' => 2, 'idcli' => 23, 'fechaven' => '2024-12-09', 'totalpagoven' => null],
            ['idven' => 'FAC016-10122024', 'idemp' => 2, 'idcli' => 7, 'fechaven' => '2024-12-10', 'totalpagoven' => null],
            ['idven' => 'FAC017-11122024', 'idemp' => 2, 'idcli' => 24, 'fechaven' => '2024-12-11', 'totalpagoven' => null],
            ['idven' => 'FAC018-12122024', 'idemp' => 1, 'idcli' => 11, 'fechaven' => '2024-12-12', 'totalpagoven' => null],
            ['idven' => 'FAC019-13122024', 'idemp' => 2, 'idcli' => 43, 'fechaven' => '2024-12-13', 'totalpagoven' => null],
            ['idven' => 'FAC020-14122024', 'idemp' => 2, 'idcli' => 54, 'fechaven' => '2024-12-14', 'totalpagoven' => null],
            ['idven' => 'FAC021-15122024', 'idemp' => 1, 'idcli' => 59, 'fechaven' => '2024-12-15', 'totalpagoven' => null],
        ]);*/
    }
}
