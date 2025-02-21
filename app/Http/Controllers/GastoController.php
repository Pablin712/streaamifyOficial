<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\TipoGasto;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GastoController extends Controller
{
    /*
    // Método __construct original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:gastos')->only('index');
        $this->middleware('can:gastos.store')->only('store');
        $this->middleware('can:gastos.update')->only('update');
        $this->middleware('can:gastos.destroy')->only('destroy');
    }
    */

    // Mostrar todos los gastos
    public function index()
    {
        if (!Auth::user()->hasPermissionTo('gastos')) {
            abort(403, 'No tienes permiso para ver los gastos.');
        }

        $gastos = Gasto::with('tipoGasto')->orderBy('fechagas', 'desc')->get();
        $tipoGastos = TipoGasto::all();

        return view('finance.gastos', compact('gastos', 'tipoGastos'));
    }

    // Crear un nuevo gasto desde el modal
    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('gastos.store')) {
            abort(403, 'No tienes permiso para crear costos.');
        }

        $request->validate([
            'idtip' => 'required|exists:tipo_gasto,idtip',
            'fechagas' => 'required|date',
            'montogas' => 'required|numeric',
            'descripciongas' => 'required|string|max:50',
        ]);

        $gasto = Gasto::create([
            'idtip' => $request->idtip,
            'fechagas' => $request->fechagas,
            'montogas' => $request->montogas,
            'descripciongas' => $request->descripciongas,
        ]);

        Historial::create([
            'accion' => 'Creación de Gasto',
            'descripcion' => 'Datos: ' . json_encode($gasto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('gastos')->with('success', 'Gasto creado con éxito');
    }

    // Mostrar el formulario para editar un gasto (modal)
    public function edit($id)
    {
        // Para la edición no se aplica verificación de permisos manualmente, pero puedes agregarla si lo deseas:
        if (!Auth::user()->hasPermissionTo('gastos.update')) {
            abort(403, 'No tienes permiso para editar costos.');
        }

        $gasto = Gasto::findOrFail($id);
        $tipoGastos = TipoGasto::all();

        return response()->json([
            'gasto' => $gasto,
            'tipoGastos' => $tipoGastos
        ]);
    }

    // Actualizar un gasto (desde el modal)
    public function update(Request $request, $idgas)
    {
        if (!Auth::user()->hasPermissionTo('gastos.update')) {
            abort(403, 'No tienes permiso para actualizar costos.');
        }

        $request->validate([
            'idtip' => 'required|exists:tipo_gasto,idtip',
            'fechagas' => 'required|date',
            'montogas' => 'required|numeric',
            'descripciongas' => 'required|string|max:50',
        ]);

        $gasto = Gasto::findOrFail($idgas);

        Historial::create([
            'accion' => 'Actualización de Gasto',
            'descripcion' => 'Datos antiguos: ' . json_encode($gasto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $gasto->update([
            'fechagas' => $request->fechagas,
            'montogas' => $request->montogas,
            'descripciongas' => $request->descripciongas,
        ]);

        return redirect()->route('gastos')->with('success', 'Gasto actualizado con éxito');
    }

    // Eliminar un gasto
    public function destroy($id)
    {
        if (!Auth::user()->hasPermissionTo('gastos.destroy')) {
            abort(403, 'No tienes permiso para eliminar costos.');
        }

        $gasto = Gasto::findOrFail($id);

        Historial::create([
            'accion' => 'Eliminación de Gasto',
            'descripcion' => 'Datos Eliminados: ' . json_encode($gasto),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $gasto->delete();

        return redirect()->route('gastos')->with('success', 'Gasto eliminado con éxito');
    }
}