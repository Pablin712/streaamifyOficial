<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asistencia;
use Illuminate\Support\Facades\Auth;
class AsistenciaController extends Controller
{
    public function ping(Request $request)
    {
        Asistencia::create([
            'empleado_id' => Auth::user()->idemp, // asumiendo que usas Auth con empleados
            'ruta_actual' => $request->input('ruta_actual'),
        ]);

        return response()->noContent();
    }
}
