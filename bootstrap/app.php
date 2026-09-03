<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        using: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar alias de middleware
        $middleware->alias([
            'api.key'    => \App\Http\Middleware\AuthenticateApiKey::class,
            'donna.api'  => \App\Http\Middleware\DonnaApiKeyMiddleware::class,
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /*
         * Sesión caducada (419 TokenMismatch).
         *
         * Por defecto Laravel pinta la página "PAGE EXPIRED", que en el panel
         * aparecía dentro del modal de error de Livewire: el empleado tenía que
         * cerrar el cuadro a mano y volver a entrar. Se cambia por lo que hace
         * cualquier aplicación: mandar al login directamente.
         */
        $exceptions->render(function (TokenMismatchException $e, $request) {
            // Livewire y las llamadas AJAX necesitan una respuesta que puedan
            // interpretar, no HTML: se les devuelve a dónde ir.
            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                return response()->json([
                    'message'  => 'Tu sesión expiró.',
                    'redirect' => route('login'),
                ], 419);
            }

            return redirect()
                ->guest(route('login'))
                ->with('error', 'Tu sesión expiró por inactividad. Vuelve a iniciar sesión.');
        });

        /*
         * Fallo de conexión con la base de datos.
         *
         * El hosting devuelve [2002] "Operation not permitted" cuando se agotan
         * las conexiones. Cada uno de esos fallos escribía un volcado completo
         * en un laravel.log que llegó a 400 MB, lo que a su vez ralentizaba
         * todavía más cada petición. Se registra una línea corta y, si la
         * petición era de fondo (un sondeo), se responde en silencio para no
         * lanzarle un cuadro de error al empleado.
         */
        $exceptions->render(function (QueryException $e, $request) {
            if (!str_contains($e->getMessage(), '2002')) {
                return null; // otros errores de SQL siguen su curso normal
            }

            Log::warning('BD no disponible temporalmente', [
                'ruta' => $request->path(),
            ]);

            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                return response()->json(['message' => 'Servicio no disponible, reintentando.'], 503);
            }

            return null;
        });

        // Los fallos de conexión no necesitan volcado de pila: son ruido.
        $exceptions->dontReport(TokenMismatchException::class);
    })->create();
