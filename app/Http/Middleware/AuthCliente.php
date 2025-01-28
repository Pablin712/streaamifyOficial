<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthCliente
{
    /**
     * Manejar una solicitud entrante.
     */
    public function handle(Request $request, Closure $next)
    {

        if (!Auth::guard('cliente')->check()) {
            session()->flash('error', 'No tienes permisos para acceder a esta página.');
            return redirect()->route('principal'); // Redirige a principal
        }

        // Si pasa las validaciones, continúa con la solicitud
        return $next($request);
    }
}
