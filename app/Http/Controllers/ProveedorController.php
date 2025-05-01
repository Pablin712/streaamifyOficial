<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Historial;
use App\Models\Valor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CuentaService;
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
        $proveedores = Proveedor::where('activopro', true)->get();
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
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
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
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
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

        // Verificar si hay valores asociados
        $valoresAsociados = Valor::where('idpro', $proveedor->idpro)->where('activoval', true)->exists();
        if ($valoresAsociados) {
            return redirect()->route('proveedores')->with('error', 'No se puede eliminar porque tiene valores asociados.');
        }

        // Registrar en historial
        Historial::create([
            'accion' => 'Se desactivó el proveedor con ID: ' . $proveedor->idpro,
            'descripcion' => 'Datos inactivos: ' . json_encode($proveedor),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        // Desactivar el proveedor
        $proveedor->update([
            'activopro' => false,
        ]);

        return redirect()->route('proveedores')->with('success', 'Proveedor desactivado con éxito.');
    }
}
