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
            session()->flash('autenticate', 'Regístrate y recarga saldo para poder comprar con Streamify de forma segura.');
            return redirect()->route('cliente.register'); // Redirige a principal
        }

        // Si pasa las validaciones, continúa con la solicitud
        return $next($request);
    }
}
