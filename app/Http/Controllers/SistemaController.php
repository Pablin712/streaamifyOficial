<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SistemaController extends Controller
{
    /**
     * Mostrar la vista de configuración del sistema
     * Solo accesible para administradores
     */
    public function index()
    {
        // Verificar que el usuario tenga rol de administrador
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user || !$user->hasRole('Admin')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return view('settings.sistema.index');
    }
}
