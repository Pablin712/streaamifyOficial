<?php

namespace App\Http\Controllers;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::all();
        return view('inventory.servicios.index', compact('servicios'));
    }
    // Crear un nuevo servicio
    public function create()
    {
        return view('inventory.servicios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'idser' => 'required|string|max:10|unique:servicios,idser',
            'nombreser' => 'required|string|max:20',
            'completoser' => 'nullable|numeric',
            'precioser' => 'nullable|numeric',
            'comboser' => 'nullable|numeric',
            'reventaser' => 'nullable|numeric',
            'revcompser' => 'nullable|numeric',
        ]);

        Servicio::create($request->all());

        return redirect()->route('servicios')->with('success', 'Servicio creado con éxito.');
    }

    // Editar un servicio existente
    public function edit($idser)
    {
        $servicio = Servicio::findOrFail($idser);
        return view('inventory.servicios.index', compact('servicio'));
    }

    public function update(Request $request, $idser)
    {
        $request->validate([
            'nombreser' => 'required|string|max:20', // varchar(20)
            'completoser' => 'nullable|numeric',
            'precioser' => 'nullable|numeric',
            'comboser' => 'nullable|numeric',
            'reventaser' => 'nullable|numeric',
            'revcompser' => 'nullable|numeric',
        ]);

        $servicio = Servicio::findOrFail($idser);
        $servicio->update($request->all());

        return redirect()->route('servicios')->with('success', 'Servicio actualizado con éxito.');
    }

    // Eliminar un servicio
    public function destroy($idser)
    {
        $servicio = Servicio::findOrFail($idser);
        $servicio->delete();

        return redirect()->route('servicios')->with('success', 'Servicio eliminado con éxito.');
    }
}
