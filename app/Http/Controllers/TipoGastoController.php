<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoGasto;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TipoGastoController extends Controller
{
    public function index()
    {
        if (!Gate::allows('tipos')) {
            abort(403, 'No tienes permiso para ver los tipos de gasto.');
        }
        $tipoGastos = TipoGasto::all();
        return view('finance.gastos', compact('tipoGastos'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('tipos.store')) {
            abort(403, 'No tienes permiso para crear tipos de gasto.');
        }

        $request->validate([
            'detalletip' => 'required|string|max:50',
        ]);

        $tipogasto = TipoGasto::create([
            'detalletip' => $request->detalletip,
        ]);

        Historial::create([
            'accion' => 'Creación de Tipo de gasto',
            'descripcion' => 'Datos del tipo de gasto: ' . json_encode($tipogasto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto creado con éxito');
    }

    public function edit($id)
    {
        if (!Gate::allows('tipos.update')) {
            abort(403, 'No tienes permiso para editar tipos de gasto.');
        }
        $tipoGasto = TipoGasto::findOrFail($id);
        return response()->json([
            'tipoGastos' => $tipoGasto
        ]);
    }

    public function update(Request $request, $idtip)
    {
        if (!Gate::allows('tipos.update')) {
            abort(403, 'No tienes permiso para actualizar tipos de gasto.');
        }

        $request->validate([
            'detalletip' => 'required|string|max:50',
        ]);

        $tipoGasto = TipoGasto::findOrFail($idtip);

        Historial::create([
            'accion' => 'Actualización de Tipo de gasto',
            'descripcion' => 'Datos antiguos: ' . json_encode($tipoGasto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $tipoGasto->update([
            'detalletip' => $request->detalletip,
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto actualizado con éxito');
    }

    public function destroy($id)
    {
        if (!Gate::allows('tipos.destroy')) {
            abort(403, 'No tienes permiso para eliminar tipos de gasto.');
        }

        $tipoGasto = TipoGasto::findOrFail($id);

        Historial::create([
            'accion' => 'Eliminación de Tipo de gasto',
            'descripcion' => 'Datos Eliminados: ' . json_encode($tipoGasto),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $tipoGasto->delete();

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto eliminado con éxito');
    }
}