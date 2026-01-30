<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Controlador para gestión de Ventas (Agente IA - Técnico)
 * Maneja operaciones sobre ventas y relaciones con transacciones
 *
 * Rutas: /api/v2/tech-ventas/*
 */
class TecnicoVentasController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    /**
     * Crear nueva venta con detalles y transacción
     * POST /api/v2/tech-ventas/crear
     *
     * Body:
     * {
     *   "idcli": "string",         // ID del cliente
     *   "empleado_id": "integer",  // ID del empleado
     *   "detalles": [              // Array de detalles de venta
     *     {
     *       "idper": "string",
     *       "montodet": 5.00,
     *       "mesesdet": 1
     *     }
     *   ],
     *   "transaccion": {           // Opcional: datos de transacción
     *     "idbanco": "integer",
     *     "montotran": 5.00,
     *     "descripciontran": "string"
     *   }
     * }
     */
    public function crear(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'idcli' => 'required|string|exists:clientes,idcli',
                'empleado_id' => 'required|integer|exists:empleados,idemp',
                'detalles' => 'required|array|min:1',
                'detalles.*.idper' => 'required|string|exists:perfiles,idper',
                'detalles.*.montodet' => 'required|numeric|min:0',
                'detalles.*.mesesdet' => 'required|integer|min:1',
                'transaccion' => 'nullable|array',
                'transaccion.idbanco' => 'required_with:transaccion|integer|exists:bancos,idbanco',
                'transaccion.montotran' => 'required_with:transaccion|numeric|min:0',
                'transaccion.descripciontran' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Crear la venta (el trigger generará el idven automáticamente)
            $venta = new Venta();
            $venta->idcli = $request->idcli;
            $venta->empleado_id = $request->empleado_id;
            $venta->fechaven = Carbon::now();
            $venta->totalpagoven = 0; // Se calculará con triggers
            $venta->estadoven = 'COBRADO';
            $venta->save();

            // Crear detalles de venta
            $detallesCreados = [];
            foreach ($request->detalles as $detalle) {
                $fechaVencimiento = Carbon::now()->addMonths($detalle['mesesdet']);

                $detalleVenta = DetalleVenta::create([
                    'idven' => $venta->idven,
                    'idper' => $detalle['idper'],
                    'idcli' => $request->idcli,
                    'montodet' => $detalle['montodet'],
                    'fechavendet' => $fechaVencimiento,
                    'activodet' => true
                ]);

                $detallesCreados[] = [
                    'iddet' => $detalleVenta->iddet,
                    'perfil' => $detalle['idper'],
                    'monto' => $detalle['montodet'],
                    'vencimiento' => $fechaVencimiento->format('Y-m-d')
                ];
            }

            // Crear transacción si se proporcionó
            $transaccionCreada = null;
            if ($request->has('transaccion')) {
                $transaccion = Transaccion::create([
                    'idven' => $venta->idven,
                    'idbanco' => $request->transaccion['idbanco'],
                    'montotran' => $request->transaccion['montotran'],
                    'fechatran' => Carbon::now(),
                    'descripciontran' => $request->transaccion['descripciontran'] ?? 'Pago de venta',
                    'empleado_id' => $request->empleado_id
                ]);

                $transaccionCreada = [
                    'idtran' => $transaccion->idtran,
                    'banco' => $transaccion->idbanco,
                    'monto' => $transaccion->montotran
                ];
            }

            // Refrescar venta para obtener total calculado
            $venta->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta creada exitosamente',
                'data' => [
                    'venta' => [
                        'idven' => $venta->idven,
                        'fecha' => $venta->fechaven,
                        'total' => $venta->totalpagoven,
                        'estado' => $venta->estadoven
                    ],
                    'detalles' => $detallesCreados,
                    'transaccion' => $transaccionCreada
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar una venta existente
     * PUT /api/v2/tech-ventas/editar/{idven}
     *
     * Body:
     * {
     *   "estadoven": "COBRADO", // Opcional
     *   "detalles": [           // Opcional: actualizar detalles
     *     {
     *       "iddet": "string",
     *       "montodet": 6.00,
     *       "activodet": true
     *     }
     *   ]
     * }
     */
    public function editar(Request $request, $idven)
    {
        try {
            $validator = Validator::make($request->all(), [
                'estadoven' => 'nullable|in:COBRADO,PENDIENTE,CANCELADO',
                'detalles' => 'nullable|array',
                'detalles.*.iddet' => 'required|string|exists:detalles_venta,iddet',
                'detalles.*.montodet' => 'nullable|numeric|min:0',
                'detalles.*.activodet' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $venta = Venta::findOrFail($idven);

            // Actualizar estado si se proporcionó
            if ($request->has('estadoven')) {
                $venta->estadoven = $request->estadoven;
                $venta->save();
            }

            // Actualizar detalles si se proporcionaron
            $detallesActualizados = [];
            if ($request->has('detalles')) {
                foreach ($request->detalles as $detalle) {
                    $detalleVenta = DetalleVenta::where('iddet', $detalle['iddet'])
                        ->where('idven', $idven)
                        ->firstOrFail();

                    if (isset($detalle['montodet'])) {
                        $detalleVenta->montodet = $detalle['montodet'];
                    }
                    if (isset($detalle['activodet'])) {
                        $detalleVenta->activodet = $detalle['activodet'];
                    }
                    $detalleVenta->save();

                    $detallesActualizados[] = [
                        'iddet' => $detalleVenta->iddet,
                        'monto' => $detalleVenta->montodet,
                        'activo' => $detalleVenta->activodet
                    ];
                }
            }

            // Refrescar venta para obtener total actualizado
            $venta->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada exitosamente',
                'data' => [
                    'venta' => [
                        'idven' => $venta->idven,
                        'total' => $venta->totalpagoven,
                        'estado' => $venta->estadoven
                    ],
                    'detalles_actualizados' => $detallesActualizados
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al editar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalle completo de una venta
     * GET /api/v2/tech-ventas/detalle/{idven}
     */
    public function detalle($idven)
    {
        try {
            $venta = Venta::with([
                'cliente',
                'empleado',
                'detalles.perfil.cuenta.valor.servicio',
                'transacciones.banco'
            ])->findOrFail($idven);

            return response()->json([
                'success' => true,
                'data' => [
                    'venta' => [
                        'idven' => $venta->idven,
                        'fecha' => $venta->fechaven,
                        'total' => $venta->totalpagoven,
                        'estado' => $venta->estadoven
                    ],
                    'cliente' => [
                        'idcli' => $venta->cliente->idcli,
                        'nombre' => $venta->cliente->nombrecli,
                        'email' => $venta->cliente->correocli,
                        'telefono' => $venta->cliente->telefonocli
                    ],
                    'empleado' => [
                        'idemp' => $venta->empleado->idemp ?? null,
                        'nombre' => $venta->empleado->nombreemp ?? null
                    ],
                    'detalles' => $venta->detalles->map(function($det) {
                        return [
                            'iddet' => $det->iddet,
                            'cuenta' => $det->perfil->cuenta->idcue ?? 'N/A',
                            'perfil' => $det->perfil->numeroper ?? 'N/A',
                            'servicio' => $det->perfil->cuenta->valor->servicio->nombreser ?? 'N/A',
                            'monto' => $det->montodet,
                            'fecha_vencimiento' => $det->fechavendet,
                            'activo' => $det->activodet
                        ];
                    }),
                    'transacciones' => $venta->transacciones->map(function($tran) {
                        return [
                            'idtran' => $tran->idtran,
                            'banco' => $tran->banco->nombanco ?? 'N/A',
                            'monto' => $tran->montotran,
                            'fecha' => $tran->fechatran,
                            'descripcion' => $tran->descripciontran
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle de venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar ventas con filtros
     * GET /api/v2/tech-ventas/listar
     *
     * Query params:
     * - fecha_desde: YYYY-MM-DD
     * - fecha_hasta: YYYY-MM-DD
     * - estado: COBRADO, PENDIENTE, CANCELADO
     * - idcli: ID del cliente
     * - limit: Límite de resultados (default: 50)
     */
    public function listar(Request $request)
    {
        try {
            $query = Venta::with(['cliente', 'empleado']);

            if ($request->has('fecha_desde')) {
                $query->where('fechaven', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->where('fechaven', '<=', $request->fecha_hasta);
            }

            if ($request->has('estado')) {
                $query->where('estadoven', strtoupper($request->estado));
            }

            if ($request->has('idcli')) {
                $query->where('idcli', $request->idcli);
            }

            $limit = $request->query('limit', 50);
            $ventas = $query->orderBy('fechaven', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($venta) {
                    return [
                        'idven' => $venta->idven,
                        'cliente' => $venta->cliente->nombrecli ?? 'N/A',
                        'fecha' => $venta->fechaven,
                        'total' => $venta->totalpagoven,
                        'estado' => $venta->estadoven,
                        'empleado' => $venta->empleado->nombreemp ?? 'N/A'
                    ];
                });

            return response()->json([
                'success' => true,
                'count' => $ventas->count(),
                'ventas' => $ventas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de ventas
     * GET /api/v2/tech-ventas/estadisticas
     *
     * Query params:
     * - periodo: hoy, semana, mes, año (default: mes)
     */
    public function estadisticas(Request $request)
    {
        try {
            $periodo = $request->query('periodo', 'mes');

            $fechaInicio = match($periodo) {
                'hoy' => Carbon::today(),
                'semana' => Carbon::now()->startOfWeek(),
                'mes' => Carbon::now()->startOfMonth(),
                'año' => Carbon::now()->startOfYear(),
                default => Carbon::now()->startOfMonth()
            };

            $totalVentas = Venta::where('fechaven', '>=', $fechaInicio)->count();

            $montoTotal = Venta::where('fechaven', '>=', $fechaInicio)
                ->sum('totalpagoven');

            $porEstado = Venta::where('fechaven', '>=', $fechaInicio)
                ->groupBy('estadoven')
                ->select('estadoven', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(totalpagoven) as monto'))
                ->get();

            $promedioVenta = $totalVentas > 0 ? $montoTotal / $totalVentas : 0;

            return response()->json([
                'success' => true,
                'periodo' => $periodo,
                'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                'data' => [
                    'total_ventas' => $totalVentas,
                    'monto_total' => round($montoTotal, 2),
                    'promedio_por_venta' => round($promedioVenta, 2),
                    'por_estado' => $porEstado
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
