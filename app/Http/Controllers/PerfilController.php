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
        $cuentas = Cuenta::with(['valor'])->orderBy('fechavencue')->get(); // Cargar valor asociado
        // Inicializar una colección vacía para los perfiles
        $perfiles = collect();

        $idcueSeleccionado = $request->idcue;

        //$usuariosActivos = ViewUsuarioActivo::where('IDCUE', $idcueSeleccionado)->get(); //por si acaso

        if ($idcueSeleccionado) {
            //$usuarioscuenta = Cuenta::where('idcue',$idcueSeleccionado)->get();
            $perfiles = Perfil::where('idcue', $idcueSeleccionado)->orderBy('idper')->get();
            foreach ($perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $idcueSeleccionado)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }
        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
        }

        // Pasar las cuentas y los perfiles a la vista
        return view('inventory.cuentas', compact('cuentas', 'perfiles', 'idcueSeleccionado'));
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
        //return redirect()->back()->with('success', 'PIN actualizado correctamente');
        return back()->with('success', 'Perfil actualizado con éxito.')->withInput()->with('focus', 'idcue');;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
