<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use App\Models\Cuenta;
use Illuminate\Http\Request;

use App\Models\Historial;
use Illuminate\Support\Facades\Auth;

class CostoController extends Controller
{
    public function index(Request $request)
    {
        
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico','contador']);
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
        $costo = Costo::create($request->all());

        Historial::create([
            'accion' => 'Se creo el costo con ID: ' . $costo->idcos,
            'descripcion' =>  'Datos: ' . json_encode($costo), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);


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

        Historial::create([
            'accion' => 'Se actualizo el costo con ID: ' . $costo->idcli,
            'descripcion' =>  'Datos antiguos : ' . json_encode($costo), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

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

        Historial::create([
            'accion' => 'Se eliminaron datos de el costo con ID: ' . $costo->idcos,
            'descripcion' =>  'Datos Eliminados: ' . json_encode($costo), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp, // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

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
