<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perfil;
use App\Models\Cuenta;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Facades\Auth;
use App\Models\Historial;

class PerfilController extends Controller
{
    /*
    public function __construct() {
        $this->middleware('can:perfil.update')->only('update');
    }
    */
    public function index(Request $request)
    {
        $cuentas = Cuenta::with(['valor'])->orderBy('fechavencue')->get();
        $perfiles = collect();
        $idcueSeleccionado = $request->idcue;

        if ($idcueSeleccionado) {
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

        return view('inventory.cuentas', compact('cuentas', 'perfiles', 'idcueSeleccionado'));
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        // Validar y guardar el perfil
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
            'numeroper' => 'required|string|max:50',
            'pinper' => 'required|string|max:6',
        ]);

        $perfil = Perfil::create($request->all());

        Historial::create([
            'accion' => 'Creación de Perfil',
            'descripcion' =>  'Datos: ' . json_encode($perfil), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);


        return redirect()->route('cuentas')->with('success', 'Perfil creado con éxito.');
    }

    public function edit(string $id)
    {
        $perfil = Perfil::findOrFail($id);
        return view('inventory.cuentas.index', compact('perfil'));
    }

    public function update(Request $request, string $id)
    {
        if (!Auth::user()->hasPermissionTo('perfil.update')) {
            abort(403, 'No tienes permiso para actualizar cuentas.');
        }
        $perfil = Perfil::findOrFail($id);
        $request->validate([
            'pinper' => 'required|string|max:255',
        ]);

        Historial::create([
            'accion' => 'Actualización de Perfil',
            'descripcion' =>  'Datos antigüos: ' . json_encode($perfil), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $perfil->update([
            'pinper' => $request->input('pinper'),
        ]);

        return back()->with('success', 'Perfil actualizado con éxito.');
    }

    public function destroy(string $id)
    {
        if (Auth::user()->idrol === 'tecnico') {
            abort(403, 'Acción no autorizada para técnicos.');
        }

        $perfil = Perfil::findOrFail($id);

        Historial::create([
            'accion' => 'Eliminación de Perfil',
            'descripcion' =>  'Datos Eliminados: ' . json_encode($perfil), // Campo opcional
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $perfil->delete();

        return redirect()->route('cuentas')->with('success', 'Perfil eliminado con éxito.');
    }
}
