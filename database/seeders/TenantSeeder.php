<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Esqueleto minimo para provisionar un Tenant nuevo (ver comando
 * `tenant:create`): solo catalogos genericos, roles y permisos base.
 *
 * Deliberadamente NO incluye (ver docs/panel-vendedores/vendedores.md y el
 * plan de la Fase 1 de multi-tenancy):
 * - ServicioSeeder / ProveedorSeeder / BancosSeeder: catalogo real y
 *   proveedores/bancos del negocio de Pablo, no aplican a un tenant nuevo.
 * - MailSeeder: credenciales reales de correo de Pablo.
 * - FinanzasAvanzadasPermisosSeeder: fondos "Efectivo"/"Mi Negocio Efectivo"
 *   son especificos del negocio personal de Pablo (Negocio Efectivo, fuera
 *   del plan base SaaS).
 * - Chat.../Donna... seeders: Donna esta fuera del plan base SaaS.
 * - Empleado/Cliente/Venta/etc: datos reales o demo, no un esqueleto vacio.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,

            TipoGastoSeeder::class,
            TipoProductoSeeder::class,
            CategoriaSeeder::class,
            EstadoRecargaSeeder::class,

            ChatPermisosSeeder::class,
            BancosPermisosSeeder::class,
        ]);
    }
}
