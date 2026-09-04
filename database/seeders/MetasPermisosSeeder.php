<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MetasPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $admin     = Role::where('name', 'Admin')->first();
        $gerente   = Role::where('name', 'Gerente')->first();
        $contador  = Role::where('name', 'Contador')->first();
        $visitante = Role::where('name', 'Visitante')->first();

        // Ver el tablero de metas: mismos roles que ven el dashboard.
        Permission::firstOrCreate(['name' => 'metas'])
            ->syncRoles(array_filter([$admin, $gerente, $contador, $visitante]));

        // Fijar y cambiar objetivos es una decision de direccion.
        Permission::firstOrCreate(['name' => 'metas.store'])
            ->syncRoles(array_filter([$admin, $gerente]));

        Permission::firstOrCreate(['name' => 'metas.update'])
            ->syncRoles(array_filter([$admin, $gerente]));

        Permission::firstOrCreate(['name' => 'metas.destroy'])
            ->syncRoles(array_filter([$admin]));

        $this->command->info('Permisos de metas creados y asignados correctamente.');
    }
}
