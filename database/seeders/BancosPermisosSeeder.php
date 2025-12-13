<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Deuda;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BancosPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $admin = Role::where('name', 'Admin')->first();
        $gerente = Role::where('name', 'Gerente')->first();
        $contador = Role::where('name', 'Contador')->first();
        $visitante = Role::where('name', 'Visitante')->first();

        // Crear permisos para bancos
        // Ver lista de bancos - Restringido a Admin, Gerente, Contador, Visitante
        Permission::firstOrCreate(['name' => 'bancos.index'])->syncRoles([$admin, $gerente, $contador, $visitante]);

        // Crear bancos - Solo Admin
        Permission::firstOrCreate(['name' => 'bancos.store'])->syncRoles([$admin]);

        // Editar bancos (nombre, tipo, descripción, foto) - Admin y Gerente
        Permission::firstOrCreate(['name' => 'bancos.update'])->syncRoles([$admin, $gerente]);

        // Registrar transacciones manuales - Admin, Gerente, Contador
        Permission::firstOrCreate(['name' => 'bancos.transacciones.store'])->syncRoles([$admin, $gerente, $contador]);

        // Ver transacciones - Admin, Gerente, Contador, Visitante
        Permission::firstOrCreate(['name' => 'bancos.transacciones.index'])->syncRoles([$admin, $gerente, $contador, $visitante]);

        //montos iniciales de los bancos - Solo Admin
        //Banco Pichincha
        Banco::where('nombreban', 'Banco Pichincha')->update(['monto' => 400]);
        Banco::where('nombreban', 'Banco Guayaquil')->update(['monto' => 20.36]);
        Banco::where('nombreban', 'Produbanco')->update(['monto' => 30]);
        Banco::where('nombreban', 'Banco Internacional')->update(['monto' => 33.19]);
        Banco::where('nombreban', 'Binance')->update(['monto' => 20]);
        Banco::where('nombreban', 'PayPal')->update(['monto' => 16.25]);
        Banco::where('nombreban', 'Banco Bolivariano')->update(['monto' => 3.75]);

        //Agregar la deuda inicial o actual existente de Streamify
        Deuda::firstOrCreate([
            'proveedor_id' => 3, // Suponiendo que el proveedor con ID 1 es el correspondiente
            'monto' => 313.95,
            'monto_pagado' => 0.00,
            'estado' => 'pendiente'
        ]);

        //mensaje
        $this->command->info('Permisos de bancos creados y asignados a roles correctamente.');
        $this->command->info('Montos iniciales de los bancos actualizados correctamente.');
    }
}
