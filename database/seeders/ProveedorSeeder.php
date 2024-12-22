<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('proveedores')->insert([
            ['nombrepro' => 'Juan Domínguez', 'telefonopro' => '0992905379'],
            ['nombrepro' => 'José Mora Moormix', 'telefonopro' => '0990880300'],
            ['nombrepro' => 'Ec Virtual Store', 'telefonopro' => '0960523682'],
            ['nombrepro' => 'Full Entretenimiento', 'telefonopro' => '+573205045002'],
            ['nombrepro' => 'Fenix Store', 'telefonopro' => '0963433482'],
            ['nombrepro' => 'Spotify', 'telefonopro' => 'PayPal'],
            ['nombrepro' => 'Google', 'telefonopro' => 'Tarjeta'],
        ]);
    }
}
