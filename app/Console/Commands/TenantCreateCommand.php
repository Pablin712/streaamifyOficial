<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * Aprovisiona un Tenant nuevo (un "Vendedor"): crea su BD desde cero
 * (via el listener TenantCreated -> CreateDatabase + MigrateDatabase,
 * ver TenancyServiceProvider), su Domain, y lo siembra con el esqueleto
 * generico TenantSeeder (roles, catalogos base — nunca datos de Streamify).
 */
class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create
        {nombre : Nombre del negocio/tenant}
        {subdominio : Subdominio, solo el label (ej. "acme" para acme.streamify-saas.test)}';

    protected $description = 'Crea un Tenant nuevo: BD aislada, dominio y datos base';

    public function handle(): int
    {
        $nombre = (string) $this->argument('nombre');
        $subdominio = Str::of((string) $this->argument('subdominio'))
            ->lower()
            ->before('.'.config('tenancy.base_domain'))
            ->slug();

        if ($subdominio === '') {
            $this->error('Subdominio invalido.');
            return self::FAILURE;
        }

        if (Domain::where('domain', $subdominio)->exists()) {
            $this->error("El subdominio '{$subdominio}' ya esta en uso.");
            return self::FAILURE;
        }

        $id = Str::slug($nombre).'-'.Str::random(6);

        $this->line("Creando tenant '{$nombre}' (id: {$id})...");

        $tenant = Tenant::create([
            'id' => $id,
            'nombre' => $nombre,
        ]);

        Domain::create([
            'domain' => (string) $subdominio,
            'tenant_id' => $tenant->id,
        ]);

        $this->info('BD creada y migrada. Sembrando datos base (TenantSeeder)...');

        $this->call('tenants:seed', [
            '--tenants' => [$id],
            '--class' => 'TenantSeeder',
        ]);

        $this->info("Tenant '{$nombre}' listo en: {$subdominio}.".config('tenancy.base_domain'));

        return self::SUCCESS;
    }
}
