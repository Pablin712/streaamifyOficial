<?php

namespace App\Http\Controllers;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ValorController extends Controller
{
    public function index()
    {
        $valores = Valor::with(['proveedor', 'servicio'])->get(); // Cargar proveedor y servicio asociados
        return view('inventory.valores.index', compact('valores'));
    }

    // Mostrar formulario para crear un nuevo valor
    public function create()
    {
        $proveedores = Proveedor::all(); // Obtener lista de proveedores
        $servicios = Servicio::all();
        return view('inventory.valores.create', compact('servicios','proveedores'));
    }

    // Guardar un nuevo valor
    public function store(Request $request)
    {
        $request->validate([
            'idval' => 'required|string|max:20|unique:valores,idval',
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedors,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
            'pantminval' => 'required|integer|min:1',
            'pantmaxval' => 'required|integer|min:1',
            'mesesval' => 'required|integer|min:1',
        ]);

        Valor::create($request->all());

        return redirect()->route('valores.index')->with('success', 'Valor creado con éxito.');
    }

    // Mostrar formulario para editar un valor
    public function edit($idval)
    {
        // Buscar el valor con las relaciones 'proveedor' y 'servicio'
        $valor = Valor::with(['proveedor', 'servicio'])->findOrFail($idval);
        $proveedores = Proveedor::all();
        $servicios = Servicio::all();
        return view('inventory.valores.edit', compact('valor', 'proveedores', 'servicios'));
    }

    // Actualizar un valor existente
    public function update(Request $request, $idval)
    {
        $request->validate([
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedors,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
            'pantminval' => 'required|integer|min:1',
            'pantmaxval' => 'required|integer|min:1',
            'mesesval' => 'required|integer|min:1',
        ]);

        $valor = Valor::findOrFail($idval);
        $valor->update($request->all());

        return redirect()->route('valores.index')->with('success', 'Valor actualizado con éxito.');
    }

    // Eliminar un valor
    public function destroy($idval)
    {
        $valor = Valor::findOrFail($idval);
        $valor->delete();

        return redirect()->route('valores.index')->with('success', 'Valor eliminado con éxito.');
    }
}
