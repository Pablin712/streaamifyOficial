<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;
use Illuminate\Support\Facades\Auth;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico']);

        $cuentas = Cuenta::with(['valor'])->orderBy('fechavencue')->get();
        $perfiles = collect();
        $idcueSeleccionado = $request->idcue;

        if ($idcueSeleccionado) {
            $perfiles = Perfil::where('idcue', $idcueSeleccionado)->get();
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

        return view('inventory.cuentas.index', compact('cuentas', 'perfiles', 'idcueSeleccionado'));
    }

    public function create()
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $valores = Valor::all();
        return view('inventory.cuentas.create', compact('valores'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $validated = $request->validate([
            'idcue' => 'required|string|max:20|unique:cuentas,idcue',
            'idval' => 'required|exists:valores,idval',
            'fechavencue' => 'required|date',
            'usuariocue' => 'required|string|max:50|unique:cuentas,idcue',
            'contrasenacue' => 'required|string|min:8|max:50',
            'caidacue' => 'required|boolean',
        ]);
        $request->merge(['idcue' => strtoupper($request->idcue)]);

        $cuenta = Cuenta::create($validated);

        if (!empty($request->descripcioncos) && !empty($request->montocos)) {
            $request->validate([
                'descripcioncos' => 'string|max:50',
                'montocos' => 'numeric|min:0',
            ]);

            Costo::create([
                'idcue' => $cuenta->idcue,
                'descripcioncos' => $request->descripcioncos,
                'montocos' => $request->montocos,
                'fechacos' => now(),
            ]);
        }
        return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
    }

    public function edit($idcue)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $cuenta = Cuenta::with(['valor'])->findOrFail($idcue);
        $valores = Valor::all();
        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    public function update(Request $request, $idcue)
    {
        $this->authorizeRole(['administrador', 'bodeguero', 'tecnico']);

        $request->validate([
            'idval' => 'required|exists:valores,idval',
            'fechavencue' => 'required|date',
            'usuariocue' => 'required|string|max:50',
            'contrasenacue' => 'required|string|max:50',
            'caidacue' => 'required|boolean|min:1'
        ]);

        $cuenta = Cuenta::findOrFail($idcue);
        $cuenta->update($request->all());

        if (!empty($request->descripcioncos) && !empty($request->montocos)) {
            $request->validate([
                'descripcioncos' => 'string|max:50',
                'montocos' => 'numeric|min:0',
            ]);

            Costo::create([
                'idcue' => $cuenta->idcue,
                'descripcioncos' => $request->descripcioncos,
                'montocos' => $request->montocos,
                'fechacos' => now(),
            ]);
        }

        return redirect()->route('cuentas')->with('success', 'Cuenta actualizada con éxito.');
    }

    public function destroy($idcue)
    {
        $this->authorizeRole(['administrador', 'bodeguero']);

        $cuenta = Cuenta::findOrFail($idcue);
        $cuenta->delete();

        return redirect()->route('cuentas')->with('success', 'Cuenta eliminada con éxito.');
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
