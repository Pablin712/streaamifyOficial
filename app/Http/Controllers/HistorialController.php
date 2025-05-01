<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistorialController extends Controller
{
    public function show()
    {
        if (!Auth::user()->hasPermissionTo('historial')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        $historial = Historial::orderBy('created_at', 'desc')->get();
        return view('historial.index', compact('historial'));
    }
    public function clear(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('historial.clear')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        // Borrar registros en el rango de fechas (suponiendo que 'fecha' es del tipo date o datetime)
        Historial::whereBetween('created_at', [$request->start_date, $request->end_date])->delete();

        return redirect()->route('historial')->with('success', 'Historial borrado exitosamente.');
    }
}
