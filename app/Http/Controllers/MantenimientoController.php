<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mantenimiento;
use App\Models\Cuenta;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;

class MantenimientoController extends Controller
{
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:mantenimientos')->only('index');
        $this->middleware('can:mantenimientos.store')->only('create', 'store');
        $this->middleware('can:mantenimientos.update')->only('edit', 'update');
        $this->middleware('can:mantenimientos.destroy')->only('destroy');
    }
    */

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('mantenimientos')) {
            abort(403, 'No tienes permiso para ver los mantenimientos.');
        }
        $mantenimientos = Mantenimiento::orderBy('fechaman', 'asc')->get();
        return view('inventory.mantenimientos.index', compact('mantenimientos'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermissionTo('mantenimientos.store')) {
            abort(403, 'No tienes permiso para crear mantenimientos.');
        }
        $cuentas = Cuenta::with(['valor'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
        return view('inventory.mantenimientos.create', compact('cuentas'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('mantenimientos.store')) {
            abort(403, 'No tienes permiso para crear mantenimientos.');
        }
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
            'fechaman' => 'required|date',
            'descripcionman' => 'required|string|max:255',
        ]);

        $mantenimiento = Mantenimiento::create([
            'idcue' => $request->idcue,
            'fechaman' => $request->fechaman,
            'descripcionman' => $request->descripcionman,
        ]);

        Historial::create([
            'accion' => 'Creación de Mantenimiento',
            'descripcion' => 'Datos: ' . json_encode($mantenimiento),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('mantenimientos')->with('success', 'Mantenimiento creado exitosamente.');
    }

    public function edit($id)
    {
        if (!Auth::user()->hasPermissionTo('mantenimientos.update')) {
            abort(403, 'No tienes permiso para editar mantenimientos.');
        }
        $mantenimiento = Mantenimiento::findOrFail($id);
        return view('inventory.mantenimientos.edit', compact('mantenimiento'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('mantenimientos.update')) {
            abort(403, 'No tienes permiso para actualizar mantenimientos.');
        }
        $request->validate([
            'fechaman' => 'required|date',
            'descripcionman' => 'required|string|max:255',
        ]);

        $mantenimiento = Mantenimiento::findOrFail($id);

        Historial::create([
            'accion' => 'Actualización de Mantenimiento',
            'descripcion' => 'Datos antiguos: ' . json_encode($mantenimiento),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $mantenimiento->update([
            'fechaman' => $request->fechaman,
            'descripcionman' => $request->descripcionman,
        ]);

        return redirect()->route('mantenimientos')->with('success', 'Mantenimiento actualizado exitosamente.');
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasPermissionTo('mantenimientos.destroy')) {
            abort(403, 'No tienes permiso para eliminar mantenimientos.');
        }
        $mantenimiento = Mantenimiento::findOrFail($id);

        Historial::create([
            'accion' => 'Eliminación de Mantenimiento',
            'descripcion' => 'Datos Eliminados: ' . json_encode($mantenimiento),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $mantenimiento->delete();
        return redirect()->route('mantenimientos')->with('success', 'Mantenimiento eliminado exitosamente.');
    }
}
