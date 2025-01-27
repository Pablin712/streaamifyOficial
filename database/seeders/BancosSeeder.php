<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class BancosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propietario = 'Pablo Darío Jiménez Elizalde';
        $cedula = '1004549976';

        $bancos = [
            [
                'nombreban' => 'Banco Pichincha',
                'numeroban' => '2209859440',
                'tipoban' => 'Transaccional',
                'detalleban' => 'Cuenta transaccional, deposita o transfiere en cualquier punto de Banco Pichincha',
                'foto' => 'storage/fotos/pichincha.jpg',
            ],
            [
                'nombreban' => 'Banco Guayaquil',
                'numeroban' => '33111385',
                'tipoban' => 'Ahorros',
                'detalleban' => 'Cuenta de ahorros, deposita o transfiere en cualquier punto de Banco Guayaquil',
                'foto' => 'storage/fotos/guayaquil.jpg',
            ],
            [
                'nombreban' => 'Produbanco',
                'numeroban' => '20001295622',
                'tipoban' => 'Ahorros',
                'detalleban' => 'Cuenta de ahorros, deposita o transfiere en cualquier punto de Produbanco',
                'foto' => 'storage/fotos/produbanco.jpg',
            ],
            [
                'nombreban' => 'Be Produbando',
                'numeroban' => '18001221307',
                'tipoban' => 'Ahorros',
                'detalleban' => 'Cuenta de ahorros, deposita o transfiere en cualquier punto de Be Produbanco',
                'foto' => 'storage/fotos/beprodubanco.png',
            ],
            [
                'nombreban' => 'Banco Bolivariano',
                'numeroban' => '1671082425',
                'tipoban' => 'Ahorros',
                'detalleban' => 'Cuenta de ahorros, deposita o transfiere en cualquier punto de Banco Bolivariano',
                'foto' => 'storage/fotos/bolivariano.jpg',
            ],
        ];

        foreach ($bancos as $banco) {
            DB::table('bancos')->insert(array_merge($banco, [
                'propietarioban' => $propietario,
                'cedulaban' => $cedula,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
