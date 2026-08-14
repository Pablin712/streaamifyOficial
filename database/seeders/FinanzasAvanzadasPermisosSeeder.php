<?php

namespace Database\Seeders;

use App\Models\Fondo;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Permisos y datos base para el modelo financiero "disponible vs no disponible":
 * fondos (Efectivo, Mi Negocio Efectivo) y prestamos a deudores.
 * Ver docs/finanzas/modeloFinanciero.md
 *
 * Es dinero personal de Pablo, no operacion del negocio: solo Admin y Contador.
 */
class FinanzasAvanzadasPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $contador = Role::where('name', 'Contador')->first();
        $roles = array_filter([$admin, $contador]);

        Permission::firstOrCreate(['name' => 'fondos.index'])->syncRoles($roles);
        Permission::firstOrCreate(['name' => 'fondos.store'])->syncRoles($roles);
        Permission::firstOrCreate(['name' => 'fondos.transacciones.store'])->syncRoles($roles);

        Permission::firstOrCreate(['name' => 'deudores.index'])->syncRoles($roles);
        Permission::firstOrCreate(['name' => 'prestamos.store'])->syncRoles($roles);
        Permission::firstOrCreate(['name' => 'prestamos.abonar'])->syncRoles($roles);

        Permission::firstOrCreate(['name' => 'mne.index'])->syncRoles($roles);
        Permission::firstOrCreate(['name' => 'mne.store'])->syncRoles($roles);

        // Fondos base: saldo en 0, se cargan con una transaccion de "Saldo inicial"
        // desde la UI (mantiene trazabilidad completa, ver modeloFinanciero.md).
        Fondo::firstOrCreate(['nombre' => 'Efectivo'], [
            'descripcion' => 'Caja fisica de efectivo. Dinero no disponible: no esta en un banco.',
            'saldo' => 0,
            'activo' => true,
        ]);

        Fondo::firstOrCreate(['nombre' => 'Mi Negocio Efectivo'], [
            'descripcion' => 'Saldo de la app de recargas telefonicas. Dinero no disponible: esta dentro de la app.',
            'saldo' => 0,
            'activo' => true,
        ]);

        $this->command->info('Permisos y fondos base de finanzas avanzadas creados correctamente.');
    }
}
