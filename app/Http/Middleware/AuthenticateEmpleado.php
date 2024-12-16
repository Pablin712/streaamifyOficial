<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateEmpleado
{
    /**
     * Manejar una solicitud entrante.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Debes iniciar sesión.');
            session()->put('error', 'Debes iniciar sesión.');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
