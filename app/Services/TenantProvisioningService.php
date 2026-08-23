<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * Aprovisiona un Tenant nuevo (BD aislada + dominio + TenantSeeder).
 * Usado tanto por el comando `tenant:create` como por el panel central
 * (App\Http\Controllers\Central\TenantController) para no duplicar logica.
 */
class TenantProvisioningService
{
    public function create(string $nombre, string $subdominioCrudo): Tenant
    {
        $subdominio = (string) Str::of($subdominioCrudo)
            ->lower()
            ->before('.'.config('tenancy.base_domain'))
            ->slug();

        if ($subdominio === '') {
            throw ValidationException::withMessages(['subdominio' => 'Subdominio invalido.']);
        }

        if (Domain::where('domain', $subdominio)->exists()) {
            throw ValidationException::withMessages(['subdominio' => "El subdominio '{$subdominio}' ya esta en uso."]);
        }

        $id = Str::slug($nombre).'-'.Str::random(6);

        // Dispara TenantCreated (CreateDatabase + MigrateDatabase), ver
        // TenancyServiceProvider.
        $tenant = Tenant::create([
            'id' => $id,
            'nombre' => $nombre,
        ]);

        Domain::create([
            'domain' => $subdominio,
            'tenant_id' => $tenant->id,
        ]);

        Artisan::call('tenants:seed', [
            '--tenants' => [$id],
            '--class' => 'TenantSeeder',
        ]);

        return $tenant;
    }
}
