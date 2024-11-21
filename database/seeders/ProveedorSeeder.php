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
            ['nombrepro' => 'JUAN DOMÍNGUEZ', 'telefonopro' => '0992905379'],
            ['nombrepro' => 'JOSÉ MORA MOORMIX', 'telefonopro' => '0990880300'],
            ['nombrepro' => 'EC VIRTUAL STORE', 'telefonopro' => '0960523682'],
        ]);
    }
}
