<?php
namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Historial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicioController extends Controller
{
    public function __construct() {
        $this->middleware('can:servicios')->only('index');
        $this->middleware('can:servicios.store')->only('create', 'store');
        $this->middleware('can:servicios.update')->only('edit', 'update');
        $this->middleware('can:servicios.destroy')->only('destroy');
    }
    public function index()
    {
        $servicios = Servicio::all();
        return view('inventory.servicios.index', compact('servicios'));
    }

    public function create()
    {
        return view('inventory.servicios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'idser' => 'required|string|max:10|unique:servicios,idser',
            'nombreser' => 'required|string|max:20',
            'completoser' => 'nullable|numeric',
            'precioser' => 'nullable|numeric',
            'comboser' => 'nullable|numeric',
            'reventaser' => 'nullable|numeric',
            'revcompser' => 'nullable|numeric',
        ]);

        $request->merge([
            'idser' => strtoupper($request->idser),
            'nombreser' => ucwords(strtolower($request->nombreser))
        ]);

        $servicio = Servicio::create($request->all());

        Historial::create([
            'accion' => 'Creación de Servicio',
            'descripcion' =>  'Datos del servicio: '. json_encode($servicio), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        return redirect()->route('servicios')->with('success', 'Servicio creado con éxito.');
    }

    public function edit($idser)
    {
        $servicio = Servicio::findOrFail($idser);
        return view('inventory.servicios.edit', compact('servicio'));
    }

    public function update(Request $request, $idser)
    {
        $request->validate([
            'nombreser' => 'required|string|max:20',
            'completoser' => 'nullable|numeric',
            'precioser' => 'nullable|numeric',
            'comboser' => 'nullable|numeric',
            'reventaser' => 'nullable|numeric',
            'revcompser' => 'nullable|numeric',
        ]);

        $request->merge([
            'nombreser' => ucwords(strtolower($request->nombreser))
        ]);

        $servicio = Servicio::findOrFail($idser);

        Historial::create([
            'accion' => 'Actualización de Servicio',
            'descripcion' =>  'Datos antigüos: ' . json_encode($servicio), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $servicio->update($request->all());

        return redirect()->route('servicios')->with('success', 'Servicio actualizado con éxito.');
    }

    public function destroy($idser)
    {
        $servicio = Servicio::findOrFail($idser);

        Historial::create([
            'accion' => 'Eliminación de Servicio',
            'descripcion' =>  'Datos Eliminados: ' . json_encode($servicio), // Campo opcional
            'realizado_por' => Auth::user()->nombreemp.' | '. request()->ip(), // Almacena el nombre del usuario
            'fecha' => now(),
        ]);

        $servicio->delete();

        return redirect()->route('servicios')->with('success', 'Servicio eliminado con éxito.');
    }
}
