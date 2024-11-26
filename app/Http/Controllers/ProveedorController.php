<?php

namespace App\Http\Controllers;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::all();
        return view('inventory.proveedores.index', compact('proveedores'));
    }
    // Crear un nuevo proveedor
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

        Proveedor::create($request->all());

        return redirect()->route('proveedores')->with('success', 'Proveedor creado con éxito.');
    }

    // Editar un proveedor existente
    public function edit($idpro)
    {
        $proveedor = Proveedor::findOrFail($idpro);
        return view('inventory.proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $idpro)
    {
        $request->validate([
            'nombrepro' => 'required|string|max:20', // varchar(20)
            'telefonopro' => 'nullable|string|max:15'
        ]);

        $proveedor = Proveedor::findOrFail($idpro);
        $proveedor->update($request->all());

        return redirect()->route('proveedores')->with('success', 'Proveedor actualizado con éxito.');
    }

    // Eliminar un servicio
    public function destroy($idpro)
    {
        $proveedor = Proveedor::findOrFail($idpro);
        $proveedor->delete();

        return redirect()->route('proveedores')->with('success', 'Proveedor eliminado con éxito.');
    }
}
