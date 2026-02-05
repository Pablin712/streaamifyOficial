<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ViewUsuarioActivo;
use App\Models\DetalleVenta;
use App\Models\Cuenta;
use App\Models\Historial;
use App\Services\CuentaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UsuarioController extends Controller
{
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
    }

    public function index()
    {
        if (!Gate::allows('usuarios')) {
            abort(403, 'No tienes permiso para ver los usuarios.');
        }
        $usuarios = ViewUsuarioActivo::with(['cuenta.valor.servicio', 'cliente'])
            ->orderBy('fecha_vencimiento')
            ->orderBy('nombre_cliente')
            ->get();
        $cuentas = Cuenta::where('activocue', true)->orderBy('idcue')->get();
        return view('inventory.usuarios.index', compact('usuarios', 'cuentas'));
    }

    // Método para mostrar el formulario de cambio de usuario
    public function change($iddet)
    {
        if (!Gate::allows('usuarios.change')) {
            abort(403, 'No tienes permiso para cambiar datos de usuarios.');
        }
        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();
        $cuentas = Cuenta::with('perfiles')->where('activocue', true)->orderBy('idcue')->get();

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
        if (!Gate::allows('usuarios.update')) {
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
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $detalle->save();
        return redirect()->route('usuarios')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function moverUsuario($iddet){
        if (!Gate::allows('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }
        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

        $respuesta = $this->cuentaService->mudarClienteAOtraCuenta($usuario);
        if($respuesta == 'error'){
            return redirect()->back()->with('error', 'No se pudo mover el usuario, probablemente ya no quedan espacios');
        }
        else{
            return redirect()->back()->with('success', $respuesta);
        }
    }
    public function moverUsuarioMesa($iddet){
        if (!Gate::allows('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }
        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

        $respuesta = $this->cuentaService->mudarClienteAMesaDeTrabajo($usuario);
        if($respuesta == 'error'){
            return redirect()->back()->with('error', 'No se pudo mover el usuario, probablemente ya no quedan espacios');
        }
        else{
            return redirect()->back()->with('success', $respuesta);
        }
    }
    public function actualizarEstadoCobro(Request $request, $iddet)
    {
        try {
            $request->validate([
                'estado' => 'required|in:COBRADO,PENDIENTE'
            ]);

            $detalle = DetalleVenta::findOrFail($iddet);
            $estadoAnterior = $detalle->estado;
            $detalle->estado = $request->estado;
            $detalle->save();

            Historial::create([
                'accion' => 'Actualización Estado de Cobro',
                'descripcion' => 'Usuario ID: ' . $iddet . ' - Estado cambió de ' . $estadoAnterior . ' a ' . $request->estado,
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'estado' => $detalle->estado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($iddet)
    {
        if (!Gate::allows('usuarios.destroy')) {
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }
        $detalle = DetalleVenta::findOrFail($iddet);
        // Invertir el estado de activodet
        $detalle->activodet = !$detalle->activodet;
        $detalle->save();

        Historial::create([
            'accion' => 'Cuenta-Quitada',
            'descripcion' => 'Cliente: ' . $detalle->venta->cliente->nombrecli . ' - Usuario que se quitó: ' . json_encode($detalle),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Usuario eliminado con éxito.');
    }
    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('usuarios', []);
        $ids = array_filter($ids, 'is_numeric');
        if (!Gate::allows('usuarios.destroy')) {
            abort(403, 'No tienes permiso para eliminar usuarios.');
        }
        if (!empty($ids)) {
            $detalles = DetalleVenta::whereIn('iddet', $ids)->get();
            foreach ($detalles as $detalle) {
                $detalle->activodet = !$detalle->activodet;
                $detalle->save();

                Historial::create([
                    'accion' => 'Cuenta-Quitada',
                    'descripcion' => 'Cliente: ' . ($detalle->venta->cliente->nombrecli ?? 'N/A') . ' - Usuario que se quitó: ' . json_encode($detalle),
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);
            }
        }
        return redirect()->back()->with('success', 'Usuarios eliminados correctamente.');
    }

    /**
     * Mover usuario a un servicio diferente
     */
    public function moverUsuarioOtroServicio(Request $request, $iddet)
    {
        if (!Gate::allows('usuarios.update')) {
            abort(403, 'No tienes permiso para actualizar usuarios.');
        }

        $request->validate([
            'idser_destino' => 'required|string'
        ]);

        $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

        if (!$usuario) {
            return redirect()->back()->with('error', 'Usuario no encontrado');
        }

        $idserDestino = $request->idser_destino;

        $respuesta = $this->cuentaService->mudarClienteAOtroServicio($usuario, $idserDestino);

        if ($respuesta == 'error_no_disponible') {
            return redirect()->back()->with('error', 'No hay cuentas disponibles en el servicio ' . $idserDestino);
        } elseif ($respuesta == 'error_sin_perfil') {
            return redirect()->back()->with('error', 'No hay perfiles disponibles en las cuentas del servicio ' . $idserDestino);
        } else {
            return redirect()->back()->with('success', $respuesta);
        }
    }
}
