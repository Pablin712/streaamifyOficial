<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostoController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'contador']);

        // Obtener todas las cuentas para el selector
        $cuentas = Cuenta::all();

        // Filtrar costos por cuenta seleccionada
        $idcueSeleccionado = $request->idcue;
        $costos = Costo::all();

        return view('finance.costos', compact('cuentas', 'costos', 'idcueSeleccionado'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador']);

        // Validar los datos
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
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
        $this->authorizeRole(['administrador']);

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
        $this->authorizeRole(['administrador']);

        $costo = Costo::findOrFail($idcos);
        $idcue = $costo->idcue;
        $costo->delete();

        return redirect()->route('costos', ['idcue' => $idcue])->with('success', 'Costo eliminado correctamente.');
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
