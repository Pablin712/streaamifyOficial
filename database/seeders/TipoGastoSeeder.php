<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class TipoGastoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_gasto')->insert([
            ['detalletip' => 'PUBLICIDAD (META ADS)'],
            ['detalletip' => 'PAGO DE EMPLEADOS'],
            ['detalletip' => 'SERVICIO EN LA NUBE'],
            ['detalletip' => 'OTRO'],
        ]);
    }
}
