<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\DetalleVenta;
use App\Models\Perfil;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * API v2 - CRUD de Ventas basado en Productos
 *
 * Este controlador reutiliza la logica de venta automatizada del agente:
 * 1) valida saldo del cliente,
 * 2) valida disponibilidad de cuentas/perfiles,
 * 3) genera venta y detalles,
 * 4) descuenta saldo al cliente.
 */
class TecnicoVentasController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    /**
     * Crear venta por producto.
     *
     * POST /api/v2/ventas
     * POST /api/v2/tech-ventas/crear
     */
    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcli' => 'required|string|exists:clientes,idcli',
            'idproducto' => 'required|integer|exists:productos,id',
            'idemp' => 'nullable|integer|exists:empleados,idemp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $producto = Producto::with('detalles')->findOrFail((int) $request->input('idproducto'));

        if (!$producto->activo || (int) $producto->tipo_producto_id !== 1 || $producto->detalles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El producto no esta disponible para venta inmediata.',
            ], 422);
        }

        $cliente = Cliente::findOrFail($request->input('idcli'));

        if ((float) $cliente->saldo < (float) $producto->preciopro) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo insuficiente para realizar la compra.',
                'data' => [
                    'saldo_actual' => (float) $cliente->saldo,
                    'precio_producto' => (float) $producto->preciopro,
                    'faltante' => round((float) $producto->preciopro - (float) $cliente->saldo, 2),
                ],
            ], 422);
        }

        foreach ($producto->detalles as $detalle) {
            if (!$this->buscarCuentaDisponible($detalle->idser)) {
                $producto->update(['activo' => false]);

                return response()->json([
                    'success' => false,
                    'message' => 'No hay cuentas disponibles para este servicio en este momento.',
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $empleadoAutenticado = $request->user('api');
            $idEmp = $request->filled('idemp')
                ? (int) $request->input('idemp')
                : (int) ($empleadoAutenticado->idemp ?? 10);

            $venta = new Venta();
            $venta->idemp = $idEmp;
            $venta->idcli = $cliente->idcli;
            $venta->fechaven = now();
            $venta->totalpagoven = (float) $producto->preciopro;
            $venta->save();

            $venta->idven = DB::selectOne(
                'SELECT idven FROM ventas WHERE idcli = ? ORDER BY fechaven DESC LIMIT 1',
                [$venta->idcli]
            )?->idven;

            if (!$venta->idven) {
                throw new \RuntimeException('No se pudo obtener el id de venta generado.');
            }

            $entregas = [];
            $montoTotalDetalles = 0.0;
            $detalleCount = max($producto->detalles->count(), 1);
            $montoRestante = (float) $producto->preciopro;

            foreach ($producto->detalles->values() as $index => $detalle) {
                $cuenta = $this->buscarCuentaDisponible($detalle->idser);

                if (!$cuenta) {
                    $producto->update(['activo' => false]);
                    throw new \RuntimeException('No hay cuentas disponibles para el servicio.');
                }

                $perfil = $this->buscarPerfilDisponible($cuenta, true);

                if (!$perfil) {
                    throw new \RuntimeException('No hay perfiles disponibles en la cuenta seleccionada.');
                }

                $montoDetalle = $index === $detalleCount - 1
                    ? round($montoRestante, 2)
                    : round((float) $producto->preciopro / $detalleCount, 2);

                $montoRestante -= $montoDetalle;

                $detalleVenta = new DetalleVenta();
                $detalleVenta->idven = $venta->idven;
                $detalleVenta->idper = $perfil->idper;
                $detalleVenta->fechavendet = now()->addMonths((int) $detalle->meses)->subDay();
                $detalleVenta->descripciondet = 'Venta automatizada por API v2 (android)';
                $detalleVenta->montodet = $montoDetalle;
                $detalleVenta->activodet = true;
                $detalleVenta->save();

                $montoTotalDetalles += (float) $detalleVenta->montodet;

                $entregas[] = [
                    'iddet' => $detalleVenta->iddet,
                    'servicio' => $perfil->cuenta->valor->servicio->nombreser ?? null,
                    'usuario' => $perfil->cuenta->usuariocue ?? null,
                    'contrasena' => $perfil->cuenta->contrasenacue ?? null,
                    'perfil' => $perfil->numeroper,
                    'pin' => $perfil->pinper,
                    'vence' => optional($detalleVenta->fechavendet)->toDateString(),
                    'monto' => (float) $detalleVenta->montodet,
                ];
            }

            $cliente->saldo = round((float) $cliente->saldo - $montoTotalDetalles, 2);
            if (!$cliente->ya_compro) {
                $cliente->ya_compro = true;
            }
            $cliente->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta creada correctamente.',
                'data' => [
                    'venta' => [
                        'idven' => $venta->idven,
                        'idcli' => $venta->idcli,
                        'idemp' => $venta->idemp,
                        'producto_id' => $producto->id,
                        'producto' => $producto->nombrepro,
                        'fecha' => optional($venta->fechaven)->toDateTimeString(),
                        'total' => round($montoTotalDetalles, 2),
                        'saldo_restante' => (float) $cliente->saldo,
                    ],
                    'entregas' => $entregas,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo crear la venta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Editar venta y/o sus detalles.
     *
     * PUT /api/v2/ventas/{idven}
     * PUT /api/v2/tech-ventas/editar/{idven}
     */
    public function editar(Request $request, $idven)
    {
        try {
            $venta = Venta::find($idven);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'idemp' => 'nullable|integer|exists:empleados,idemp',
                'idcli' => 'nullable|string|exists:clientes,idcli',
                'fechaven' => 'nullable|date',
                'detalles' => 'nullable|array',
                'detalles.*.iddet' => 'required|string|exists:detalles_venta,iddet',
                'detalles.*.montodet' => 'nullable|numeric|min:0',
                'detalles.*.fechavendet' => 'nullable|date',
                'detalles.*.descripciondet' => 'nullable|string|max:255',
                'detalles.*.activodet' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validacion fallida',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $camposVenta = [];
            foreach (['idemp', 'idcli', 'fechaven'] as $campo) {
                if ($request->filled($campo)) {
                    $camposVenta[$campo] = $request->input($campo);
                }
            }

            if (!empty($camposVenta)) {
                $venta->update($camposVenta);
            }

            $detallesActualizados = [];
            if ($request->has('detalles')) {
                foreach ($request->detalles as $detalle) {
                    $detalleVenta = DetalleVenta::where('iddet', $detalle['iddet'])
                        ->where('idven', $idven)
                        ->firstOrFail();

                    if (isset($detalle['montodet'])) {
                        $detalleVenta->montodet = $detalle['montodet'];
                    }
                    if (isset($detalle['fechavendet'])) {
                        $detalleVenta->fechavendet = $detalle['fechavendet'];
                    }
                    if (isset($detalle['descripciondet'])) {
                        $detalleVenta->descripciondet = $detalle['descripciondet'];
                    }
                    if (isset($detalle['activodet'])) {
                        $detalleVenta->activodet = $detalle['activodet'];
                    }
                    $detalleVenta->save();

                    $detallesActualizados[] = [
                        'iddet' => $detalleVenta->iddet,
                        'monto' => $detalleVenta->montodet,
                        'fecha_vencimiento' => optional($detalleVenta->fechavendet)->toDateString(),
                        'activo' => (bool) $detalleVenta->activodet,
                    ];
                }
            }

            $venta->refresh();
            $venta->load(['cliente', 'empleado', 'detalles_venta']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada exitosamente',
                'data' => [
                    'venta' => [
                        'idven' => $venta->idven,
                        'idcli' => $venta->idcli,
                        'cliente' => $venta->cliente->nombrecli ?? null,
                        'idemp' => $venta->idemp,
                        'empleado' => $venta->empleado->nombreemp ?? null,
                        'fecha' => optional($venta->fechaven)->toDateTimeString(),
                        'total' => (float) $venta->totalpagoven,
                    ],
                    'detalles_actualizados' => $detallesActualizados,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al editar venta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ver detalle completo de una venta.
     *
     * GET /api/v2/ventas/{idven}
     * GET /api/v2/tech-ventas/detalle/{idven}
     */
    public function detalle($idven)
    {
        try {
            $venta = Venta::with([
                'cliente',
                'empleado',
                'detalles_venta.perfil.cuenta.valor.servicio',
                'transaccion.banco',
            ])->find($idven);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'venta' => [
                        'idven' => $venta->idven,
                        'fecha' => optional($venta->fechaven)->toDateTimeString(),
                        'total' => (float) $venta->totalpagoven,
                    ],
                    'cliente' => [
                        'idcli' => $venta->cliente->idcli ?? null,
                        'nombre' => $venta->cliente->nombrecli ?? null,
                        'email' => $venta->cliente->email ?? null,
                        'telefono' => $venta->cliente->telefonocli ?? null,
                    ],
                    'empleado' => [
                        'idemp' => $venta->empleado->idemp ?? null,
                        'nombre' => $venta->empleado->nombreemp ?? null,
                    ],
                    'detalles' => $venta->detalles_venta->map(function ($det) {
                        $cuenta = $det->perfil->cuenta ?? null;
                        $servicio = $cuenta?->valor?->servicio;

                        return [
                            'iddet' => $det->iddet,
                            'cuenta' => $cuenta->idcue ?? null,
                            'usuario' => $cuenta->usuariocue ?? null,
                            'perfil' => $det->perfil->numeroper ?? null,
                            'servicio' => $servicio->nombreser ?? null,
                            'monto' => (float) $det->montodet,
                            'descripcion' => $det->descripciondet,
                            'fecha_vencimiento' => optional($det->fechavendet)->toDateString(),
                            'activo' => (bool) $det->activodet,
                        ];
                    }),
                    'transaccion' => $venta->transaccion ? [
                        'id' => $venta->transaccion->id,
                        'banco' => $venta->transaccion->banco->nombreban ?? null,
                        'monto' => (float) $venta->transaccion->monto,
                        'fecha' => optional($venta->transaccion->created_at)->toDateTimeString(),
                        'descripcion' => $venta->transaccion->descripcion,
                    ] : null,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle de venta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listado de ventas (mismo enfoque de la vista de ventas).
     *
     * GET /api/v2/ventas
     * GET /api/v2/tech-ventas/listar
     */
    public function listar(Request $request)
    {
        try {
            $perPage = (int) $request->query('per_page', 20);
            if ($perPage < 1) {
                $perPage = 20;
            }
            $perPage = min($perPage, 100);

            $query = Venta::with(['cliente', 'empleado', 'detalles_venta'])
                ->orderByDesc('fechaven');

            $empleadoAutenticado = $request->user('api');
            if ($empleadoAutenticado && !$request->filled('idemp')) {
                $query->where('idemp', $empleadoAutenticado->idemp);
            }

            if ($request->filled('search')) {
                $search = (string) $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('idven', 'like', "%{$search}%")
                        ->orWhereHas('cliente', function ($qCliente) use ($search) {
                            $qCliente->where('nombrecli', 'like', "%{$search}%")
                                ->orWhere('telefonocli', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('idcli')) {
                $query->where('idcli', $request->query('idcli'));
            }

            if ($request->filled('idemp')) {
                $query->where('idemp', $request->query('idemp'));
            }

            if ($request->filled('fecha_inicio')) {
                $query->whereDate('fechaven', '>=', $request->query('fecha_inicio'));
            }

            if ($request->filled('fecha_fin')) {
                $query->whereDate('fechaven', '<=', $request->query('fecha_fin'));
            }

            $sortBy = $request->query('sort_by', 'fechaven');
            $sortOrder = strtolower((string) $request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';
            $sortWhitelist = ['idven', 'fechaven', 'totalpagoven'];

            if (in_array($sortBy, $sortWhitelist, true)) {
                $query->reorder($sortBy, $sortOrder);
            }

            $ventas = $query->paginate($perPage);

            $data = collect($ventas->items())->map(function ($venta) {
                $montoDetalles = (float) $venta->detalles_venta->sum('montodet');

                return [
                    'idven' => $venta->idven,
                    'idcli' => $venta->idcli,
                    'cliente' => $venta->cliente->nombrecli ?? null,
                    'idemp' => $venta->idemp,
                    'empleado' => $venta->empleado->nombreemp ?? null,
                    'fecha' => optional($venta->fechaven)->toDateTimeString(),
                    'totalpagoven' => (float) $venta->totalpagoven,
                    'cantidad_detalles' => $venta->detalles_venta->count(),
                    'monto_detalles' => $montoDetalles,
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $ventas->total(),
                    'per_page' => $ventas->perPage(),
                    'current_page' => $ventas->currentPage(),
                    'last_page' => $ventas->lastPage(),
                    'from' => $ventas->firstItem(),
                    'to' => $ventas->lastItem(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar ventas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar una venta.
     *
     * DELETE /api/v2/ventas/{idven}
     * DELETE /api/v2/tech-ventas/eliminar/{idven}
     */
    public function eliminar(Request $request, string $idven)
    {
        $venta = Venta::with('detalles_venta')->find($idven);

        if (!$venta) {
            return response()->json([
                'success' => false,
                'message' => 'Venta no encontrada',
            ], 404);
        }

        $force = filter_var($request->query('force', false), FILTER_VALIDATE_BOOL);
        $detallesActivos = $venta->detalles_venta->where('activodet', true)->count();

        if (!$force && $detallesActivos > 0) {
            return response()->json([
                'success' => false,
                'message' => 'La venta tiene detalles activos. Envia force=true para eliminarla.',
                'data' => [
                    'detalles_activos' => $detallesActivos,
                ],
            ], 400);
        }

        DB::beginTransaction();

        try {
            if ($force) {
                DetalleVenta::where('idven', $venta->idven)->delete();
            }

            $venta->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada correctamente.',
                'data' => [
                    'idven' => $idven,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar venta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Estadisticas de ventas.
     *
     * GET /api/v2/tech-ventas/estadisticas
     */
    public function estadisticas(Request $request)
    {
        try {
            $periodo = $request->query('periodo', 'mes');

            $fechaInicio = match ($periodo) {
                'hoy' => Carbon::today(),
                'semana' => Carbon::now()->startOfWeek(),
                'mes' => Carbon::now()->startOfMonth(),
                'anio' => Carbon::now()->startOfYear(),
                default => Carbon::now()->startOfMonth(),
            };

            $totalVentas = Venta::where('fechaven', '>=', $fechaInicio)->count();

            $montoTotal = Venta::where('fechaven', '>=', $fechaInicio)
                ->sum('totalpagoven');

            $promedioVenta = $totalVentas > 0 ? $montoTotal / $totalVentas : 0;

            return response()->json([
                'success' => true,
                'periodo' => $periodo,
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'data' => [
                    'total_ventas' => $totalVentas,
                    'monto_total' => round((float) $montoTotal, 2),
                    'promedio_por_venta' => round((float) $promedioVenta, 2),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadisticas de ventas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function buscarCuentaDisponible($idser)
    {
        return Cuenta::whereHas('valor', function ($query) use ($idser) {
            $query->where('idser', $idser);
        })
            ->where('caidacue', false)
            ->where('activocue', true)
            ->whereHas('valor', function ($query) {
                $query->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos WHERE view_usuarios_activos.idcue = cuentas.idcue) < valores.pantmaxval');
            })
            ->first();
    }

    private function buscarPerfilDisponible($cuenta, bool $forUpdate = false)
    {
        $query = Perfil::where('idcue', $cuenta->idcue)->orderBy('numeroper');

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $perfiles = $query->get();

        foreach ([0, 1] as $ocupacionPermitida) {
            foreach ($perfiles as $perfil) {
                $usuariosActivos = DetalleVenta::where('idper', $perfil->idper)
                    ->where('activodet', true)
                    ->whereDate('fechavendet', '>=', now()->toDateString())
                    ->count();

                if ($usuariosActivos === $ocupacionPermitida) {
                    return $perfil;
                }
            }
        }

        return null;
    }
}
