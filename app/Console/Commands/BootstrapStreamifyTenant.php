<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Events\TenantCreated;

/**
 * Comando de un solo uso: registra el negocio real de Pablo (Streamify)
 * como el primer Tenant, apuntando a la BD `streamify` YA EXISTENTE, sin
 * crearla ni migrarla — sus 101 migraciones y datos reales ya estan ahi.
 *
 * NUNCA correr esto mas de una vez con el mismo --id, y nunca en pipelines
 * automaticos (ver Fase 1 del plan de multi-tenancy, Paso 1.6).
 */
class BootstrapStreamifyTenant extends Command
{
    protected $signature = 'tenant:bootstrap-streamify
        {--id=streamify : Id del tenant a crear}
        {--database= : Nombre de la BD existente de Streamify (por defecto DB_DATABASE del .env)}
        {--subdomain=streamify : Subdominio (solo el label, sin el dominio base)}';

    protected $description = 'Registra el negocio real de Streamify como el primer Tenant, reutilizando su BD existente';

    public function handle(): int
    {
        $id = (string) $this->option('id');
        $database = (string) ($this->option('database') ?: config('database.connections.mysql.database'));
        $subdomain = (string) $this->option('subdomain');

        if (Tenant::find($id)) {
            $this->error("Ya existe un tenant con id '{$id}'. Este comando es de un solo uso.");
            return self::FAILURE;
        }

        $this->line("Registrando tenant '{$id}' -> BD existente '{$database}' (sin migrar/seedear)...");

        // Evita que el listener de TenantCreated (CreateDatabase + MigrateDatabase,
        // ver TenancyServiceProvider) intente crear/migrar una BD que ya existe
        // con datos reales. Seguro: este comando es un proceso de un solo uso.
        \Illuminate\Support\Facades\Event::forget(TenantCreated::class);

        $tenant = Tenant::create([
            'id' => $id,
            'tenancy_db_name' => $database,
        ]);

        Domain::create([
            'domain' => $subdomain,
            'tenant_id' => $tenant->id,
        ]);

        $this->info("Tenant '{$id}' registrado. Dominio: {$subdomain}." . config('tenancy.base_domain'));
        $this->warn('Recordatorio: correr `php artisan tenants:migrate --tenants=' . $id . '` y confirmar que reporta "Nothing to migrate" (Paso 6 del plan) antes de dar esto por valido.');

        return self::SUCCESS;
    }
}
