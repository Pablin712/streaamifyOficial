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
use App\Models\Banco;
use App\Services\BancoService;
use App\Services\EntregaMensajeService;
use App\Services\TareaService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
class VentaController extends Controller
{
    protected $bancoService;
    protected $entregaMensajeService;

    public function __construct(BancoService $bancoService, EntregaMensajeService $entregaMensajeService)
    {
        $this->bancoService = $bancoService;
        $this->entregaMensajeService = $entregaMensajeService;
    }

    public function index(Request $request)
    {
        if (!Gate::allows('ventas')) {
            abort(403, 'No tienes permiso para ver las ventas.');
        }

        // Si es petición AJAX, retornar datos paginados
        if ($request->ajax() || $request->has('ajax')) {
            return $this->getVentasAjax($request);
        }

        // Vista normal: solo estadísticas, NO cargar todas las ventas
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
            'ingresos_dia',
            'ventas_dia',
            'autenticados',
            'recargasPendientes',
            'pedidosPendientes',
            'ventasLaravel'
        ));
    }

    /**
     * Obtener ventas paginadas para AJAX (Enhanced Table v2 Server-side)
     */
    private function getVentasAjax(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', '');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = Venta::with(['cliente', 'empleado', 'banco']);

        // Búsqueda
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('idven', 'like', "%{$search}%")
                    ->orWhereHas('cliente', function ($q2) use ($search) {
                        $q2->where('nombrecli', 'like', "%{$search}%");
                    })
                    ->orWhereHas('empleado', function ($q2) use ($search) {
                        $q2->where('nombreemp', 'like', "%{$search}%");
                    });
            });
        }

        // Ordenamiento
        $validSortColumns = ['idven' => 'idven', 'fechaven' => 'fechaven', 'totalpagoven' => 'totalpagoven'];
        if ($sortBy !== '' && isset($validSortColumns[$sortBy])) {
            $query->orderBy($validSortColumns[$sortBy], $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $totalRecords = $query->count();
        $ventas = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Renderizar HTML de las filas
        $html = view('sales.ventas.partials.table-rows', compact('ventas'))->render();

        return response()->json([
            'html' => $html,
            'total_records' => $totalRecords,
            'current_page' => $page,
            'per_page' => $perPage
        ]);
    }

    public function create()
    {
        if (!Gate::allows('ventas.store')) {
            abort(403, 'No tienes permiso para crear ventas.');
        }
        $clientes = Cliente::all();
        $empleados = Empleado::all();
        $cuentas = $this->obtenerCuentasDisponiblesParaVenta();
        $bancos = Banco::all();

        return view('sales.ventas.create', compact('empleados', 'clientes', 'cuentas', 'bancos'));
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

        // Verificar si se pagó y registrar transacción
        $sePago = $request->has('se_pago') && $request->se_pago == '1';
        $metodoPago = $request->input('metodo_pago', 'banco'); // 'banco' | 'saldo'
        $transaccionId = null;
        $clientePago = null;

        if ($sePago) {
            if ($metodoPago === 'saldo') {
                $clientePago = Cliente::findOrFail($request->idcli);
                if ($clientePago->saldo < $total_venta) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Saldo insuficiente. El cliente tiene $' . number_format($clientePago->saldo, 2) . ' y la venta total es $' . number_format($total_venta, 2) . '.');
                }
            } else {
                $request->validate([
                    'banco_id' => 'required|exists:bancos,idban'
                ]);
            }
        }

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

            // Verificar si el perfil existe, si no crearlo
            $perfil = \App\Models\Perfil::find($idper);
            if (!$perfil) {
                $perfil = \App\Models\Perfil::create([
                    'idper' => $idper,
                    'idcue' => $idcue,
                    'numeroper' => $numeroper,
                    'pinper' => null, // Se puede actualizar después si es necesario
                ]);
            }

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

        // Registrar transacción bancaria (ingreso) o descontar saldo del cliente
        if ($sePago) {
            if ($metodoPago === 'saldo') {
                $clientePago->saldo -= $total_venta;
                $clientePago->save();
            } else {
                try {
                    $venta->load('cliente');
                    $transaccion = $this->bancoService->registrarTransaccion(
                        $request->banco_id,
                        $total_venta,
                        'ingreso',
                        'Venta #' . $venta->idven . ' - Cliente: ' . $venta->cliente->nombrecli
                    );
                    $transaccionId = $transaccion->id;
                    $venta->transaccion_id = $transaccionId;
                    $venta->save();
                } catch (\Exception $e) {
                    $venta->detalles_venta()->delete();
                    $venta->delete();
                    return redirect()->route('ventas.create')->with('error', $e->getMessage());
                }
            }
        }

        // Lógica para generar y enviar la factura por correo
        if ($venta->cliente->email) {
            //Mail::to($venta->cliente->email)->send(new facturaMail($venta));
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

        $mensajeEntrega = $this->entregaMensajeService->mensajeEntregaVenta(
            $venta->loadMissing('detalles_venta.perfil.cuenta.valor.servicio')
        );

        return redirect()->route('ventas.create')
            ->with('success', 'Venta registrada correctamente')
            ->with('mensaje_entrega', $mensajeEntrega)
            ->with('mensaje_entrega_cliente', [
                'idcli' => $venta->cliente->idcli,
                'cliente' => $venta->cliente->nombrecli,
                'telefono' => $venta->cliente->telefonocli,
            ]);
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
        $oldIdDets = \App\Models\DetalleVenta::where('idven', $idvenPasado)->pluck('iddet')->toArray();
        \App\Models\DetalleVenta::where('idven', $idvenPasado)->update(['activodet' => false]);
        $detalles = json_decode($request->detalles_venta, true);

        if (!is_array($detalles) || empty($detalles)) {
            return redirect()->back()->with('error', 'No hay detalles válidos para renovar la venta.');
        }

        foreach ($detalles as $detalle) {
            $idcue = $detalle['cuenta'] ?? null;
            $numeroper = $detalle['perfil'] ?? null;

            if (empty($idcue) || empty($numeroper)) {
                return redirect()->back()->with('error', 'Se detectó un detalle incompleto en la renovación.');
            }

            $idper = $idcue . '.' . $numeroper;
            $perfilExiste = DB::table('perfiles')->where('idper', $idper)->exists();

            if (!$perfilExiste) {
                return redirect()->back()->with('error', 'Uno o más perfiles de la renovación ya no existen. Actualiza los detalles antes de guardar.');
            }
        }

        $total_venta = collect($detalles)->sum('monto');
        $fecha = Carbon::today()->toDateString();

        // Verificar si se pagó y registrar transacción
        $sePago = $request->has('se_pago') && $request->se_pago == '1';
        $metodoPago = $request->input('metodo_pago', 'banco'); // 'banco' | 'saldo'
        $transaccionId = null;
        $clientePago = null;

        if ($sePago) {
            if ($metodoPago === 'saldo') {
                $clientePago = Cliente::findOrFail($request->idcli);
                if ($clientePago->saldo < $total_venta) {
                    return redirect()->back()->withInput()
                        ->with('error', 'Saldo insuficiente. El cliente tiene $' . number_format($clientePago->saldo, 2) . ' y la renovación total es $' . number_format($total_venta, 2) . '.');
                }
            } else {
                $request->validate([
                    'banco_id' => 'required|exists:bancos,idban'
                ]);
                $transaccion = $this->bancoService->registrarTransaccion(
                    $request->banco_id,
                    $total_venta,
                    'ingreso',
                    'Renovación de venta #' . $idvenPasado . ' - Total: $' . number_format($total_venta, 2)
                );
                $transaccionId = $transaccion->id;
            }
        }

        $ventaNueva = Venta::create([
            'idcli' => $request->idcli,
            'idemp' => $request->idemp,
            'fechaven' => $fecha,
            'totalpagoven' => $total_venta,
            'transaccion_id' => $transaccionId,
        ]);

        $ventaNueva->idven = DB::table('ventas')->where('idcli', $request->idcli)
            ->where('idemp', $request->idemp)
            ->where('fechaven', $fecha)
            ->orderBy('idven', 'desc')
            ->value('idven');

        if ($ventaNueva->cliente->email) {
            //Mail::to($ventaNueva->cliente->email)->send(new facturaMail($ventaNueva));
        }

        // Descontar saldo del cliente si se usó ese método
        if ($sePago && $metodoPago === 'saldo' && $clientePago) {
            $clientePago->saldo -= $total_venta;
            $clientePago->save();
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

        $empleadoId = Auth::user()->idemp;
        $tareaService = app(TareaService::class);
        foreach ($oldIdDets as $oldIddet) {
            $tareaService->completarTareasRelacionadas('cobrar_usuario', 'ViewUsuarioActivo', $oldIddet, $empleadoId);
            $tareaService->completarTareasRelacionadas('quitar_usuario', 'ViewUsuarioActivo', $oldIddet, $empleadoId);
        }

        $mensajeRenovacion = $this->entregaMensajeService->mensajeRenovacionVenta(
            $ventaNueva->loadMissing('detalles_venta.perfil.cuenta.valor.servicio')
        );

        return redirect()->route('usuarios')
            ->with('success', 'Venta renovada correctamente')
            ->with('mensaje_renovacion', $mensajeRenovacion)
            ->with('mensaje_renovacion_cliente', [
                'idcli' => $ventaNueva->cliente->idcli,
                'cliente' => $ventaNueva->cliente->nombrecli,
                'telefono' => $ventaNueva->cliente->telefonocli,
            ]);
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
        $venta = Venta::with(['detalles_venta', 'transaccion'])->findOrFail($idven);
        $empleados = Empleado::all();
        $cuentas = $this->obtenerCuentasDisponiblesParaVenta();
        $bancos = Banco::all();

        return view('sales.ventas.edit', compact('venta', 'empleados', 'cuentas', 'bancos'));
    }

    public function renew($idcli, $idven)
    {
        if (!Gate::allows('ventas.renew')) {
            abort(403, 'No tienes permiso para renovar ventas.');
        }
        $venta = Venta::with(['detalles_venta', 'cliente', 'transaccion'])->findOrFail($idven);
        $empleados = Empleado::all();
        $cuentas = $this->obtenerCuentasDisponiblesParaVenta();
        $bancos = Banco::all();

        if ($venta->idcli != $idcli) {
            abort(404, 'Cliente no coincide con la venta.');
        }

        $detalles = $venta->detalles_venta->map(function ($detalle) {
            $detalle->fechavendet_suma = Carbon::parse($detalle->fechavendet)->addMonth()->format('Y-m-d');
            return $detalle;
        });

        $totalVenta = $venta->detalles_venta->sum('montodet');

        return view('sales.ventas.renew', [
            'empleados' => $empleados,
            'cuentas' => $cuentas,
            'venta' => $venta,
            'detalles' => $detalles,
            'totalVenta' => $totalVenta,
            'bancos' => $bancos
        ]);
    }

    private function obtenerCuentasDisponiblesParaVenta()
    {
        $hoy = Carbon::today();

        $cuentas = Cuenta::with(['valor.servicio', 'perfiles'])
            ->where('activocue', true)
            ->where('caidacue', false)
            ->where('idcue', 'NOT LIKE', '%Atencion%')
            ->whereHas('valor', function ($query) {
                $query->whereNotNull('pantmaxval');
            })
            ->orderBy('idcue')
            ->get();

        if ($cuentas->isEmpty()) {
            return $cuentas;
        }

        $idsCuentas = $cuentas->pluck('idcue')->values();

        $usuariosActivosPorCuenta = ViewUsuarioActivo::query()
            ->select('idcue', DB::raw('COUNT(*) as total_activos'))
            ->whereIn('idcue', $idsCuentas)
            ->whereDate('fecha_vencimiento', '>', $hoy)
            ->groupBy('idcue')
            ->pluck('total_activos', 'idcue');

        $usuariosActivosPorPerfil = ViewUsuarioActivo::query()
            ->select('idcue', 'perfil', DB::raw('COUNT(*) as total_activos'))
            ->whereIn('idcue', $idsCuentas)
            ->whereDate('fecha_vencimiento', '>', $hoy)
            ->groupBy('idcue', 'perfil')
            ->get()
            ->groupBy('idcue');

        return $cuentas
            ->filter(function ($cuenta) use ($usuariosActivosPorCuenta, $usuariosActivosPorPerfil) {
                $usuariosActivos = (int) ($usuariosActivosPorCuenta[$cuenta->idcue] ?? 0);
                $pantMax = (int) ($cuenta->valor->pantmaxval ?? 0);

                $cuenta->usuarios_activos = $usuariosActivos;
                $cuenta->cupos_disponibles = max(0, $pantMax - $usuariosActivos);

                $perfilesActivos = collect($usuariosActivosPorPerfil->get($cuenta->idcue, []))
                    ->pluck('total_activos', 'perfil');

                foreach ($cuenta->perfiles as $perfil) {
                    $perfil->usuarios_activos = (int) ($perfilesActivos[$perfil->numeroper] ?? 0);
                }

                return $pantMax > 0 && $usuariosActivos < $pantMax;
            })
            ->values();
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

        $montoAnterior = $venta->totalpagoven;
        // Obtener banco anterior desde la transacción
        $bancoAnterior = $venta->transaccion ? $venta->transaccion->banco_id : null;
        $transaccionAnterior = $venta->transaccion_id;

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

        // Verificar si se pagó y registrar transacción
        $sePago = $request->has('se_pago') && $request->se_pago == '1';

        if ($sePago) {
            $request->validate([
                'banco_id' => 'required|exists:bancos,idban'
            ]);

            $bancoChanged = (int) $bancoAnterior !== (int) $request->banco_id;
            $montoChanged = (float) $montoAnterior !== (float) $totalVenta;

            if (!$transaccionAnterior || $bancoChanged || $montoChanged) {
                // Anular transacción anterior si existe antes de crear la nueva
                if ($transaccionAnterior) {
                    $this->bancoService->anularTransaccion($transaccionAnterior);
                }

                try {
                    $transaccion = $this->bancoService->registrarTransaccion(
                        $request->banco_id,
                        $totalVenta,
                        'ingreso',
                        'Actualización Venta #' . $venta->idven . ' - Cliente: ' . $venta->cliente->nombrecli
                    );

                    $venta->transaccion_id = $transaccion->id;
                    $venta->save();
                } catch (\Exception $e) {
                    return redirect()->route('ventas.edit', $idven)->with('error', $e->getMessage());
                }
            }
            // mismo banco y mismo monto → transacción existente ya es correcta, sin cambios
        } else {
            // Si no se pagó y había transacción anterior, anularla
            if ($transaccionAnterior) {
                $this->bancoService->anularTransaccion($transaccionAnterior);
                $venta->transaccion_id = null;
                $venta->save();
            }
        }

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

    /**
     * Obtener detalles completos de una venta (AJAX)
     */
    public function getDetails($id)
    {
        try {
            $venta = Venta::with(['cliente', 'empleado', 'detalles_venta.perfil.cuenta.valor.servicio', 'transaccion.banco'])
                ->findOrFail($id);

            // Formatear los detalles para la respuesta
            $detalles = $venta->detalles_venta->map(function($detalle) {
                return [
                    'cuenta' => $detalle->perfil->cuenta->idcue ?? 'N/A',
                    'perfil' => 'Perfil ' . ($detalle->perfil->numeroper ?? 'N/A'),
                    'descripcion' => $detalle->descripciondet ?? '',
                    'cantidad' => 1,
                    'precio' => number_format($detalle->montodet, 2),
                    'subtotal' => number_format($detalle->montodet, 2),
                ];
            });

            $mensajeEntrega = $this->entregaMensajeService->mensajeEntregaVenta($venta);

            return response()->json([
                'success' => true,
                'venta' => [
                    'idven' => $venta->idven,
                    'fechaven' => $venta->fechaven->format('d/m/Y H:i'),
                    'metodopagoven' => $venta->transaccion && $venta->transaccion->banco ? $venta->transaccion->banco->nombreban : 'No especificado',
                    'totalpagoven' => number_format($venta->totalpagoven, 2),
                    'cliente' => [
                        'nombrecli' => $venta->cliente->nombrecli,
                        'telefonocli' => $venta->cliente->telefonocli ?? 'No especificado',
                        'email' => $venta->cliente->email ?? 'No especificado',
                    ],
                    'empleado' => [
                        'nombreemp' => $venta->empleado->nombreemp,
                    ],
                    'banco' => [
                        'nombreban' => $venta->transaccion && $venta->transaccion->banco ? $venta->transaccion->banco->nombreban : 'No especificado',
                        'idban' => $venta->transaccion && $venta->transaccion->banco ? $venta->transaccion->banco->idban : null,
                    ],
                    'detalles' => $detalles,
                    'mensaje_entrega' => $mensajeEntrega,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles de la venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendInvoice($id)
    {
        if (!Gate::allows('ventas.sendInvoice')) {
            abort(403, 'No tienes permiso para enviar facturas.');
        }

        try {
            $venta = Venta::findOrFail($id);
            $cliente = $venta->cliente;

            if (!$cliente->email) {
                if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El cliente no tiene email registrado.',
                    ], 400);
                }
                return redirect()->route('ventas')->with('error', 'El cliente no tiene email registrado.');
            }

            Mail::to($cliente->email)->send(new facturaMail($venta));

            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Factura enviada correctamente al email: ' . $cliente->email,
                ]);
            }

            return redirect()->route('ventas')->with('success', 'Factura enviada correctamente.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar la factura: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('ventas')->with('error', 'Error al enviar la factura.');
        }
    }

    public function destroy($idven)
    {
        if (!Gate::allows('ventas.destroy')) {
            abort(403, 'No tienes permiso para eliminar ventas.');
        }

        try {
            $venta = Venta::findOrFail($idven);

            // Anular transacción si existe
            if ($venta->transaccion_id) {
                $this->bancoService->anularTransaccion($venta->transaccion_id);
            }

            Historial::create([
                'accion' => 'Eliminación de Venta',
                'descripcion' => 'Datos Eliminados: ' . json_encode($venta),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            $venta->detalles_venta()->delete();
            $venta->delete();

            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Venta eliminada con éxito.',
                ]);
            }

            return redirect()->route('ventas')->with('success', 'Venta eliminada con éxito.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la venta: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('ventas')->with('error', 'Error al eliminar la venta.');
        }
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
