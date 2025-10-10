<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PedidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = DB::table('clientes')->pluck('idcli')->toArray();
        $productos = DB::table('productos')->pluck('id')->toArray();
        $estados = DB::table('estado_recargas')->pluck('idestado')->toArray();
        
        if (empty($clientes) || empty($productos) || empty($estados)) {
            echo "⚠️  Faltan datos: clientes, productos o estados.\n";
            return;
        }
        
        $pedidos = [
            [
                'idcli' => $clientes[0] ?? 1,
                'producto_id' => $productos[0] ?? 1,
                'idestado' => $estados[0] ?? 1, // Pendiente
                'fechapedido' => Carbon::now()->subHours(2),
                'respuesta' => 'Pedido recibido, procesando...',
            ],
            [
                'idcli' => $clientes[1] ?? 2,
                'producto_id' => $productos[1] ?? 2,
                'idestado' => $estados[1] ?? 2, // Aprobado/Completado
                'fechapedido' => Carbon::now()->subDays(1),
                'respuesta' => 'Pedido completado. Credenciales enviadas por WhatsApp.',
            ],
            [
                'idcli' => $clientes[2] ?? 3,
                'producto_id' => $productos[0] ?? 1,
                'idestado' => $estados[0] ?? 1, // Pendiente
                'fechapedido' => Carbon::now()->subMinutes(30),
                'respuesta' => 'Verificando disponibilidad del servicio...',
            ],
        ];
        
        DB::table('pedidos')->insert($pedidos);
        
        echo "✅ PedidoSeeder: Se crearon " . count($pedidos) . " pedidos de ejemplo.\n";
    }
}