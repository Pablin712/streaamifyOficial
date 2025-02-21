<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Cuenta;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostoController extends Controller
{
    // Eliminamos el __construct() que usaba middleware para probar los métodos de forma manual

    public function index(Request $request)
    {
        // Verificar permiso para ver costos (ajusta el nombre del permiso según corresponda)
        if (!Auth::user()->hasPermissionTo('costos')) {
            abort(403, 'No tienes permiso para ver los costos.');
        }
        
        // Obtener todas las cuentas para el selector
        $cuentas = Cuenta::all();

        // Puedes filtrar por cuenta si lo deseas, aquí se listan todos ordenados
        $idcueSeleccionado = $request->idcue;
        $costos = Costo::orderBy('fechacos', 'desc')->get();

        return view('finance.costos', compact('cuentas', 'costos', 'idcueSeleccionado'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('costos.store')) {
            abort(403, 'No tienes permiso para crear costos.');
        }

        // Validar los datos
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
            'descripcioncos' => 'required|string|max:50',
            'montocos' => 'required|numeric|min:0',
            'fechacos' => 'nullable|date'
        ]);

        // Crear el costo
        $costo = Costo::create($request->all());

        Historial::create([
            'accion' => 'Creación de Costo',
            'descripcion' => 'Datos: ' . json_encode($costo),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('costos', ['idcue' => $request->idcue])
                         ->with('success', 'Costo creado correctamente.');
    }

    public function update(Request $request, $idcos)
    {
        if (!Auth::user()->hasPermissionTo('costos.update')) {
            abort(403, 'No tienes permiso para actualizar costos.');
        }

        $request->validate([
            'descripcioncos' => 'required|string|max:50',
            'montocos' => 'required|numeric',
            'fechacos' => 'required|date',
        ]);

        $costo = Costo::findOrFail($idcos);

        Historial::create([
            'accion' => 'Actualización de Costo',
            'descripcion' => 'Datos antiguos: ' . json_encode($costo),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . $request->ip(),
            'fecha' => now(),
        ]);

        $costo->update([
            'descripcioncos' => $request->descripcioncos,
            'montocos' => $request->montocos,
            'fechacos' => $request->fechacos,
        ]);

        return redirect()->route('costos')->with('success', 'Costo actualizado con éxito.');
    }

    public function destroy($idcos)
    {
        if (!Auth::user()->hasPermissionTo('costos.destroy')) {
            abort(403, 'No tienes permiso para eliminar costos.');
        }

        $costo = Costo::findOrFail($idcos);
        $idcue = $costo->idcue; // Para regresar a la cuenta seleccionada

        Historial::create([
            'accion' => 'Eliminación de Costo',
            'descripcion' => 'Datos Eliminados: ' . json_encode($costo),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $costo->delete();

        return redirect()->route('costos', ['idcue' => $idcue])
                         ->with('success', 'Costo eliminado correctamente.');
    }
}