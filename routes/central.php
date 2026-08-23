<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas centrales (dominio de administracion de Tenants)
|--------------------------------------------------------------------------
|
| Stub minimo de Fase 1. La Fase 2 agregara aqui el panel donde Pablo
| crea/gestiona Tenants desde UI (hoy solo existe el comando artisan
| tenant:create). Este dominio NUNCA inicializa tenancy — ver
| tenancy.central_admin_domain y PreventAccessFromCentralDomains.
|
*/

Route::get('/', function () {
    return 'Streamify SaaS — panel central (pendiente Fase 2).';
});
