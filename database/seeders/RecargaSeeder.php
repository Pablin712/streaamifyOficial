<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = DB::table('clientes')->pluck('idcli')->toArray();
        $bancos = DB::table('bancos')->pluck('idban')->toArray();
        $estados = DB::table('estado_recargas')->pluck('idestado')->toArray();
        
        if (empty($clientes) || empty($bancos) || empty($estados)) {
            echo "⚠️  Faltan datos: clientes, bancos o estados de recarga.\n";
            return;
        }
        
        $recargas = [
            [
                'idcli' => $clientes[0] ?? 1,
                'numcomprobante' => 'REC' . str_pad(1, 6, '0', STR_PAD_LEFT),
                'valor' => 25.00,
                'foto' => 'comprobantes/recarga1.jpg',
                'idestado' => $estados[1] ?? 2, // Aprobado
                'idban' => $bancos[0] ?? 1,
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'idcli' => $clientes[1] ?? 2,
                'numcomprobante' => 'REC' . str_pad(2, 6, '0', STR_PAD_LEFT),
                'valor' => 50.00,
                'foto' => 'comprobantes/recarga2.jpg',
                'idestado' => $estados[0] ?? 1, // Pendiente
                'idban' => $bancos[1] ?? 1,
                'created_at' => Carbon::now()->subHours(5),
                'updated_at' => Carbon::now()->subHours(5),
            ],
            [
                'idcli' => $clientes[2] ?? 3,
                'numcomprobante' => 'REC' . str_pad(3, 6, '0', STR_PAD_LEFT),
                'valor' => 15.00,
                'foto' => null,
                'idestado' => $estados[2] ?? 3, // Rechazado
                'idban' => $bancos[0] ?? 1,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];
        
        DB::table('recargas')->insert($recargas);
        
        echo "✅ RecargaSeeder: Se crearon " . count($recargas) . " recargas de ejemplo.\n";
    }
}