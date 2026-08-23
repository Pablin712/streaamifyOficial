<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Deuda;
use Illuminate\Database\Seeder;

/**
 * Saldos reales y deuda inicial de Streamify (Pablo). Exclusivo del tenant
 * Streamify — jamas incluir en TenantSeeder, un tenant nuevo no tiene
 * relacion con estos bancos ni con esta deuda.
 */
class StreamifySaldosInicialesSeeder extends Seeder
{
    public function run(): void
    {
        Banco::where('nombreban', 'Banco Pichincha')->update(['monto' => 400]);
        Banco::where('nombreban', 'Banco Guayaquil')->update(['monto' => 20.36]);
        Banco::where('nombreban', 'Produbanco')->update(['monto' => 30]);
        Banco::where('nombreban', 'Banco Internacional')->update(['monto' => 33.19]);
        Banco::where('nombreban', 'Binance')->update(['monto' => 20]);
        Banco::where('nombreban', 'PayPal')->update(['monto' => 16.25]);
        Banco::where('nombreban', 'Banco Bolivariano')->update(['monto' => 3.75]);

        Deuda::firstOrCreate([
            'proveedor_id' => 3,
            'monto' => 313.95,
            'monto_pagado' => 0.00,
            'estado' => 'pendiente',
        ]);

        $this->command->info('Montos iniciales de los bancos de Streamify actualizados correctamente.');
    }
}
