<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class EstadoRecargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Pendiente', 'descripcion' => 'La recarga está pendiente de aprobación.'],
            ['nombre' => 'Rechazado', 'descripcion' => 'La recarga fue rechazada.'],
            ['nombre' => 'Aprobado', 'descripcion' => 'La recarga fue aprobada.'],
        ];

        foreach ($estados as $estado) {
            DB::table('estado_recargas')->insert(array_merge($estado, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
