<?php

namespace App\Console\Commands;

use App\Services\TenantProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

/**
 * Aprovisiona un Tenant nuevo (un "Vendedor"): crea su BD desde cero
 * (via el listener TenantCreated -> CreateDatabase + MigrateDatabase,
 * ver TenancyServiceProvider), su Domain, y lo siembra con el esqueleto
 * generico TenantSeeder (roles, catalogos base — nunca datos de Streamify).
 *
 * Misma logica que usa el panel central (Fase 2) — ver
 * App\Services\TenantProvisioningService.
 */
class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create
        {nombre : Nombre del negocio/tenant}
        {subdominio : Subdominio, solo el label (ej. "acme" para acme.streamify-saas.test)}';

    protected $description = 'Crea un Tenant nuevo: BD aislada, dominio y datos base';

    public function handle(TenantProvisioningService $provisioning): int
    {
        $nombre = (string) $this->argument('nombre');
        $subdominio = (string) $this->argument('subdominio');

        $this->line("Creando tenant '{$nombre}'...");

        try {
            $tenant = $provisioning->create($nombre, $subdominio);
        } catch (ValidationException $e) {
            $this->error(collect($e->errors())->flatten()->first());
            return self::FAILURE;
        }

        $this->info("Tenant '{$nombre}' (id: {$tenant->id}) listo.");

        return self::SUCCESS;
    }
}
