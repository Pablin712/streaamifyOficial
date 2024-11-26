<?php

namespace App\Http\Controllers;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        return view('sales.clientes.index', compact('clientes'));
    }
    // Crear un nuevo proveedor
    public function create()
    {
        return view('sales.clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombrecli' => 'required|string|max:20',
            'telefonocli' => 'string|max:15'
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes')->with('success', 'Cliente creado con éxito.');
    }

    // Editar un cliente existente
    public function edit($idcli)
    {
        $cliente = Cliente::findOrFail($idcli);
        return view('sales.clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $idcli)
    {
        $request->validate([
            'nombrecli' => 'required|string|max:20', // varchar(20)
            'telefonocli' => 'nullable|string|max:15'
        ]);

        $cliente = Cliente::findOrFail($idcli);
        $cliente->update($request->all());

        return redirect()->route('clientes')->with('success', 'Cliente actualizado con éxito.');
    }

    // Eliminar un cliente
    public function destroy($idcli)
    {
        $cliente = Cliente::findOrFail($idcli);
        $cliente->delete();

        return redirect()->route('clientes')->with('success', 'Cliente eliminado con éxito.');
    }
}
