<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
    public function index()
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $proveedores = Proveedor::all();
        return view('inventory.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        return view('inventory.proveedores.create');
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

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
            'descripcion' =>  'Se registró al proveedor con datos: '. json_encode($proveedor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);


        return redirect()->route('proveedores')->with('success', 'Proveedor creado con éxito.');
    }

    public function edit($idpro)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $proveedor = Proveedor::findOrFail($idpro);
        return view('inventory.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $idpro)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

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
            'descripcion' =>  'Datos antiguos: ' . json_encode($proveedor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp .' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $proveedor->update($request->all());

        return redirect()->route('proveedores')->with('success', 'Proveedor actualizado con éxito.');
    }

    public function destroy($idpro)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $proveedor = Proveedor::findOrFail($idpro);

        Historial::create([
            'accion' => 'Eliminación de Proveedor',
            'descripcion' =>  'Datos Eliminados: ' . json_encode($proveedor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $proveedor->delete();

        return redirect()->route('proveedores')->with('success', 'Proveedor eliminado con éxito.');
    }

    private function authorizeRole(array $roles)
    {
        $userRole = Auth::user()->idrol;

        if (!in_array($userRole, $roles)) {
            // Redirigir a la vista anterior con una alerta
            return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción.')->send();
        }
    }
}
