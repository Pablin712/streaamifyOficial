<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Historial;
use App\Models\Valor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Gate;
class ProveedorController extends Controller
{
    public function index()
    {
        if (!Gate::allows('proveedores')) {
            abort(403, 'No tienes permiso para ver los proveedores.');
        }
        $proveedores = Proveedor::where('activopro', true)->get();
        return view('inventory.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        if (!Gate::allows('proveedores.store')) {
            abort(403, 'No tienes permiso para crear proveedores.');
        }
        return view('inventory.proveedores.create');
    }

    public function store(Request $request)
    {
        if (!Gate::allows('proveedores.store')) {
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

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado con éxito.',
                'proveedor' => $proveedor
            ]);
        }

        return redirect()->route('proveedores')->with('success', 'Proveedor creado con éxito.');
    }

    public function edit($idpro)
    {
        if (!Gate::allows('proveedores.update')) {
            abort(403, 'No tienes permiso para editar proveedores.');
        }
        $proveedor = Proveedor::findOrFail($idpro);

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'proveedor' => $proveedor
            ]);
        }

        return view('inventory.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $idpro)
    {
        if (!Gate::allows('proveedores.update')) {
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

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado con éxito.',
                'proveedor' => $proveedor
            ]);
        }

        return redirect()->route('proveedores')->with('success', 'Proveedor actualizado con éxito.');
    }

    public function destroy($idpro)
    {
        if (!Gate::allows('proveedores.destroy')) {
            abort(403, 'No tienes permiso para eliminar proveedores.');
        }

        $proveedor = Proveedor::findOrFail($idpro);

        // Verificar si hay valores asociados
        $valoresAsociados = Valor::where('idpro', $proveedor->idpro)->where('activoval', true)->exists();
        if ($valoresAsociados) {
            // Triple verificación AJAX
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar porque tiene valores asociados.'
                ], 400);
            }
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

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Proveedor desactivado con éxito.'
            ]);
        }

        return redirect()->route('proveedores')->with('success', 'Proveedor desactivado con éxito.');
    }
}
