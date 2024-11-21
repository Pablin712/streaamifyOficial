<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class GastoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('gastos')->insert([
            ['idtip' => 1, 'fechagas' => '2024-09-10', 'montogas' => 40, 'descripciongas' => 'FACEBOOK ADS'],
            ['idtip' => 2, 'fechagas' => '2024-10-30', 'montogas' => 120, 'descripciongas' => 'PAGO A MATEO'],
            ['idtip' => 2, 'fechagas' => '2024-10-30', 'montogas' => 20, 'descripciongas' => 'PAGO A RONALDO'],
            ['idtip' => 3, 'fechagas' => '2024-09-10', 'montogas' => 40, 'descripciongas' => 'PAGO DE AWS'],
        ]);
    }
}
