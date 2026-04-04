<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Listar todas las migraciones de 2024 que queremos marcar como ejecutadas
        $migrations2024 = [
            '2024_01_01_000001_create_base_tables',
            '2024_01_01_000002_create_valores_cuentas_perfiles',
            '2024_01_01_000003_create_ventas_detalles',
            '2024_01_01_000004_create_costos_mantenimientos',
            '2024_01_01_000005_create_productos_categorias',
            '2024_01_01_000006_create_bancos_recargas_pedidos',
            '2024_01_01_000007_create_gastos_contabilidad_estadisticas',
            '2024_01_01_000008_create_tareas_asistencias_historial',
            '2024_01_01_000009_create_permissions_roles',
            '2024_01_01_000010_create_triggers',
            '2024_01_01_000011_create_perfiles_trigger',
            '2024_01_01_000012_create_views',
            '2026_04_04_000001_create_soportes_table',
        ];

        $batch = 1; // Número de batch (puedes ajustarlo si ya tienes migraciones)

        foreach ($migrations2024 as $migration) {
            // Verificar si ya existe antes de insertar
            $exists = DB::table('migrations')
                ->where('migration', $migration)
                ->exists();

            if (!$exists) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch
                ]);

                $this->command->info("✅ Migración marcada: {$migration}");
            } else {
                $this->command->warn("⚠️  Ya existe: {$migration}");
            }
        }

        $this->command->info('🎉 Todas las migraciones de 2024 han sido marcadas como ejecutadas.');
    }
}
