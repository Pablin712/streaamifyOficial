<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categoria::insert([
            ['nombre' => 'Individual', 'descripcion' => 'Productos individuales'],
            ['nombre' => 'Combo', 'descripcion' => 'Productos en combo'],
            ['nombre' => 'Completo', 'descripcion' => 'Productos completos que incluyen múltiples servicios'],
        ]);
    }
}
