<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ServicioController extends Controller
{
    /*
    // Método __construct original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:servicios')->only('index');
        $this->middleware('can:servicios.store')->only('create', 'store');
        $this->middleware('can:servicios.update')->only('edit', 'update');
        $this->middleware('can:servicios.destroy')->only('destroy');
    }
    */

    public function index()
    {
        if (!Gate::allows('servicios')) {
            abort(403, 'No tienes permiso para ver los servicios.');
        }

        $servicios = Servicio::all();
        return view('inventory.servicios.index', compact('servicios'));
    }

    public function create()
    {
        if (!Gate::allows('servicios.store')) {
            abort(403, 'No tienes permiso para crear servicios.');
        }

        return view('inventory.servicios.create');
    }

    public function store(Request $request)
    {
        if (!Gate::allows('servicios.store')) {
            abort(403, 'No tienes permiso para crear servicios.');
        }

        $request->validate([
            'idser' => 'required|string|max:10|unique:servicios,idser',
            'nombreser' => 'required|string|max:20',
            'completoser' => 'nullable|numeric',
            'precioser' => 'nullable|numeric',
            'comboser' => 'nullable|numeric',
            'reventaser' => 'nullable|numeric',
            'revcompser' => 'nullable|numeric',
        ]);

        $request->merge([
            'idser' => strtoupper($request->idser),
            'nombreser' => ucwords(strtolower($request->nombreser))
        ]);

        $servicio = Servicio::create($request->all());

        Historial::create([
            'accion' => 'Creación de Servicio',
            'descripcion' => 'Datos del servicio: ' . json_encode($servicio),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        // Si es petición AJAX, retornar JSON
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Servicio creado con éxito',
                'servicio' => $servicio
            ]);
        }

        return redirect()->route('servicios')->with('success', 'Servicio creado con éxito.');
    }

    public function edit($idser)
    {
        if (!Gate::allows('servicios.update')) {
            abort(403, 'No tienes permiso para editar servicios.');
        }

        $servicio = Servicio::where('idser', $idser)->firstOrFail();

        // Si es petición AJAX, retornar JSON
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'servicio' => $servicio
            ]);
        }

        return view('inventory.servicios.edit', compact('servicio'));
    }

    public function update(Request $request, $idser)
    {
        if (!Gate::allows('servicios.update')) {
            abort(403, 'No tienes permiso para actualizar servicios.');
        }

        $request->validate([
            'nombreser' => 'required|string|max:20',
            'completoser' => 'nullable|numeric',
            'precioser' => 'nullable|numeric',
            'comboser' => 'nullable|numeric',
            'reventaser' => 'nullable|numeric',
            'revcompser' => 'nullable|numeric',
        ]);

        $request->merge([
            'nombreser' => ucwords(strtolower($request->nombreser))
        ]);

        $servicio = Servicio::where('idser', $idser)->firstOrFail();

        Historial::create([
            'accion' => 'Actualización de Servicio',
            'descripcion' => 'Datos antiguos: ' . json_encode($servicio),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $servicio->update($request->all());

        // Si es petición AJAX, retornar JSON
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Servicio actualizado con éxito',
                'servicio' => $servicio
            ]);
        }

        return redirect()->route('servicios')->with('success', 'Servicio actualizado con éxito.');
    }

    public function destroy($idser)
    {
        if (!Gate::allows('servicios.destroy')) {
            abort(403, 'No tienes permiso para eliminar servicios.');
        }

        $servicio = Servicio::where('idser', $idser)->firstOrFail();

        Historial::create([
            'accion' => 'Eliminación de Servicio',
            'descripcion' => 'Datos Eliminados: ' . json_encode($servicio),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $servicio->delete();

        // Si es petición AJAX, retornar JSON
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Servicio eliminado con éxito'
            ]);
        }

        return redirect()->route('servicios')->with('success', 'Servicio eliminado con éxito.');
    }
}
