<?php

use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // Registrar rutas de la API
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Registrar rutas web (si las tienes)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar alias de middleware
        $middleware->alias([
            'api.key' => AuthenticateApiKey::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Confiar en todos los proxies (ngrok, cloudflare, etc)
        $middleware->trustProxies('*');
        $middleware->trustHosts();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
