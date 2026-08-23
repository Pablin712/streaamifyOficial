<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder raiz por convencion de Laravel/stancl-tenancy. Delega al esqueleto
 * generico de Tenant — ver TenantSeeder para el detalle, y StreamifyDemoSeeder
 * para reconstruir datos tipo-Streamify en dev/staging.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TenantSeeder::class);
    }
}
