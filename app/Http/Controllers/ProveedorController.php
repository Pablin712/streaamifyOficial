<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:proveedores')->only('index');
        $this->middleware('can:proveedores.store')->only('create', 'store');
        $this->middleware('can:proveedores.update')->only('edit', 'update');
        $this->middleware('can:proveedores.destroy')->only('destroy');
    }
    */

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('proveedores')) {
            abort(403, 'No tienes permiso para ver los proveedores.');
        }
        $proveedores = Proveedor::all();
        return view('inventory.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermissionTo('proveedores.store')) {
            abort(403, 'No tienes permiso para crear proveedores.');
        }
        return view('inventory.proveedores.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermissionTo('proveedores.store')) {
            abort(403, 'No tienes permiso para crear proveedores.');
        }

        $request->validate([
            'nombrepro' => 'required|string|max:20',
            'telefonopro' => 'string|max:15'
        ]);

        $request->merge([
            'nombrepro' => ucwords(strtolower($request->nombrepro))
        ]);

        $proveedor = Proveedor::create($request->all());

        Historial::create([
            'accion' => 'Creación de Proveedor',
            'descripcion' => 'Se registró al proveedor con datos: ' . json_encode($proveedor),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('proveedores')->with('success', 'Proveedor creado con éxito.');
    }

    public function edit($idpro)
    {
        if (!Auth::user()->hasPermissionTo('proveedores.update')) {
            abort(403, 'No tienes permiso para editar proveedores.');
        }
        $proveedor = Proveedor::findOrFail($idpro);
        return view('inventory.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $idpro)
    {
        if (!Auth::user()->hasPermissionTo('proveedores.update')) {
            abort(403, 'No tienes permiso para actualizar proveedores.');
        }

        $request->validate([
            'nombrepro' => 'required|string|max:20',
            'telefonopro' => 'nullable|string|max:15'
        ]);

        $request->merge([
            'nombrepro' => ucwords(strtolower($request->nombrepro))
        ]);

        $proveedor = Proveedor::findOrFail($idpro);

        Historial::create([
            'accion' => 'Actualización de Proveedor',
            'descripcion' => 'Datos antiguos: ' . json_encode($proveedor),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $proveedor->update($request->all());

        return redirect()->route('proveedores')->with('success', 'Proveedor actualizado con éxito.');
    }

    public function destroy($idpro)
    {
        if (!Auth::user()->hasPermissionTo('proveedores.destroy')) {
            abort(403, 'No tienes permiso para eliminar proveedores.');
        }
        $proveedor = Proveedor::findOrFail($idpro);

        Historial::create([
            'accion' => 'Eliminación de Proveedor',
            'descripcion' => 'Datos Eliminados: ' . json_encode($proveedor),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $proveedor->delete();

        return redirect()->route('proveedores')->with('success', 'Proveedor eliminado con éxito.');
    }
}