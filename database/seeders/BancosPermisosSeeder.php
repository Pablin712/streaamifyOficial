<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Solo permisos del modulo de bancos (generico, apto para cualquier Tenant).
 * Los saldos/deudas reales de Streamify viven en StreamifySaldosInicialesSeeder.
 */
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

        $this->command->info('Permisos de bancos creados y asignados a roles correctamente.');
    }
}
