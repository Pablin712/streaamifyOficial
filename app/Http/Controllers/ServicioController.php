<?php
namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicioController extends Controller
{
    public function index()
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'vendedor', 'contador']);

        $servicios = Servicio::all();
        return view('inventory.servicios.index', compact('servicios'));
    }

    public function create()
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        return view('inventory.servicios.create');
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

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
            'idser' => strtoupper($request->nombreser),
            'nombreser' => ucwords(strtolower($request->nombreser))
        ]);

        Servicio::create($request->all());

        return redirect()->route('servicios')->with('success', 'Servicio creado con éxito.');
    }

    public function edit($idser)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $servicio = Servicio::findOrFail($idser);
        return view('inventory.servicios.edit', compact('servicio'));
    }

    public function update(Request $request, $idser)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

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
        $servicio->update($request->all());

        return redirect()->route('servicios')->with('success', 'Servicio actualizado con éxito.');
    }

    public function destroy($idser)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $servicio = Servicio::findOrFail($idser);
        $servicio->delete();

        return redirect()->route('servicios')->with('success', 'Servicio eliminado con éxito.');
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
