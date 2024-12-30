<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            ServicioSeeder::class,
            ProveedorSeeder::class,
            ValorSeeder::class,
            RolSeeder::class,
            EmpleadoSeeder::class,
            TipoGastoSeeder::class,
            GastoSeeder::class,
            ClienteSeeder::class,
            CuentaSeeder::class,
            //CostoSeeder::class,
            VentaSeeder::class,
            //DetalleVentaSeeder::class,
            ContabilidadSeeder::class,
            //MantenimientoSeeder::class,
        ]);
    }
}
