<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Cuenta;
use Illuminate\Http\Request;

class CostoController extends Controller
{
    public function index(Request $request)
    {
        // Obtener todas las cuentas para el selector
        $cuentas = Cuenta::all();

        // Filtrar costos por cuenta seleccionada
        $idcueSeleccionado = $request->idcue; // ID de la cuenta seleccionada
        //$costos = $idcueSeleccionado
        //  ? Costo::where('idcue', $idcueSeleccionado)->get()
        // : collect(); // Colección vacía si no se selecciona ninguna cuenta
        $costos = Costo::all();
        return view('finance.costos', compact('cuentas', 'costos', 'idcueSeleccionado'));
    }

    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue', // Debe ser una cuenta válida
            'descripcioncos' => 'required|string|max:50',
            'montocos' => 'required|numeric|min:0',
            'fechacos' => 'nullable|date'
        ]);

        // Crear un nuevo costo
        Costo::create($request->all());

        return redirect()->route('costos', ['idcue' => $request->idcue])->with('success', 'Costo creado correctamente.');
    }

    public function update(Request $request, $idcos)
    {
        $request->validate([
            'descripcioncos' => 'required|string|max:50',
            'montocos' => 'required|numeric',
            'fechacos' => 'required|date',
        ]);

        $costo = Costo::findOrFail($idcos);
        $costo->update([
            'descripcioncos' => $request->descripcioncos,
            'montocos' => $request->montocos,
            'fechacos' => $request->fechacos,
        ]);

        return redirect()->route('costos')->with('success', 'Costo actualizado con éxito.');
    }

    public function destroy($idcos)
    {
        // Eliminar el costo
        $costo = Costo::findOrFail($idcos);
        $idcue = $costo->idcue; // Para volver a la cuenta seleccionada
        $costo->delete();

        return redirect()->route('costos', ['idcue' => $idcue])->with('success', 'Costo eliminado correctamente.');
    }
}
