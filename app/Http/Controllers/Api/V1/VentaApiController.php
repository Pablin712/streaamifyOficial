<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaApiController extends Controller
{
    /**
     * Constructor - Aplicar middleware de permisos
     */
    public function __construct()
    {
        // Descomentar cuando se implemente Spatie Permissions
        // $this->middleware('permission:ventas.ver')->only(['index', 'show', 'estadisticas', 'detalles']);
        // $this->middleware('permission:ventas.crear')->only(['store']);
        // $this->middleware('permission:ventas.editar')->only(['update', 'renovar']);
        // $this->middleware('permission:ventas.eliminar')->only(['destroy']);
    }

    /**
     * Listar todas las ventas con filtros opcionales
     * GET /api/v1/ventas
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $query = Venta::with(['cliente', 'empleado', 'detalles_venta.perfil.cuenta']);

            // Filtro por cliente
            if ($request->has('idcli')) {
                $query->where('idcli', $request->idcli);
            }

            // Filtro por empleado
            if ($request->has('idemp')) {
                $query->where('idemp', $request->idemp);
            }

            // Filtro por rango de fechas
            if ($request->has('fecha_inicio')) {
                $query->whereDate('fechaven', '>=', $request->fecha_inicio);
            }
            if ($request->has('fecha_fin')) {
                $query->whereDate('fechaven', '<=', $request->fecha_fin);
            }

            // Búsqueda por nombre de cliente
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('cliente', function ($q) use ($search) {
                    $q->where('nombrecli', 'LIKE', "%{$search}%")
                      ->orWhere('telefonocli', 'LIKE', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortBy = $request->input('sort_by', 'fechaven');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $ventas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $ventas->items(),
                'pagination' => [
                    'total' => $ventas->total(),
                    'per_page' => $ventas->perPage(),
                    'current_page' => $ventas->currentPage(),
                    'last_page' => $ventas->lastPage(),
                    'from' => $ventas->firstItem(),
                    'to' => $ventas->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener ventas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una venta específica con todas sus relaciones
     * GET /api/v1/ventas/{id}
     */
    public function show(string $id)
    {
        try {
            $venta = Venta::with([
                'cliente',
                'empleado',
                'detalles_venta.perfil.cuenta.valor.servicio',
                'usuarios'
            ])->find($id);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'error' => 'Venta no encontrada',
                    'message' => "No existe una venta con ID {$id}"
                ], 404);
            }

            // Calcular monto total de la venta
            $montoTotal = $venta->detalles_venta->sum('montodet');

            $ventaData = $venta->toArray();
            $ventaData['monto_total'] = $montoTotal;
            $ventaData['cantidad_detalles'] = $venta->detalles_venta->count();

            return response()->json([
                'success' => true,
                'data' => $ventaData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva venta con sus detalles
     * POST /api/v1/ventas
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idemp' => 'required|exists:empleados,idemp',
                'idcli' => 'required|exists:clientes,idcli',
                'fechaven' => 'nullable|date',
                'detalles' => 'required|array|min:1',
                'detalles.*.idper' => 'required|exists:perfiles,idper',
                'detalles.*.descripciondet' => 'nullable|string|max:255',
                'detalles.*.fechavendet' => 'nullable|date',
                'detalles.*.montodet' => 'required|numeric|min:0',
                'detalles.*.activodet' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validar que el cliente existe
            $cliente = Cliente::find($request->idcli);
            if (!$cliente) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cliente no encontrado'
                ], 404);
            }

            // Validar que los perfiles existen y están disponibles
            $perfilesIds = collect($request->detalles)->pluck('idper')->unique();
            $perfiles = Perfil::whereIn('idper', $perfilesIds)->get();

            if ($perfiles->count() !== $perfilesIds->count()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Uno o más perfiles no existen'
                ], 404);
            }

            DB::beginTransaction();

            try {
                // Crear la venta (el trigger generará el idven automáticamente)
                $venta = new Venta([
                    'idemp' => $request->idemp,
                    'idcli' => $request->idcli,
                    'fechaven' => $request->fechaven ?? now(),
                ]);
                $venta->save();

                // IMPORTANTE: Obtener el idven generado por el trigger MySQL
                // Como usamos un trigger BEFORE INSERT, debemos consultar la última venta insertada
                $ventaCreada = Venta::where('idemp', $request->idemp)
                    ->where('idcli', $request->idcli)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                if (!$ventaCreada) {
                    throw new \Exception('Error al obtener la venta creada');
                }

                // Crear los detalles de venta
                $montoTotal = 0;
                foreach ($request->detalles as $detalle) {
                    DetalleVenta::create([
                        'idven' => $ventaCreada->idven,
                        'idper' => $detalle['idper'],
                        'descripciondet' => $detalle['descripciondet'] ?? null,
                        'fechavendet' => $detalle['fechavendet'] ?? now(),
                        'montodet' => $detalle['montodet'],
                        'activodet' => $detalle['activodet'] ?? true,
                    ]);
                    $montoTotal += $detalle['montodet'];
                }

                DB::commit();

                // Recargar la venta con sus relaciones
                $venta = Venta::with([
                    'cliente',
                    'empleado',
                    'detalles_venta.perfil.cuenta'
                ])->find($ventaCreada->idven);

                $ventaData = $venta->toArray();
                $ventaData['monto_total'] = $montoTotal;

                return response()->json([
                    'success' => true,
                    'message' => 'Venta creada exitosamente',
                    'data' => $ventaData
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar venta existente
     * PUT/PATCH /api/v1/ventas/{id}
     */
    public function update(Request $request, string $id)
    {
        try {
            $venta = Venta::find($id);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'error' => 'Venta no encontrada',
                    'message' => "No existe una venta con ID {$id}"
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'idemp' => 'sometimes|exists:empleados,idemp',
                'idcli' => 'sometimes|exists:clientes,idcli',
                'fechaven' => 'sometimes|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $venta->update($request->only(['idemp', 'idcli', 'fechaven']));

            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada exitosamente',
                'data' => $venta->fresh(['cliente', 'empleado', 'detalles_venta'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar venta
     * DELETE /api/v1/ventas/{id}
     */
    public function destroy(string $id)
    {
        try {
            $venta = Venta::find($id);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'error' => 'Venta no encontrada',
                    'message' => "No existe una venta con ID {$id}"
                ], 404);
            }

            // Validar si la venta puede ser eliminada
            // Por ejemplo, no eliminar si tiene detalles activos
            $detallesActivos = $venta->detalles_venta()->where('activodet', true)->count();
            if ($detallesActivos > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se puede eliminar',
                    'message' => 'La venta tiene detalles activos. Desactívelos antes de eliminar.'
                ], 400);
            }

            $venta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al eliminar venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renovar venta - Crear nueva venta basada en una anterior
     * POST /api/v1/ventas/{id}/renovar
     */
    public function renovar(Request $request, string $id)
    {
        try {
            $ventaOriginal = Venta::with('detalles_venta')->find($id);

            if (!$ventaOriginal) {
                return response()->json([
                    'success' => false,
                    'error' => 'Venta no encontrada',
                    'message' => "No existe una venta con ID {$id}"
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'idemp' => 'required|exists:empleados,idemp',
                'fechaven' => 'nullable|date',
                'meses_duracion' => 'nullable|integer|min:1|max:12',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Crear nueva venta (el trigger generará el idven)
                $nuevaVenta = new Venta([
                    'idemp' => $request->idemp,
                    'idcli' => $ventaOriginal->idcli,
                    'fechaven' => $request->fechaven ?? now(),
                ]);
                $nuevaVenta->save();

                // IMPORTANTE: Obtener el idven generado por el trigger MySQL
                $ventaCreada = Venta::where('idemp', $request->idemp)
                    ->where('idcli', $ventaOriginal->idcli)
                    ->orderBy('created_at', 'DESC')
                    ->first();

                if (!$ventaCreada) {
                    throw new \Exception('Error al obtener la venta renovada');
                }

                // Copiar detalles de la venta original
                $mesesDuracion = $request->meses_duracion ?? 1;
                $montoTotal = 0;

                foreach ($ventaOriginal->detalles_venta as $detalleOriginal) {
                    $nuevaFechaVencimiento = now()->addMonths($mesesDuracion);

                    DetalleVenta::create([
                        'idven' => $ventaCreada->idven,
                        'idper' => $detalleOriginal->idper,
                        'descripciondet' => 'Renovación - ' . ($detalleOriginal->descripciondet ?? ''),
                        'fechavendet' => $nuevaFechaVencimiento,
                        'montodet' => $detalleOriginal->montodet,
                        'activodet' => true,
                    ]);
                    $montoTotal += $detalleOriginal->montodet;
                }

                DB::commit();

                // Recargar con relaciones
                $nuevaVenta = Venta::with([
                    'cliente',
                    'empleado',
                    'detalles_venta.perfil.cuenta'
                ])->find($ventaCreada->idven);

                $ventaData = $nuevaVenta->toArray();
                $ventaData['monto_total'] = $montoTotal;
                $ventaData['venta_original_id'] = $ventaOriginal->idven;

                return response()->json([
                    'success' => true,
                    'message' => 'Venta renovada exitosamente',
                    'data' => $ventaData
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al renovar venta',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de una venta específica
     * GET /api/v1/ventas/{id}/detalles
     */
    public function detalles(string $id)
    {
        try {
            $venta = Venta::find($id);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'error' => 'Venta no encontrada',
                    'message' => "No existe una venta con ID {$id}"
                ], 404);
            }

            $detalles = DetalleVenta::with([
                'perfil.cuenta.valor.servicio',
                'perfil.cuenta.valor.proveedor'
            ])
            ->where('idven', $id)
            ->get()
            ->map(function ($detalle) {
                return [
                    'iddet' => $detalle->iddet,
                    'perfil' => $detalle->perfil ? [
                        'idper' => $detalle->perfil->idper,
                        'numeroper' => $detalle->perfil->numeroper,
                        'pinper' => $detalle->perfil->pinper,
                        'cuenta' => $detalle->perfil->cuenta ? [
                            'idcue' => $detalle->perfil->cuenta->idcue,
                            'correocue' => $detalle->perfil->cuenta->correocue,
                            'servicio' => $detalle->perfil->cuenta->valor->servicio->nombreser ?? null,
                            'proveedor' => $detalle->perfil->cuenta->valor->proveedor->nombreprov ?? null,
                        ] : null,
                    ] : null,
                    'descripciondet' => $detalle->descripciondet,
                    'fechavendet' => $detalle->fechavendet,
                    'montodet' => $detalle->montodet,
                    'activodet' => $detalle->activodet,
                    'dias_restantes' => $detalle->fechavendet ? Carbon::parse($detalle->fechavendet)->diffInDays(now(), false) : null,
                    'estado' => $detalle->activodet ?
                        ($detalle->fechavendet && Carbon::parse($detalle->fechavendet)->isPast() ? 'Vencido' : 'Activo') :
                        'Inactivo',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'idven' => $venta->idven,
                    'total_detalles' => $detalles->count(),
                    'monto_total' => $detalles->sum('montodet'),
                    'detalles_activos' => $detalles->where('activodet', true)->count(),
                    'detalles_vencidos' => $detalles->where('estado', 'Vencido')->count(),
                    'detalles' => $detalles,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener detalles',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de ventas
     * GET /api/v1/ventas/estadisticas
     */
    public function estadisticas(Request $request)
    {
        try {
            $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth());
            $fechaFin = $request->input('fecha_fin', now()->endOfMonth());

            // Total de ventas
            $totalVentas = Venta::whereBetween('fechaven', [$fechaInicio, $fechaFin])->count();

            // Total de ingresos
            $totalIngresos = DetalleVenta::whereHas('venta', function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fechaven', [$fechaInicio, $fechaFin]);
            })->sum('montodet');

            // Promedio por venta
            $promedioVenta = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;

            // Top clientes
            $topClientes = Venta::select('idcli', DB::raw('COUNT(*) as total_ventas'))
                ->with('cliente:idcli,nombrecli,telefonocli')
                ->whereBetween('fechaven', [$fechaInicio, $fechaFin])
                ->groupBy('idcli')
                ->orderByDesc('total_ventas')
                ->limit(10)
                ->get();

            // Ventas por empleado
            $ventasPorEmpleado = Venta::select('idemp', DB::raw('COUNT(*) as total_ventas'))
                ->with('empleado:idemp,nombre1emp,nombre2emp,apellido1emp')
                ->whereBetween('fechaven', [$fechaInicio, $fechaFin])
                ->groupBy('idemp')
                ->orderByDesc('total_ventas')
                ->get();

            // Ventas por día (últimos 30 días)
            $ventasPorDia = Venta::select(
                    DB::raw('DATE(fechaven) as fecha'),
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM((SELECT SUM(montodet) FROM detalles_venta WHERE detalles_venta.idven = ventas.idven)) as monto')
                )
                ->whereBetween('fechaven', [now()->subDays(30), now()])
                ->groupBy('fecha')
                ->orderBy('fecha', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => [
                        'inicio' => $fechaInicio,
                        'fin' => $fechaFin,
                    ],
                    'resumen' => [
                        'total_ventas' => $totalVentas,
                        'total_ingresos' => round($totalIngresos, 2),
                        'promedio_venta' => round($promedioVenta, 2),
                    ],
                    'top_clientes' => $topClientes,
                    'ventas_por_empleado' => $ventasPorEmpleado,
                    'ventas_por_dia' => $ventasPorDia,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener estadísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
