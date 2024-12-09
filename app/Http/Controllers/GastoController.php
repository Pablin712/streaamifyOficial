<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gasto;
use App\Models\TipoGasto;

use Illuminate\Support\Facades\Auth;
class GastoController extends Controller
{
    //public function __construct()
    //{
    //    $this->middleware('auth'); // Solo usuarios autenticados pueden acceder
    //}

    // Mostrar todos los gastos
    public function index()
    {
        
        $this->authorizeRole(['administrador', 'contador']);
        // Obtener todos los gastos con el tipo de gasto relacionado
        $gastos = Gasto::with('tipoGasto')->get();
        // Obtener todos los tipos de gasto para el formulario
        $tipoGastos = TipoGasto::all();

        return view('finance.gastos', compact('gastos', 'tipoGastos'));
    }

    // Crear un nuevo gasto desde el modal
    public function store(Request $request)
    {
        $request->validate([
            'idtip' => 'required|exists:tipo_gasto,idtip',
            'fechagas' => 'required|date',
            'montogas' => 'required|numeric',
            'descripciongas' => 'required|string|max:50',
        ]);

        Gasto::create([
            'idtip' => $request->idtip,
            'fechagas' => $request->fechagas,
            'montogas' => $request->montogas,
            'descripciongas' => $request->descripciongas,
        ]);

        return redirect()->route('gastos')->with('success', 'Gasto creado con éxito');
    }

    // Mostrar el formulario para editar un gasto (modal)
    public function edit($id)
    {
        $gasto = Gasto::findOrFail($id);
        $tipoGastos = TipoGasto::all();

        return response()->json([
            'gasto' => $gasto,
            'tipoGastos' => $tipoGastos
        ]);
    }

    // Actualizar un gasto (desde el modal)
    public function update(Request $request, $idgas)
    {
        $request->validate([
            'idtip' => 'required|exists:tipo_gasto,idtip',
            'fechagas' => 'required|date',
            'montogas' => 'required|numeric',
            'descripciongas' => 'required|string|max:50',
        ]);

        $gasto = Gasto::findOrFail($idgas);
        $gasto->update([
            'fechagas' => $request->fechagas,
            'montogas' => $request->montogas,
            'descripciongas' => $request->descripciongas,
        ]);

        return redirect()->route('gastos')->with('success', 'Gasto actualizado con éxito');
    }

    // Eliminar un gasto
    public function destroy($id)
    {
        $gasto = Gasto::findOrFail($id);
        $gasto->delete();

        return redirect()->route('gastos')->with('success', 'Gasto eliminado con éxito');
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
