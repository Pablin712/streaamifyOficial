<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodigoController extends Controller
{
    public function index()
    {
        // Obtener el cliente autenticado (guard:cliente)
        $cliente = Auth::guard('cliente')->user();
        // Verificar si el cliente tiene usuarios activos
        $usuariosActivos = $cliente->usuarios;

        $codigos = [];
        if ($usuariosActivos->isNotEmpty()) {
            // Obtener las cuentas asociadas a los usuarios activos y sus proveedores
            foreach ($usuariosActivos as $usuario) {
                if ($usuario->cuenta->valor->bot != null) {
                    $codigos[] = [
                        'servicio' => $usuario->cuenta->valor->servicio->idser,
                        'bot' => $usuario->cuenta->valor->bot, // Asumiendo que existe una relación 'proveedor'
                    ];
                }
            }
        }

        // Pasar los datos a la vista
        return view('shopping.codigos', [
            'codigos' => $codigos,
        ]);
    }
}
