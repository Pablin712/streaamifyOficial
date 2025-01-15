<?php
namespace App\Http\Controllers;

use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Proveedor;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValorController extends Controller
{
    public function index()
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'vendedor', 'contador']);

        $valores = Valor::with(['proveedor', 'servicio'])->get();
        return view('inventory.valores.index', compact('valores'));
    }

    public function create()
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $proveedores = Proveedor::all();
        $servicios = Servicio::all();
        return view('inventory.valores.create', compact('servicios', 'proveedores'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $request->validate([
            'idval' => 'required|string|max:20|unique:valores,idval',
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedores,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
            'pantminval' => 'required|integer|min:1',
            'pantmaxval' => 'required|integer|min:1',
            'mesesval' => 'required|integer|min:1',
        ]);
        $request->merge([
            'idval' => strtoupper($request->idval)
        ]);
        $valor = Valor::create($request->all());

        Historial::create([
            'accion' => 'Se creo el valor con ID: ' . $valor->idval,
            'descripcion' =>  null, // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);


        return redirect()->route('valores')->with('success', 'Valor creado con éxito.');
    }

    public function edit($idval)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $valor = Valor::with(['proveedor', 'servicio'])->findOrFail($idval);
        $proveedores = Proveedor::all();
        $servicios = Servicio::all();
        return view('inventory.valores.edit', compact('valor', 'proveedores', 'servicios'));
    }

    public function update(Request $request, $idval)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $request->validate([
            'idser' => 'required|exists:servicios,idser',
            'idpro' => 'required|exists:proveedores,idpro',
            'costoval' => 'required|numeric|min:0|max:999.99',
            'pantminval' => 'required|integer|min:1',
            'pantmaxval' => 'required|integer|min:1',
            'mesesval' => 'required|integer|min:1',
        ]);
        $valor = Valor::findOrFail($idval);

        Historial::create([
            'accion' => 'Se actualizo el valor con ID: ' . $idval,
            'descripcion' =>  'Datos antiguos: ' . json_encode($valor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $valor->update($request->all());

        return redirect()->route('valores')->with('success', 'Valor actualizado con éxito.');
    }

    public function destroy($idval)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $valor = Valor::findOrFail($idval);

        Historial::create([
            'accion' => 'Se eliminaron los datos de el valor con ID: ' . $idval,
            'descripcion' =>  'Datos Eliminados: ' . json_encode($valor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $valor->delete();

        return redirect()->route('valores')->with('success', 'Valor eliminado con éxito.');
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
