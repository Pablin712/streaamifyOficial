<?php

namespace App\Http\Controllers;

use App\Mail\facturaMail;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Cuenta;
use App\Models\Recarga;
use App\Models\Pedido;
use App\Models\Historial;
use App\Models\ViewUsuarioActivo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
class VentaController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('ventas')) {
            abort(403, 'No tienes permiso para ver las ventas.');
        }

        $ventas = Venta::with(['detalles_venta'])->orderBy('created_at', 'desc')->get();

        $hoy = Carbon::today();
        $ingresos_dia = Venta::whereDate('fechaven', $hoy)->sum('totalpagoven');
        $ventas_dia = Venta::whereDate('fechaven', $hoy)->count();

        $autenticados = Cliente::whereNotNull('email')
            ->whereNotNull('password')
            ->count();

        $recargasPendientes = Recarga::where('idestado', 1)->count();
        $pedidosPendientes = Pedido::where('idestado', 1)->count();
        $ventasLaravel = Venta::whereDate('fechaven', $hoy)->where('idemp', 10)->count();

        return view('sales.ventas.index', compact(
            'ventas',
            'ingresos_dia',
            'ventas_dia',
            'autenticados',
            'recargasPendientes',
            'pedidosPendientes',
            'ventasLaravel'
        ));
    }

    public function create()
    {
        if (!Gate::allows('ventas.store')) {
            abort(403, 'No tienes permiso para crear ventas.');
        }
        $clientes = Cliente::all();
        $empleados = Empleado::all();
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
        return view('sales.ventas.create', compact('empleados', 'clientes', 'cuentas'));
    }

    public function store(Request $request)
    {
        if (!Gate::allows('ventas.store')) {
            abort(403, 'No tienes permiso para crear ventas.');
        }
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);

        $detalles = json_decode($request->detalles_venta, true);
        $total_venta = collect($detalles)->sum('monto');
        $fecha = Carbon::today()->toDateString();

        $venta = Venta::create([
            'idcli' => $request->idcli,
            'idemp' => $request->idemp,
            'fechaven' => $fecha,
            'totalpagoven' => $total_venta,
        ]);

        $venta->idven = DB::table('ventas')->where('idcli', $request->idcli)
            ->where('idemp', $request->idemp)
            ->where('fechaven', $fecha)
            ->orderBy('idven', 'desc')
            ->value('idven');

        $descripcionDetalles = "";
        $totalDetalles = count($detalles);
        $totalVenta = 0.00;
        foreach ($detalles as $detalle) {
            $idcue = $detalle['cuenta'];
            $numeroper = $detalle['perfil'];
            $idper = $idcue . '.' . $numeroper;

            $detalleRec = \App\Models\DetalleVenta::create([
                'idven' => $venta->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => true,
            ]);

            $totalVenta += $detalleRec->montodet;
            $descripcionDetalles .= "Cuenta: {$idcue}, Perfil: {$numeroper}, Monto: {$detalleRec->montodet}; ";
        }
        $descripcionDetalles .= "Cuentas vendidas: {$totalDetalles}. Total de la venta: {$totalVenta}.";
        // Lógica para generar y enviar la factura por correo
        if ($venta->cliente->email) {
            Mail::to($venta->cliente->email)->send(new facturaMail($venta));
        }
        Historial::create([
            'accion' => 'Venta-Realizada Factura: ' . $venta->idven,
            'descripcion' => 'Datos: ' . json_encode($venta) . ' Detalles: ' . $descripcionDetalles,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        // Verificar si el cliente ya compró
        if (!$venta->cliente->ya_compro) {
            $venta->cliente->ya_compro = true; // Marcar como que ya compró
            $venta->cliente->save();

            // Si tiene un referidor, darle $1 de saldo
            if ($venta->cliente->referido_por) {
                $referidor = Cliente::find($venta->cliente->referido_por);
                if ($referidor) {
                    $referidor->saldo += 1;
                    $referidor->save();

                    // Registrar en el historial
                    Historial::create([
                        'accion' => 'Bonificación por Referido',
                        'descripcion' => 'Se otorgó $1 al cliente ' . $referidor->nombrecli . ' por referir al cliente ' . $venta->cliente->nombrecli,
                        'empleado_id' => Empleado::where('nombreemp', 'Laravel')->value('idemp'), // ID del empleado que maneja el sistema
                        'created_at' => now(),
                    ]);
                }
            }
        }

        return redirect()->route('ventas.create')->with('success', 'Venta registrada correctamente');
    }

    public function storeRenew(Request $request)
    {
        if (!Gate::allows('ventas.storeRenew')) {
            abort(403, 'No tienes permiso para renovar ventas.');
        }
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);

        $idvenPasado = $request->idvenPasado;
        \App\Models\DetalleVenta::where('idven', $idvenPasado)->update(['activodet' => false]);
        $detalles = json_decode($request->detalles_venta, true);
        $total_venta = collect($detalles)->sum('monto');
        $fecha = Carbon::today()->toDateString();

        $ventaNueva = Venta::create([
            'idcli' => $request->idcli,
            'idemp' => $request->idemp,
            'fechaven' => $fecha,
            'totalpagoven' => $total_venta,
        ]);
        $ventaNueva->idven = DB::table('ventas')->where('idcli', $request->idcli)
            ->where('idemp', $request->idemp)
            ->where('fechaven', $fecha)
            ->orderBy('idven', 'desc')
            ->value('idven');
        if ($ventaNueva->cliente->email) {
            Mail::to($ventaNueva->cliente->email)->send(new facturaMail($ventaNueva));
        }

        Historial::create([
            'accion' => 'Renovación-Venta ' . $idvenPasado,
            'descripcion' => 'Nueva venta creada: ' . json_encode($ventaNueva),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        foreach ($detalles as $detalle) {
            $idcue = $detalle['cuenta'];
            $numeroper = $detalle['perfil'];
            $idper = $idcue . '.' . $numeroper;
            \App\Models\DetalleVenta::create([
                'idven' => $ventaNueva->idven,
                'idper' => $idper,
                'descripciondet' => $detalle['descripcion'],
                'fechavendet' => $detalle['fecha_vencimiento'],
                'montodet' => $detalle['monto'],
                'activodet' => true,
            ]);
        }

        return redirect()->route('usuarios')->with('success', 'Venta renovada correctamente');
    }

    public function storeCliente(Request $request)
    {
        if (!Gate::allows('ventas.storeCliente')) {
            abort(403, 'No tienes permiso para crear clientes desde ventas.');
        }
        $request->validate([
            'nombrecli' => 'required|string|max:50|unique:clientes,nombrecli',
            'telefonocli' => 'string|max:15|unique:clientes,telefonocli'
        ]);

        $clienteExistente = Cliente::where('nombrecli', $request->nombrecli)
            ->orWhere('telefonocli', $request->telefonocli)
            ->first();

        if ($clienteExistente) {
            return redirect()->route('ventas.create')
                ->with('error', 'Este cliente ya existe. Verifica los valores de nombre o teléfono.');
        } else {
            $cliente = Cliente::create($request->all());
            Historial::create([
                'accion' => 'Creación de Cliente en Ventas',
                'descripcion' => 'Datos: ' . json_encode($cliente),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);
            return redirect()->route('ventas.create')->with('success', 'Cliente creado con éxito.');
        }
    }

    public function show(string $id)
    {
        // Implementa si es necesario
    }

    public function edit($idven)
    {
        if (!Gate::allows('ventas.update')) {
            abort(403, 'No tienes permiso para editar ventas.');
        }
        $venta = Venta::with(['detalles_venta'])->findOrFail($idven);
        $empleados = Empleado::all();
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
        return view('sales.ventas.edit', compact('venta', 'empleados', 'cuentas'));
    }

    public function renew($idcli, $idven)
    {
        if (!Gate::allows('ventas.renew')) {
            abort(403, 'No tienes permiso para renovar ventas.');
        }
        $venta = Venta::with(['detalles_venta', 'cliente'])->findOrFail($idven);
        $empleados = Empleado::all();
        $cuentas = Cuenta::with('perfiles')->where('activocue', true)->orderBy('idcue')->get();

        if ($venta->idcli != $idcli) {
            abort(404, 'Cliente no coincide con la venta.');
        }

        $cuentas = Cuenta::with('perfiles')->get();
        $detalles = $venta->detalles_venta->map(function ($detalle) {
            $detalle->fechavendet_suma = Carbon::parse($detalle->fechavendet)->addMonth()->format('Y-m-d');
            return $detalle;
        });

        $totalVenta = $venta->detalles_venta->sum('montodet');

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

        return view('sales.ventas.renew', [
            'empleados' => $empleados,
            'cuentas' => $cuentas,
            'venta' => $venta,
            'detalles' => $detalles,
            'totalVenta' => $totalVenta
        ]);
    }

    public function update(Request $request, $idven)
    {
        if (!Gate::allows('ventas.update')) {
            abort(403, 'No tienes permiso para actualizar ventas.');
        }
        $request->validate([
            'idcli' => 'required|exists:clientes,idcli',
            'idemp' => 'required|exists:empleados,idemp',
            'detalles_venta' => 'required|json',
        ]);

        $venta = Venta::findOrFail($idven);
        if ($venta->idcli != $request->idcli) {
            return redirect()->route('ventas.edit', $idven)->with('error', 'El cliente no puede modificarse.');
        }
        $venta->totalpagoven = 0;
        $venta->save();

        $detalles = json_decode($request->detalles_venta, true);

        $venta->detalles_venta()->delete();

        $totalVenta = 0;
        foreach ($detalles as $detalle) {
            $idcue = $detalle['cuenta'];
            $numeroper = $detalle['perfil'];
            $idper = $idcue . '.' . $numeroper;
            \App\Models\DetalleVenta::create([
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

        Historial::create([
            'accion' => 'Actualización de venta ' . $venta->idven,
            'descripcion' => 'Datos: ' . json_encode($venta),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('ventas')->with('success', 'Venta actualizada correctamente');
    }

    public function status($iddet)
    {
        if (!Gate::allows('ventas.status')) {
            abort(403, 'No tienes permiso para actualizar el estado de la venta.');
        }
        $detalle = \App\Models\DetalleVenta::findOrFail($iddet);
        $detalle->activodet = !$detalle->activodet;
        $detalle->save();

        Historial::create([
            'accion' => 'Estado de usuario actualizado ' . $iddet,
            'descripcion' => 'Estado cambiado a ' . $detalle->activodet,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return redirect()->route('ventas')->with('success', 'Estado de la cuenta del cliente actualizado correctamente.');
    }

    public function sendInvoice($id)
    {
        if (!Gate::allows('ventas.sendInvoice')) {
            abort(403, 'No tienes permiso para enviar facturas.');
        }
        $venta = Venta::findOrFail($id);
        $cliente = $venta->cliente;
        Mail::to($cliente->email)->send(new facturaMail($venta));
        return redirect()->route('ventas')->with('success', 'Factura enviada correctamente.');
    }

    public function destroy($idven)
    {
        if (!Gate::allows('ventas.destroy')) {
            abort(403, 'No tienes permiso para eliminar ventas.');
        }
        $venta = Venta::findOrFail($idven);
        Historial::create([
            'accion' => 'Eliminación de Venta',
            'descripcion' => 'Datos Eliminados: ' . json_encode($venta),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        $venta->detalles_venta()->delete();
        $venta->delete();
        return redirect()->route('ventas')->with('success', 'Venta eliminada con éxito.');
    }

    public function indexApi(Request $request)
    {
        $ventas = Venta::with(['detalles_venta'])->orderBy('fechaven', 'desc')->get();
        $hoy = Carbon::today();
        $ingresos_dia = Venta::whereDate('fechaven', $hoy)->sum('totalpagoven');
        $ventas_dia = Venta::whereDate('fechaven', $hoy)->count();
        return response()->json([
            'ventas' => $ventas,
            'ingresos_dia' => $ingresos_dia,
            'ventas_dia' => $ventas_dia,
        ]);
    }
}
