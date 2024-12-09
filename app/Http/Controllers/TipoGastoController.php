<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoGasto;
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

        TipoGasto::create([
            'detalletip' => $request->detalletip,
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
        $tipoGasto->update([
            'detalletip' => $request->detalletip,
        ]);

        return redirect()->route('gastos')->with('success', 'Tipo de Gasto actualizado con éxito');
    }

    public function destroy($id)
    {
        $this->authorizeRole(['administrador', 'contador']);

        $tipoGasto = TipoGasto::findOrFail($id);
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
