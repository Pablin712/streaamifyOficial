<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perfil;
use App\Models\Cuenta;
use App\Models\ViewUsuarioActivo;
class PerfilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Fetch all cuentas, with 'valor' relationship, ordered by 'fechavencue'
    $cuentas = Cuenta::with(['valor'])->orderBy('fechavencue')->get();

    // Get the selected account (if any)
    $idcueSeleccionado = $request->input('idcue');

    // Initialize perfiles collection as empty by default
    $perfiles = collect();

    if ($idcueSeleccionado) {
        // If a specific account is selected, fetch its profiles
        $perfiles = Perfil::where('idcue', $idcueSeleccionado)->get();
        
        // Loop through each profile to add the 'usuarios_activos' count
        foreach ($perfiles as $perfil) {
            $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                ->where('idcue', $idcueSeleccionado)
                ->count();
            $perfil->usuarios_activos = $usuariosActivos;
        }
    }

    // Loop through each cuenta to add the 'usuarios_activos' count
    foreach ($cuentas as $cuenta) {
        $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
        $cuenta->usuarios_activos = $usuarios;
    }

    // Pass both cuentas and perfiles to the view
    return view('inventory.cuentas.index', compact('cuentas', 'perfiles', 'idcueSeleccionado'));
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
        // Buscar el perfil por ID
        $perfil = Perfil::findOrFail($id);
        $request->validate([
            'pinper' => 'required|string|max:6', // Asegúrate de ajustar la validación según tus necesidades
        ]);

        // Actualizar el PIN
        $perfil->pinper = $request->input('pinper');
        $perfil->save(); // Guardar los cambios

        // Redirigir con un mensaje de éxito
        return redirect()->route('cuentas')->with('success', 'PIN actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
