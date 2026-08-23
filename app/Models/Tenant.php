<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * Un "Vendedor" (o "Cliente SaaS"): negocio con su propia base de datos
 * aislada. El modelo base de stancl/tenancy no incluye HasDatabase ni
 * HasDomains por defecto — sin ellos, tenants:migrate/tenants:seed, el
 * DatabaseTenancyBootstrapper y la resolucion por subdominio (que hace
 * whereHas('domains', ...)) fallan con "Call to undefined method".
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;
}
