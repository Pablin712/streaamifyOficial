<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use App\Support\ClienteAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::guard('cliente')->attempt([
            'email' => ClienteAuth::normalizeText($credentials['email']),
            'password' => $credentials['password'],
        ])) {
            return back()->withErrors([
                'email' => 'Las credenciales ingresadas no son válidas.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        $cliente = Auth::guard('cliente')->user();

        Historial::create([
            'accion' => 'Login-Cliente',
            'descripcion' => 'Autenticación de cliente con email: ' . $cliente->email,
            'empleado_id' => null,
            'created_at' => now(),
        ]);

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
