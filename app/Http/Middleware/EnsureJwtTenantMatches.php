<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Evita que un JWT emitido en el subdominio de un tenant sea aceptado en el
 * de otro. Como cada tenant tiene su propia BD, un mismo id numerico de
 * Empleado existe (con datos distintos) en cada una — sin esta validacion,
 * un token robado o mal enrutado autenticaria "correctamente" contra el
 * Empleado equivocado de otro negocio.
 */
class EnsureJwtTenantMatches
{
    public function handle(Request $request, Closure $next)
    {
        $claimTenantId = JWTAuth::parseToken()->payload()->get('tenant_id');

        if ($claimTenantId !== tenant('id')) {
            return response()->json(['message' => 'Token no valido para este tenant.'], 401);
        }

        return $next($request);
    }
}
