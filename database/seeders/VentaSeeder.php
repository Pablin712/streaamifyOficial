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
        
        // Usar transacción para asegurar consistencia
        DB::beginTransaction();
        
        try {
            // Crear 150 ventas
            for ($i = 1; $i <= 150; $i++) {
                $fechaVenta = now()->subDays(rand(1, 90));
                
                // Insertar venta (el trigger generará automáticamente el idven)
                DB::table('ventas')->insert([
                    'idemp' => $empleados[array_rand($empleados)],
                    'idcli' => $clientes[array_rand($clientes)],
                    'fechaven' => $fechaVenta->format('Y-m-d'),
                    'totalpagoven' => null, // Se calculará automáticamente con el trigger
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta,
                ]);
                
                // Obtener la venta más reciente (la que acabamos de insertar)
                // Usamos la fecha created_at para identificarla de manera única
                $venta = DB::table('ventas')
                    ->where('created_at', $fechaVenta)
                    ->orderBy('idven', 'desc')
                    ->first();
                
                $ventaId = $venta->idven;
                $ventasCreadas++;
                
                // Crear entre 1 y 4 detalles para esta venta
                $numDetalles = rand(1, 4);
                
                for ($j = 1; $j <= $numDetalles; $j++) {
                    DB::table('detalles_venta')->insert([
                        'idven' => $ventaId,
                        'idper' => !empty($perfiles) ? $perfiles[array_rand($perfiles)] : null,
                        'descripciondet' => $this->generarDescripcionDetalle(),
                        'fechavendet' => $fechaVenta->copy()->addMonths(rand(1, 12))->format('Y-m-d'), // Fecha de vencimiento
                        'montodet' => $this->generarMontoAleatorio(),
                        'activodet' => rand(0, 10) > 1 ? 1 : 0, // 90% activos, 10% inactivos
                        'created_at' => $fechaVenta,
                        'updated_at' => $fechaVenta,
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
    }
}
