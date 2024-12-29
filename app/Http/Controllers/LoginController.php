<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Historial;

class LoginController extends Controller
{
    /**
     * Muestra la vista de login.
     */
    public function showLoginForm()
    {
        // Redirigir si el usuario ya está autenticado
        if (Auth::check()) {
            return redirect()->route('dashboard')->with('info', 'Ya estás autenticado.');
        }

        return view('auth.login'); // Vista de login
    }

    /**
     * Procesa la solicitud de inicio de sesión.
     */
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'usuarioemp' => 'required|string',
            'passwordemp' => 'required|min:3',
        ]);

        // Buscar al usuario por usuarioemp
        $empleado = \App\Models\Empleado::where('usuarioemp', $request->usuarioemp)->first();

        if (!$empleado) {
            // Retorna un mensaje de error si el usuario no existe
            return back()->withErrors([
                'usuarioemp' => 'El usuario no existe.',
            ])->withInput($request->except('passwordemp'));
        }

        // Verificar la contraseña
        if (!Hash::check($request->passwordemp, $empleado->passwordemp)) {
            return back()->withErrors([
                'passwordemp' => 'La contraseña es incorrecta.',
            ])->withInput($request->except('passwordemp'));
        }

        // Autenticar al usuario manualmente
        Auth::login($empleado);

        Historial::create([
            'accion' => 'Ingreso al sistema mediante el empleado: ' . $empleado->usuarioemp,
            'descripcion' =>  null, // Campo opcional
            'realizado_por' => $empleado->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        // Redirigir al dashboard o ruta protegida
        return redirect()->intended('inicio')->with('success', 'Inicio de sesión exitoso.');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout()
    {
        Auth::logout();

        // Redirigir al login con un mensaje
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }
}
