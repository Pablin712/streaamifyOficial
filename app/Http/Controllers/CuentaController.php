<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Perfil;
use App\Models\Costo;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        $cuentas = Cuenta::with(['valor'])->get(); // Cargar valor asociado
        // Inicializar una colección vacía para los perfiles
        $perfiles = collect();

        $idcueSeleccionado = $request->idcue;

        // Verificar si se seleccionó una cuenta para filtrar perfiles
        if ($idcueSeleccionado) {
            $perfiles = Perfil::where('idcue', $idcueSeleccionado)->get();
        }

        // Pasar las cuentas y los perfiles a la vista
        return view('inventory.cuentas.index', compact('cuentas', 'perfiles', 'idcueSeleccionado'));
    }

    // Mostrar formulario para crear una nueva cuenta contratada
    public function create()
    {
        $valores = Valor::all(); // Obtener lista de valores
        return view('inventory.cuentas.create', compact('valores'));
    }

    // Guardar una nueva cuenta
    public function store(Request $request)
    {
        //$request->validate([
        //    'idcue' => 'required|string|max:20|unique:cuentas,idcue',
        //    'idval' => 'required|exists:valores,idval',
        //    'fechavencue' => 'required|date',
        //    'usuariocue' => 'required|string|max:50',
        //    'contrasenacue' => 'required|string|max:50',
        //    'caidacue' => 'required|boolean'
        //]);
        // Validar datos de la cuenta
        $validated = $request->validate([
            'idcue' => 'required|string|max:20|unique:cuentas,idcue',
            'idval' => 'required|exists:valores,idval',
            'fechavencue' => 'required|date',
            'usuariocue' => 'required|string|max:50',
            'contrasenacue' => 'required|string|min:8|max:50',
            'caidacue' => 'required|boolean',
        ]);

        //Cuenta::create($request->all());

        // Crear la cuenta (otra alternativa)
        $cuenta = Cuenta::create($validated);
        // Verificar si se recibieron datos de costo
        if ($request->has('descripcioncos') && $request->has('montocos')) {
            // Crear el costo asociado a la cuenta
            Costo::create([
                'idcue' => $cuenta->idcue,
                'descripcioncos' => $request->descripcioncos,
                'montocos' => $request->montocos,
                'fechacos' => now(),  // O la fecha que desees
            ]);
        }
        return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
    }

    // Mostrar formulario para editar una cuenta
    public function edit($idcue)
    {
        // Buscar la cuenta con la relacion valores
        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valores = Valor::all();
        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    // Actualizar una cuenta existente
    public function update(Request $request, $idcue)
    {
        $request->validate([
            'idval' => 'required|exists:valores,idval',
            'fechavencue' => 'required|date',
            'usuariocue' => 'required|string|max:50',
            'contrasenacue' => 'required|string|max:50',
            'caidacue' => 'required|boolean|min:1'
        ]);

        $cuenta = Cuenta::findOrFail($idcue);
        $cuenta->update($request->all());

        return redirect()->route('cuentas.index')->with('success', 'Cuenta actualizada con éxito.');
    }

    // Eliminar una cuenta
    public function destroy($idcue)
    {
        $cuenta = Cuenta::findOrFail($idcue);
        $cuenta->delete();

        return redirect()->route('cuentas.index')->with('success', 'Cuenta eliminada con éxito.');
    }
}
