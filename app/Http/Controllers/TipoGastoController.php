<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoGasto;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;

class TipoGastoController extends Controller
{
    public function index()
    {
        $this->authorizeRole(['administrador', 'contador']);

        $tipoGastos = TipoGasto::all();

        return view('finance.gastos', compact('tipoGastos'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'contador']);

        $request->validate([
            'detalletip' => 'required|string|max:50',
        ]);

        $tipogasto = TipoGasto::create([
            'detalletip' => $request->detalletip,
        ]);

        Historial::create([
            'accion' => 'Se creo un tipo de gasto con ID: ' . $tipogasto->idtip,
            'descripcion' => null, // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto creado con éxito');
    }

    public function edit($id)
    {
        $this->authorizeRole(['administrador', 'contador']);

        $tipoGasto = TipoGasto::findOrFail($id);

        return response()->json([
            'tipoGastos' => $tipoGasto
        ]);
    }

    public function update(Request $request, $idtip)
    {
        $this->authorizeRole(['administrador', 'contador']);

        $request->validate([
            'detalletip' => 'required|string|max:50',
        ]);

        $tipoGasto = TipoGasto::findOrFail($idtip);

        Historial::create([
            'accion' => 'Se actualizo el tipo de gasto con ID: ' . $idtip,
            'descripcion' =>  'Datos antiguos: ' . json_encode($tipoGasto), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $tipoGasto->update([
            'detalletip' => $request->detalletip,
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto actualizado con éxito');
    }

    public function destroy($id)
    {
        $this->authorizeRole(['administrador', 'contador']);

        $tipoGasto = TipoGasto::findOrFail($id);

        Historial::create([
            'accion' => 'Se eliminaron los datos de el tipo de gasto con ID: ' . $id,
            'descripcion' =>  'Datos Eliminados: ' . json_encode($tipoGasto), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $tipoGasto->delete();

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto eliminado con éxito');
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
