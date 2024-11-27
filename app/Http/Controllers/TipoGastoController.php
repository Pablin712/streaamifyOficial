<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoGasto;
class TipoGastoController extends Controller
{
    public function index()
    {
        $tipoGastos = TipoGasto::with('tipoGasto')->get();

        return view('finance.gastos', compact('tipoGastos'));
    }

    // Crear un nuevo tipogasto desde el modal
    public function store(Request $request)
    {
        $request->validate([
            'detalletip' => 'required|string|max:50',
        ]);

        TipoGasto::create([
            'detalletip' => $request->detalletip,
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto creado con éxito');
    }

    // Mostrar el formulario para editar un tipo gasto (modal)
    public function edit($id)
    {
        $tipoGastos = TipoGasto::findOrFail($id);

        return response()->json([
            'tipoGastos' => $tipoGastos
        ]);
    }

    // Actualizar un tipo de gasto (desde el modal)
    public function update(Request $request, $idtip)
    {
        $request->validate([
            'detalletip' => 'required|string|max:50',
        ]);

        $tipoGasto = TipoGasto::findOrFail($idtip);
        $tipoGasto->update([
            'detalletip' => $request->detalletip,
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto actualizado con éxito');
    }

    // Eliminar un gasto
    public function destroy($id)
    {
        $tipoGasto = TipoGasto::findOrFail($id);
        $tipoGasto->delete();

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto eliminado con éxito');
    }
}
