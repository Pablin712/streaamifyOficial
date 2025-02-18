<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
    public function __construct() {
        $this->middleware('can:proveedores')->only('index');
        $this->middleware('can:proveedores.store')->only('create', 'store');
        $this->middleware('can:proveedores.update')->only('edit', 'update');
        $this->middleware('can:proveedores.destroy')->only('destroy');
    }
    public function index()
    {
        $proveedores = Proveedor::all();
        return view('inventory.proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('inventory.proveedores.create');
    }

    public function store(Request $request)
    {
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
        $proveedor = Proveedor::findOrFail($idpro);
        return view('inventory.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $idpro)
    {
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
}
