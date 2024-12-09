<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Cuenta;
use App\Models\Perfil;
use App\Models\ViewUsuarioActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeRole(['administrador', 'vendedor', 'contador']);

        $ventas = Venta::with(['detalles_venta'])->orderBy('fechaven')->get();

        return view('sales.ventas.index', compact('ventas'));
    }

    public function create()
    {
        $this->authorizeRole(['administrador', 'vendedor']);

        $clientes = Cliente::all();
        $empleados = Empleado::all();
        $cuentas = Cuenta::with('perfiles')->orderBy('idcue')->get();

        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            foreach ($cuenta->perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $cuenta->idcue)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }

        return view('sales.ventas.create', compact('empleados', 'clientes', 'cuentas'));
    }

    public function store(Request $request)
    {
        $this->authorizeRole(['administrador', 'vendedor']);

        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);

        $venta = Venta::create([
            'idcli' => $request->idcli,
            'idemp' => $request->idemp,
            'fechaven' => Carbon::now(),
            'totalpagoven' => 0,
        ]);

        $detalles = json_decode($request->detalles_venta, true);

        foreach ($detalles as $detalle) {
            $idcue = $detalle['cuenta'];
            $numeroper = $detalle['perfil'];
            $idper = $idcue . '.' . $numeroper;

            DetalleVenta::create([
                'idven' => $venta->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => true,
            ]);
        }

        return redirect()->route('ventas')->with('success', 'Venta registrada correctamente');
    }

    public function edit($idven)
    {
        $this->authorizeRole(['administrador', 'vendedor']);

        $venta = Venta::with(['detalles_venta'])->findOrFail($idven);
        $empleados = Empleado::all();
        $cuentas = Cuenta::with('perfiles')->orderBy('idcue')->get();

        foreach ($cuentas as $cuenta) {
            $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();
            $cuenta->usuarios_activos = $usuarios;
            foreach ($cuenta->perfiles as $perfil) {
                $usuariosActivos = ViewUsuarioActivo::where('perfil', $perfil->numeroper)
                    ->where('idcue', $cuenta->idcue)
                    ->count();
                $perfil->usuarios_activos = $usuariosActivos;
            }
        }

        return view('sales.ventas.edit', compact('venta', 'empleados', 'cuentas'));
    }

    public function update(Request $request, $idven)
    {
        $this->authorizeRole(['administrador', 'vendedor']);

        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);

        $venta = Venta::findOrFail($idven);

        if ($venta->idcli != $request->idcli) {
            return redirect()->route('ventas.edit', $idven)->with('error', 'El cliente no puede modificarse.');
        }

        $venta->fechaven = Carbon::now();
        $venta->totalpagoven = 0;
        $venta->save();

        $venta->detalles_venta()->delete();

        $totalVenta = 0;
        $detalles = json_decode($request->detalles_venta, true);

        foreach ($detalles as $detalle) {
            $idcue = $detalle['cuenta'];
            $numeroper = $detalle['perfil'];
            $idper = $idcue . '.' . $numeroper;

            DetalleVenta::create([
                'idven' => $venta->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => $detalle['estado'],
            ]);

            $totalVenta += $detalle['monto'];
        }

        $venta->totalpagoven = $totalVenta;
        $venta->save();

        return redirect()->route('ventas')->with('success', 'Venta actualizada correctamente');
    }

    public function destroy($idven)
    {
        $this->authorizeRole(['administrador']);

        $venta = Venta::findOrFail($idven);
        $venta->delete();

        return redirect()->route('ventas')->with('success', 'Venta eliminada con éxito.');
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
