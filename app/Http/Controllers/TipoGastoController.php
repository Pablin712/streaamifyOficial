<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoGasto;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;

class TipoGastoController extends Controller
{
    /*
    // Método __construct original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:tipos')->only('index');
        $this->middleware('can:tipos.store')->only('store');
        $this->middleware('can:tipos.update')->only('update', 'edit');
        $this->middleware('can:tipos.destroy')->only('destroy');
    }
    */

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('tipos')) {
            abort(403, 'No tienes permiso para ver los tipos de gasto.');
        }
        $tipoGastos = TipoGasto::all();
        return view('finance.gastos', compact('tipoGastos'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('tipos.store')) {
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
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto creado con éxito');
    }

    public function edit($id)
    {
        if (!Auth::user()->hasPermissionTo('tipos.update')) {
            abort(403, 'No tienes permiso para editar tipos de gasto.');
        }
        $tipoGasto = TipoGasto::findOrFail($id);
        return response()->json([
            'tipoGastos' => $tipoGasto
        ]);
    }

    public function update(Request $request, $idtip)
    {
        if (!Auth::user()->hasPermissionTo('tipos.update')) {
            abort(403, 'No tienes permiso para actualizar tipos de gasto.');
        }

        $request->validate([
            'detalletip' => 'required|string|max:50',
        ]);

        $tipoGasto = TipoGasto::findOrFail($idtip);

        Historial::create([
            'accion' => 'Actualización de Tipo de gasto',
            'descripcion' => 'Datos antiguos: ' . json_encode($tipoGasto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $tipoGasto->update([
            'detalletip' => $request->detalletip,
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto actualizado con éxito');
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasPermissionTo('tipos.destroy')) {
            abort(403, 'No tienes permiso para eliminar tipos de gasto.');
        }

        $tipoGasto = TipoGasto::findOrFail($id);

        Historial::create([
            'accion' => 'Eliminación de Tipo de gasto',
            'descripcion' => 'Datos Eliminados: ' . json_encode($tipoGasto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $tipoGasto->delete();

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto eliminado con éxito');
    }
}