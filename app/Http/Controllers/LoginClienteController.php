<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class LoginClienteController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('cliente')->check()) {
            return redirect()->route('shop')->with('info', 'Ya estás autenticado.');
        }
        return view('auth.logi'); // Vista personalizada para login de clientes
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|min:3',
        ]);

        // Buscar al usuario por usuarioemp
        $cliente = \App\Models\Cliente::where('email', $request->email)->first();

        if (!$cliente) {
            // Retorna un mensaje de error si el usuario no existe
            return back()->withErrors([
                'usuarioemp' => 'El usuario no existe.',
            ])->withInput($request->except('passwordemp'));
        }

        // Verificar la contraseña
        if (!Hash::check($request->password, $cliente->password)) {
            
            return back()->withErrors([
                'password' => 'La contraseña es incorrecta.',
            ])->withInput($request->except('password'));
        }

        // Autenticar al usuario manualmente
        Auth::login($cliente);
        // Redirigir al dashboard o ruta protegida
        return redirect()->route('shop')->with('success', 'Inicio de sesión exitoso.');
    }

    public function logout(Request $request)
    {
        Auth::guard('cliente')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
