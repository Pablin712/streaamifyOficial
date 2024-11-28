<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perfil;
class PerfilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $idcueSeleccionado = $request->input('idcue');
        
        if ($idcueSeleccionado) {
            // Obtener los perfiles de la cuenta seleccionada
            $perfiles = Perfil::where('idcue', $idcueSeleccionado)->get();
        } else {
            $perfiles = collect(); // Si no se ha seleccionado ninguna cuenta
        }

        //$cuentas = Cuenta::all(); // Para el dropdown de cuentas
        return view('invetory.cuentas.index', compact('perfiles','idcueSeleccionado'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $perfil = Perfil::findOrFail($id);
        return view('inventory.cuentas.index', compact('perfil'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'numeroper' => 'required|integer',
            'pinper' => 'required|string|min:4|max:6', // Asegurarse de que el PIN tenga entre 4 y 6 caracteres
        ]);

        // Actualizar el perfil
        $perfil = Perfil::findOrFail($id);
        $perfil->update($request->all());

        // Redirigir al listado de perfiles con mensaje de éxito
        return redirect()->route('perfil', ['idcue' => $perfil->idcue])->with('success', 'Perfil actualizado satisfactoriamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
