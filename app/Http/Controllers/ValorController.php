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
    public function __construct() {
        $this->middleware('can:valores')->only('index');
        $this->middleware('can:valores.store')->only('create', 'store');
        $this->middleware('can:valores.update')->only('edit', 'update');
        $this->middleware('can:valores.destroy')->only('destroy');
    }
    public function index()
    {
        $valores = Valor::with(['proveedor', 'servicio'])->get();
        return view('inventory.valores.index', compact('valores'));
    }

    public function create()
    {
        $proveedores = Proveedor::all();
        $servicios = Servicio::all();
        return view('inventory.valores.create', compact('servicios', 'proveedores'));
    }

    public function store(Request $request)
    {
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
            'accion' => 'Creación de Valor',
            'descripcion' =>  'Datos: ' . json_encode($valor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);
        return redirect()->route('valores')->with('success', 'Valor creado con éxito.');
    }

    public function edit($idval)
    {
        $valor = Valor::with(['proveedor', 'servicio'])->findOrFail($idval);
        $proveedores = Proveedor::all();
        $servicios = Servicio::all();
        return view('inventory.valores.edit', compact('valor', 'proveedores', 'servicios'));
    }

    public function update(Request $request, $idval)
    {
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
            'accion' => 'Actualización de Valor',
            'descripcion' =>  'Datos antiguos: ' . json_encode($valor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $valor->update($request->all());

        return redirect()->route('valores')->with('success', 'Valor actualizado con éxito.');
    }

    public function destroy($idval)
    {
        $valor = Valor::findOrFail($idval);

        Historial::create([
            'accion' => 'Eliminación de Valor',
            'descripcion' =>  'Datos Eliminados: ' . json_encode($valor), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $valor->delete();

        return redirect()->route('valores')->with('success', 'Valor eliminado con éxito.');
    }
}
