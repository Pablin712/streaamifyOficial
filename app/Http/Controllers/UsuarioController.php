<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewUsuarioActivo;
use App\Models\DetalleVenta;
use App\Models\Cuenta;
use App\Models\Historial;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /*
    // Constructor original con middlewares, mantenido comentado para referencia:
    public function __construct() {
        $this->middleware('can:usuarios')->only('index');
        $this->middleware('can:usuarios.change')->only('change');
        $this->middleware('can:usuarios.update')->only('update');
        $this->middleware('can:usuarios.destroy')->only('destroy');
    }
    */

    public function index()
    {
        if (!Auth::user()->hasPermissionTo('usuarios')) {
            abort(403, 'No tienes permiso para ver los usuarios.');
        }
        $usuarios = ViewUsuarioActivo::orderBy('fecha_vencimiento')->orderBy('nombre_cliente')->get();
        return view('inventory.usuarios.index', compact('usuarios'));
    }

    // Método para mostrar el formulario de cambio de usuario
    public function change($iddet)
    {
        if (!Auth::user()->hasPermissionTo('usuarios.change')) {
            abort(403, 'No tienes permiso para cambiar datos de usuarios.');
        }
        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();
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
        return view('inventory.usuarios.change', compact('usuario', 'cuentas'));
    }

    public function update(Request $request, $iddet)
    {
        if (!Auth::user()->hasPermissionTo('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }
        $request->validate([
            'idcue' => 'required|exists:cuentas,idcue',
            'perfil' => 'required|integer|min:1',
            'fecha_vencimiento' => 'required'
        ]);

        $detalle = DetalleVenta::findOrFail($iddet);
        // Actualizar los campos del usuario
        $detalle->idper = $request->idcue . '.' . $request->perfil;
        $detalle->fechavendet = $request->fecha_vencimiento;
        
        Historial::create([
            'accion' => 'Actualización de Usuario',
            'descripcion' => 'Cliente: ' . $detalle->venta->cliente->nombrecli . ' - Datos antiguos: ' . json_encode($detalle),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        $detalle->save();
        return redirect()->route('usuarios')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy($iddet)
    {
        if (!Auth::user()->hasPermissionTo('usuarios.destroy')) {
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }
        $detalle = DetalleVenta::findOrFail($iddet);
        // Invertir el estado de activodet
        $detalle->activodet = !$detalle->activodet;
        $detalle->save();

        Historial::create([
            'accion' => 'Cuenta-Quitada',
            'descripcion' => 'Cliente: ' . $detalle->venta->cliente->nombrecli . ' - Usuario que se quitó: ' . json_encode($detalle),
            'realizado_por' => Auth::user()->nombreemp . ' | ' . request()->ip(),
            'fecha' => now(),
        ]);

        return redirect()->route('usuarios')->with('success', 'Usuario eliminado con éxito.');
    }
}