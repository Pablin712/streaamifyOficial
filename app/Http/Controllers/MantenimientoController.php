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
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('mantenimientos')) {
            abort(403, 'No tienes permiso para ver los mantenimientos.');
        }
        $mantenimientos = Mantenimiento::with('cuenta')->orderBy('fechaman', 'asc')->get();
        $cuentas = Cuenta::with(['valor'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
        return view('inventory.mantenimientos.index', compact('mantenimientos', 'cuentas'));
    }

    public function create()
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('mantenimientos.store')) {
            return response()->json(['error' => 'No tienes permiso para crear mantenimientos.'], 403);
        }
        $cuentas = Cuenta::with(['valor'])
            ->where('activocue', true)
            ->orderBy('fechavencue')
            ->get();
        return response()->json(['cuentas' => $cuentas]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('mantenimientos.store')) {
            return response()->json(['error' => 'No tienes permiso para crear mantenimientos.'], 403);
        }

        try {
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
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento creado exitosamente.',
                'mantenimiento' => $mantenimiento->load('cuenta')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function edit($id)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();

        if (!$user->hasPermissionTo('mantenimientos.update')) {
            return response()->json(['error' => 'No tienes permiso para editar mantenimientos.'], 403);
        }
        $mantenimiento = Mantenimiento::with('cuenta')->findOrFail($id);
        return response()->json([
            'success' => true,
            'mantenimiento' => $mantenimiento,
            'cuenta' => $mantenimiento->cuenta ? $mantenimiento->cuenta->idcue . ' - ' . $mantenimiento->cuenta->usuariocue : 'N/A'
        ]);
    }

    public function update(Request $request, $id)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('mantenimientos.update')) {
            return response()->json(['error' => 'No tienes permiso para actualizar mantenimientos.'], 403);
        }

        try {
            $request->validate([
                'fechaman' => 'required|date',
                'descripcionman' => 'required|string|max:255',
            ]);

            $mantenimiento = Mantenimiento::findOrFail($id);

            Historial::create([
                'accion' => 'Actualización de Mantenimiento',
                'descripcion' => 'Datos antiguos: ' . json_encode($mantenimiento),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            $mantenimiento->update([
                'fechaman' => $request->fechaman,
                'descripcionman' => $request->descripcionman,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento actualizado exitosamente.',
                'mantenimiento' => $mantenimiento->load('cuenta')
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        /** @var \App\Models\Empleado $user */
        $user = Auth::user();
        if (!$user->hasPermissionTo('mantenimientos.destroy')) {
            return response()->json(['error' => 'No tienes permiso para eliminar mantenimientos.'], 403);
        }

        try {
            $mantenimiento = Mantenimiento::findOrFail($id);

            Historial::create([
                'accion' => 'Eliminación de Mantenimiento',
                'descripcion' => 'Datos Eliminados: ' . json_encode($mantenimiento),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            $mantenimiento->delete();
            return response()->json([
                'success' => true,
                'message' => 'Mantenimiento eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
