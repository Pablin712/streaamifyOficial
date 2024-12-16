<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['idrol' => 'administrador', 'detallerol' => 'Administrador del sistema'],
            ['idrol' => 'bodeguero', 'detallerol' => 'Encargado de bodega'],
            ['idrol' => 'contador', 'detallerol' => 'Encargado de contabilidad'],
            ['idrol' => 'tecnico', 'detallerol' => 'Soporte técnico'],
            ['idrol' => 'vendedor', 'detallerol' => 'Responsable de ventas'],
        ]);
    }
}
