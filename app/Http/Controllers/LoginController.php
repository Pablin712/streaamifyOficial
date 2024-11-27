<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Muestra la vista de login.
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Asegúrate de tener la vista 'auth/login.blade.php'
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

        if ($empleado && Hash::check($request->passwordemp, $empleado->passwordemp)) {
            // Autenticar manualmente al usuario si la contraseña coincide
            Auth::login($empleado);

            // Redirige al usuario a su página de inicio o dashboard
            return redirect()->intended('dashboard')->with('success', 'Inicio de sesión exitoso.');
        }

        // Si falla, regresa con un mensaje de error
        return back()->withErrors([
            'usuarioemp' => 'Las credenciales no coinciden con nuestros registros.',
        ])->withInput($request->except('passwordemp'));
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Sesión cerrada correctamente.');
    }
}

