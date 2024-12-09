<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perfil;
use App\Models\Cuenta;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico']);

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
        $this->authorizeRole(['administrador', 'bodeguero']);
        // Lógica para crear un perfil si aplica
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        // Validar y guardar el perfil
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
            'numeroper' => 'required|string|max:50',
            'pinper' => 'required|string|max:6',
        ]);

        Perfil::create($request->all());

        return redirect()->route('cuentas')->with('success', 'Perfil creado con éxito.');
    }

    public function edit(string $id)
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico']);

        $perfil = Perfil::findOrFail($id);
        return view('inventory.cuentas.index', compact('perfil'));
    }

    public function update(Request $request, string $id)
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico']);

        $perfil = Perfil::findOrFail($id);
        $request->validate([
            'pinper' => 'required|string|max:6',
        ]);

        $perfil->update([
            'pinper' => $request->input('pinper'),
        ]);

        return back()->with('success', 'Perfil actualizado con éxito.');
    }

    public function destroy(string $id)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        if (Auth::user()->idrol === 'tecnico') {
            abort(403, 'Acción no autorizada para técnicos.');
        }

        $perfil = Perfil::findOrFail($id);
        $perfil->delete();

        return redirect()->route('cuentas')->with('success', 'Perfil eliminado con éxito.');
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
