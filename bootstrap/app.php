<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Configuration\Middleware;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        using: function () {
            // El dominio central va PRIMERO: el patron wildcard '{tenant}.'
            // de mas abajo tambien matchea sintacticamente el host exacto del
            // panel central (tenant='admin'), y Laravel usa la PRIMERA ruta
            // registrada que matchea. Si el grupo de tenants fuera primero,
            // su propio PreventAccessFromCentralDomains abortaria con 404
            // antes de llegar jamas a routes/central.php.
            Route::middleware('web')
                ->domain(config('tenancy.central_admin_domain'))
                ->group(base_path('routes/central.php'));

            // Cada Tenant (Streamify incluido) vive en su propio subdominio,
            // ej. streamify.<TENANCY_BASE_DOMAIN>. web.php y api.php no se
            // reescriben: la resolucion de tenant corre antes que cualquier
            // guard de auth, asi que Empleado/Cliente/JWT ya consultan la BD
            // correcta cuando llegan a sus middlewares habituales.
            Route::middleware(['api', InitializeTenancyBySubdomain::class, PreventAccessFromCentralDomains::class])
                ->prefix('api')
                ->domain('{tenant}.'.config('tenancy.base_domain'))
                ->group(base_path('routes/api.php'));

            Route::middleware(['web', InitializeTenancyBySubdomain::class, PreventAccessFromCentralDomains::class])
                ->domain('{tenant}.'.config('tenancy.base_domain'))
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar alias de middleware
        $middleware->alias([
            'api.key'    => \App\Http\Middleware\AuthenticateApiKey::class,
            'donna.api'  => \App\Http\Middleware\DonnaApiKeyMiddleware::class,
            'jwt.tenant' => \App\Http\Middleware\EnsureJwtTenantMatches::class,
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
