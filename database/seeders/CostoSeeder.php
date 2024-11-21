<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CostoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('costos')->insert([
            ['idcue' => 'NETFLIX-1', 'montocos' => 7.25, 'descripcioncos' => 'COMPRADO SIN PROBLEMAS'],
            ['idcue' => 'NETFLIX-2', 'montocos' => 7.25, 'descripcioncos' => 'COMPRADO SIN PROBLEMAS'],
            ['idcue' => 'NETFLIX-3', 'montocos' => 7.25, 'descripcioncos' => 'COMPRADO SIN PROBLEMAS'],
            ['idcue' => 'DISNEY-1', 'montocos' => 6, 'descripcioncos' => 'COMPRADO SIN PROBLEMAS'],
            ['idcue' => 'DISNEY-2', 'montocos' => 6, 'descripcioncos' => 'COMPRADO SIN PROBLEMAS'],
            ['idcue' => 'MAX-1', 'montocos' => 2.5, 'descripcioncos' => 'COMPRADO SIN PROBLEMAS'],
            ['idcue' => 'MAX-2', 'montocos' => 2.5, 'descripcioncos' => 'COMPRADO SIN PROBLEMAS'],
        ]);
    }
}
