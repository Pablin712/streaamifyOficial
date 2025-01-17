<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoProducto;
class TipoProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoProducto::insert([
            ['nombre' => 'Inmediata', 'descripcion' => 'Productos de entrega inmediata'],
            ['nombre' => 'Pedido', 'descripcion' => 'Productos hechos bajo pedido'],
            ['nombre' => 'Personalizado', 'descripcion' => 'Productos personalizados para el cliente'],
            ['nombre' => 'Renovación', 'descripcion' => 'Productos de renovación periódica'],
        ]);
    }
}
