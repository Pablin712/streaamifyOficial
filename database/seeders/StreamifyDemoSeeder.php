<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Datos reales/demo especificos del negocio de Streamify (Pablo): NO usar
 * para provisionar un Tenant nuevo (ver TenantSeeder). Uso exclusivo
 * dev/staging para reconstruir un entorno tipo-Streamify desde cero.
 */
class StreamifyDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            // Datos básicos (sin dependencias)
            ServicioSeeder::class,
            ProveedorSeeder::class,
            TipoGastoSeeder::class,
            TipoProductoSeeder::class,
            CategoriaSeeder::class,
            BancosSeeder::class,
            EstadoRecargaSeeder::class,

            // Sistema de roles y permisos
            RoleSeeder::class,

            // Usuarios del sistema
            EmpleadoSeeder::class,
            ClienteSeeder::class,

            // Datos dependientes
            ValorSeeder::class,
            CuentaSeeder::class,
            ProductoSeeder::class,

            // Transacciones y operaciones
            GastoSeeder::class,
            ContabilidadSeeder::class,
            VentaSeeder::class, // ✅ Arreglado - ahora crea 150 ventas con 1-4 detalles
            RecargaSeeder::class,
            PedidoSeeder::class,

            // Gestión y estadísticas
            TareaSeeder::class,
            //DailyStatisticSeeder::class,
            MailSeeder::class,
            ChatPermisosSeeder::class,
            ChatSubagenteSeeder::class,
            ChatMemoriaNegocioSeeder::class,
            ChatSoportePlaybooksSeeder::class,
            BancosPermisosSeeder::class,
            StreamifySaldosInicialesSeeder::class,
            FinanzasAvanzadasPermisosSeeder::class,
        ]);
    }
}
