<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Igual que AuthCliente, pero para el guard 'central' del panel de
 * administracion de Tenants. No se usa el middleware 'auth' generico
 * porque su redirect por defecto usa route('login'), que en esta app
 * ya existe con otro significado (login de Empleado en el dominio de
 * cada Tenant) y apuntaria al dominio equivocado.
 */
class AuthCentral
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('central')->check()) {
            return redirect()->route('central.login');
        }

        return $next($request);
    }
}
