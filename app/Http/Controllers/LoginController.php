<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Historial;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    // Mostrar el formulario de login (web)
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard')->with('info', 'Ya estás autenticado.');
        }

        return view('auth.login'); // Vista de login
    }

    // Iniciar sesión (web)
    public function login(Request $request)
    {
        $request->validate([
            'usuarioemp' => 'required|string',
            'passwordemp' => 'required|min:3',
        ]);

        $empleado = \App\Models\Empleado::where('usuarioemp', $request->usuarioemp)->first();

        if (!$empleado || !Hash::check($request->passwordemp, $empleado->passwordemp)) {
            Historial::create([
                'accion' => 'Intento fallido de ingreso',
                'descripcion' => 'Fallo en el ingreso de usuario o contraseña',
                'realizado_por' => $request->ip(),
                'fecha' => now(),
            ]);

            return back()->withErrors([
                'usuarioemp' => 'Usuario o contraseña incorrectos.',
            ])->withInput($request->except('passwordemp'));
        }

        Auth::login($empleado);

        Historial::create([
            'accion' => 'Ingreso de ' . $empleado->usuarioemp,
            'descripcion' => 'Autenticación e ingreso al sistema',
            'realizado_por' => $empleado->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('inicio')->with('success', 'Inicio de sesión exitoso.');
    }

    // Iniciar sesión (API) con JWT
    public function loginApi(Request $request)
    {
        $request->validate([
            'usuarioemp' => 'required|string',
            'passwordemp' => 'required|min:3',
        ]);

        $empleado = \App\Models\Empleado::where('usuarioemp', $request->usuarioemp)->first();

        if (!$empleado || !Hash::check($request->passwordemp, $empleado->passwordemp)) {
            return response()->json(['error' => 'Usuario o contraseña incorrectos.'], 401);
        }

        try {
            $token = JWTAuth::fromUser($empleado);
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo crear el token.'], 500);
        }

        return response()->json(['token' => $token]);
    }

    // Cerrar sesión (web)
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }

    // Cerrar sesión (API)
    public function logoutApi(Request $request)
    {
        try {
            JWTAuth::parseToken()->invalidate();
            return response()->json(['message' => 'Sesión cerrada correctamente.']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo cerrar la sesión.'], 500);
        }
    }
}
