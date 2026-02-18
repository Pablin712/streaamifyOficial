<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Servicio;
use App\Models\Valor;
use App\Models\ViewUsuarioActivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controlador para el Técnico de Cuentas (Agente IA)
 * Gestiona información y estadísticas de cuentas para análisis técnico
 *
 * Rutas: /api/v2/tech-accounts/*
 */
class TecnicoCuentasController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    /**
     * Resumen general de todas las cuentas
     * GET /api/v2/tech-accounts/resumen
     */
    public function resumenGeneral()
    {
        try {
            $activas = Cuenta::where('activocue', true)->count();

            // Cuentas por estado
            $ahora = Carbon::now();

            $vencidas = Cuenta::where('activocue', true)
                ->where('fechavencue', '<', $ahora)
                ->count();

            $vigentes = Cuenta::where('activocue', true)
                ->where('fechavencue', '>=', $ahora)
                ->count();

            $porVencer = Cuenta::where('activocue', true)
                ->whereBetween('fechavencue', [$ahora, $ahora->copy()->addDays(7)])
                ->count();

            // Cuentas dañadas (con caída)
            $danadas = Cuenta::where('caidacue', '>', 0)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'activas' => $activas,
                    'vencidas' => $vencidas,
                    'vigentes' => $vigentes,
                    'por_vencer_7_dias' => $porVencer,
                    'danadas' => $danadas,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen de cuentas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cuentas agrupadas por servicio
     * GET /api/v2/tech-accounts/por-servicio
     */
    public function cuentasPorServicio()
    {
        try {
            $cuentas = Cuenta::with(['valor.servicio'])
                ->where('activocue', true)
                ->get()
                ->groupBy(function($cuenta) {
                    return $cuenta->valor->servicio->nombreser ?? 'Sin Servicio';
                })
                ->map(function($grupo, $servicio) {
                    $total = $grupo->count();
                    $activas = $grupo->where('fechavencue', '>=', Carbon::now())->count();
                    $vencidas = $grupo->where('fechavencue', '<', Carbon::now())->count();
                    $danadas = $grupo->where('caidacue', '>', 0)->count();

                    return [
                        'servicio' => $servicio,
                        'total' => $total,
                        'activas' => $activas,
                        'vencidas' => $vencidas,
                        'danadas' => $danadas,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $cuentas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cuentas por servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estado detallado de cuentas por criterio
     * GET /api/v2/tech-accounts/estado?tipo=activas|vencidas|por-vencer|danadas
     */
    public function cuentasPorEstado(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'activas');
            $ahora = Carbon::now();

            $query = Cuenta::with(['valor.servicio', 'perfiles'])
                ->where('activocue', true);

            switch ($tipo) {
                case 'vencidas':
                    $query->where('fechavencue', '<', $ahora);
                    break;
                case 'por-vencer':
                    $dias = $request->input('dias', 7);
                    $query->whereBetween('fechavencue', [$ahora, $ahora->copy()->addDays($dias)]);
                    break;
                case 'danadas':
                    $query->where('caidacue', '>', 0);
                    break;
                case 'activas':
                default:
                    $query->where('fechavencue', '>=', $ahora);
                    break;
            }

            $cuentas = $query->get()->map(function($cuenta) {
                return [
                    'id' => $cuenta->idcue,
                    'servicio' => $cuenta->valor->servicio->nombreser ?? 'N/A',
                    'tipo' => $cuenta->valor->tipoval ?? 'N/A',
                    'usuario' => $cuenta->usuariocue,
                    'fecha_vencimiento' => $cuenta->fechavencue,
                    'dias_restantes' => Carbon::parse($cuenta->fechavencue)->diffInDays(Carbon::now(), false),
                    'costo' => $cuenta->valor->costoval ?? 0,
                    'perfiles_totales' => $cuenta->perfiles->count(),
                    'usuarios_activos' => $cuenta->usuarios_activos_attribute ?? 0,
                    'caidas' => $cuenta->caidacue,
                    'activa' => $cuenta->activocue,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo' => $tipo,
                    'total' => $cuentas->count(),
                    'cuentas' => $cuentas
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cuentas por estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de uso y ocupación de cuentas
     * GET /api/v2/tech-accounts/estadisticas-uso
     */
    public function estadisticasUso()
    {
        try {
            $cuentas = Cuenta::with(['valor.servicio', 'perfiles'])
                ->where('activocue', true)
                ->get();

            $estadisticas = $cuentas->map(function($cuenta) {
                $usuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                    ->where('fecha_vencimiento', '>', Carbon::now())
                    ->count();

                $capacidadMax = $cuenta->valor->pantmaxval ?? 0;
                $capacidadMin = $cuenta->valor->pantminval ?? 0;
                $ocupacion = $capacidadMax > 0 ? ($usuariosActivos / $capacidadMax) * 100 : 0;

                return [
                    'id' => $cuenta->idcue,
                    'servicio' => $cuenta->valor->servicio->nombreser ?? 'N/A',
                    'tipo' => $cuenta->valor->tipoval ?? 'N/A',
                    'usuarios_activos' => $usuariosActivos,
                    'capacidad_maxima' => $capacidadMax,
                    'capacidad_minima' => $capacidadMin,
                    'porcentaje_ocupacion' => round($ocupacion, 2),
                    'espacios_disponibles' => max(0, $capacidadMax - $usuariosActivos),
                    'es_rentable' => $usuariosActivos >= $capacidadMin,
                ];
            });

            $promedioOcupacion = $estadisticas->avg('porcentaje_ocupacion');
            $promedioUsuarios = $estadisticas->avg('usuarios_activos');
            $cuentasRentables = $estadisticas->where('es_rentable', true)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'resumen' => [
                        'promedio_ocupacion' => round($promedioOcupacion, 2),
                        'promedio_usuarios_por_cuenta' => round($promedioUsuarios, 2),
                        'cuentas_rentables' => $cuentasRentables,
                        'total_cuentas' => $estadisticas->count(),
                    ],
                    'detalle_por_cuenta' => $estadisticas
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de uso',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Servicios más vendidos (por cantidad de usuarios activos)
     * GET /api/v2/tech-accounts/mas-vendidos
     */
    public function serviciosMasVendidos()
    {
        try {
            // view_usuarios_activos ya tiene idcue, conectar directamente
            $servicios = DB::table('view_usuarios_activos')
                ->join('cuentas', 'view_usuarios_activos.idcue', '=', 'cuentas.idcue')
                ->join('valores', 'cuentas.idval', '=', 'valores.idval')
                ->join('servicios', 'valores.idser', '=', 'servicios.idser')
                ->where('view_usuarios_activos.fecha_vencimiento', '>', Carbon::now())
                ->select(
                    'servicios.nombreser',
                    DB::raw('COUNT(DISTINCT view_usuarios_activos.iddet) as total_usuarios'),
                    DB::raw('COUNT(DISTINCT cuentas.idcue) as total_cuentas'),
                    DB::raw('ROUND(COUNT(DISTINCT view_usuarios_activos.iddet) / NULLIF(COUNT(DISTINCT cuentas.idcue), 0), 2) as usuarios_por_cuenta')
                )
                ->groupBy('servicios.idser', 'servicios.nombreser')
                ->orderBy('total_usuarios', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $servicios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener servicios más vendidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Análisis financiero por servicio
     * GET /api/v2/tech-accounts/analisis-financiero
     */
    public function analisisFinanciero()
    {
        try {
            $servicios = Servicio::with(['valores.cuentas'])->get();

            $analisis = $servicios->map(function($servicio) {
                $cuentasActivas = Cuenta::whereHas('valor', function($q) use ($servicio) {
                    $q->where('idser', $servicio->idser);
                })
                ->where('activocue', true)
                ->with(['valor', 'perfiles'])
                ->get();

                $costoTotal = $cuentasActivas->sum(function($cuenta) {
                    return $cuenta->valor->costoval ?? 0;
                });

                $usuariosActivos = 0;
                $ingresoTotal = 0;

                foreach ($cuentasActivas as $cuenta) {
                    $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                        ->where('fecha_vencimiento', '>', Carbon::now())
                        ->get();

                    $usuariosActivos += $usuarios->count();

                    foreach ($usuarios as $usuario) {
                        $ingresoTotal += $usuario->detalle_venta->precio ?? 0;
                    }
                }

                $ganancia = $ingresoTotal - $costoTotal;
                $margen = $ingresoTotal > 0 ? (($ganancia / $ingresoTotal) * 100) : 0;

                return [
                    'servicio' => $servicio->nombreser,
                    'cuentas_activas' => $cuentasActivas->count(),
                    'usuarios_activos' => $usuariosActivos,
                    'costo_total' => round($costoTotal, 2),
                    'ingreso_total' => round($ingresoTotal, 2),
                    'ganancia_neta' => round($ganancia, 2),
                    'margen_porcentual' => round($margen, 2),
                    'ingreso_por_cuenta' => $cuentasActivas->count() > 0
                        ? round($ingresoTotal / $cuentasActivas->count(), 2)
                        : 0,
                ];
            })
            ->sortByDesc('ganancia_neta')
            ->values();

            return response()->json([
                'success' => true,
                'data' => $analisis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar análisis financiero',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Costo promedio por servicio
     * GET /api/v2/tech-accounts/costos-servicio
     */
    public function costosPorServicio()
    {
        try {
            $costos = DB::table('valores')
                ->join('servicios', 'valores.idser', '=', 'servicios.idser')
                ->join('cuentas', 'valores.idval', '=', 'cuentas.idval')
                ->where('cuentas.activocue', true)
                ->select(
                    'servicios.nombreser',
                    'valores.tipoval',
                    DB::raw('COUNT(cuentas.idcue) as total_cuentas'),
                    DB::raw('AVG(valores.costoval) as costo_promedio'),
                    DB::raw('MIN(valores.costoval) as costo_minimo'),
                    DB::raw('MAX(valores.costoval) as costo_maximo'),
                    DB::raw('SUM(valores.costoval) as costo_total')
                )
                ->groupBy('servicios.idser', 'servicios.nombreser', 'valores.tipoval')
                ->orderBy('servicios.nombreser')
                ->get()
                ->map(function($item) {
                    return [
                        'servicio' => $item->nombreser,
                        'tipo' => $item->tipoval,
                        'total_cuentas' => $item->total_cuentas,
                        'costo_promedio' => round($item->costo_promedio, 2),
                        'costo_minimo' => round($item->costo_minimo, 2),
                        'costo_maximo' => round($item->costo_maximo, 2),
                        'costo_total_mensual' => round($item->costo_total, 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $costos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener costos por servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Promedio de clientes por cuenta agrupado por servicio
     * GET /api/v2/tech-accounts/promedio-clientes
     */
    public function promedioClientesPorCuenta()
    {
        try {
            // view_usuarios_activos ya tiene idcue, conectar directamente
            $estadisticas = DB::table('view_usuarios_activos')
                ->join('cuentas', 'view_usuarios_activos.idcue', '=', 'cuentas.idcue')
                ->join('valores', 'cuentas.idval', '=', 'valores.idval')
                ->join('servicios', 'valores.idser', '=', 'servicios.idser')
                ->where('view_usuarios_activos.fecha_vencimiento', '>', Carbon::now())
                ->where('cuentas.activocue', true)
                ->select(
                    'servicios.nombreser',
                    'valores.tipoval',
                    DB::raw('COUNT(DISTINCT cuentas.idcue) as total_cuentas'),
                    DB::raw('COUNT(DISTINCT view_usuarios_activos.iddet) as total_usuarios'),
                    DB::raw('ROUND(COUNT(DISTINCT view_usuarios_activos.iddet) / NULLIF(COUNT(DISTINCT cuentas.idcue), 0), 2) as promedio_usuarios_por_cuenta'),
                    DB::raw('AVG(valores.pantmaxval) as capacidad_promedio')
                )
                ->groupBy('servicios.idser', 'servicios.nombreser', 'valores.tipoval')
                ->orderBy('promedio_usuarios_por_cuenta', 'desc')
                ->get()
                ->map(function($item) {
                    $ocupacion = $item->capacidad_promedio > 0
                        ? ($item->promedio_usuarios_por_cuenta / $item->capacidad_promedio) * 100
                        : 0;

                    return [
                        'servicio' => $item->nombreser,
                        'tipo' => $item->tipoval,
                        'total_cuentas' => $item->total_cuentas,
                        'total_usuarios' => $item->total_usuarios,
                        'promedio_usuarios_por_cuenta' => floatval($item->promedio_usuarios_por_cuenta),
                        'capacidad_promedio' => round($item->capacidad_promedio, 2),
                        'porcentaje_ocupacion' => round($ocupacion, 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener promedio de clientes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detalle completo de una cuenta específica
     * GET /api/v2/tech-accounts/cuenta/{id}
     */
    public function detalleCuenta($id)
    {
        try {
            $cuenta = Cuenta::with(['valor.servicio', 'valor.proveedor', 'perfiles', 'costos'])
                ->findOrFail($id);

            $usuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                ->where('fecha_vencimiento', '>', Carbon::now())
                ->with(['detalle_venta.venta.cliente'])
                ->get();

            $ingresoMensual = $usuariosActivos->sum(function($usuario) {
                return $usuario->detalle_venta->precio ?? 0;
            });

            $costoMensual = $cuenta->valor->costoval ?? 0;
            $ganancia = $ingresoMensual - $costoMensual;

            $clientes = $usuariosActivos->map(function($usuario) {
                return [
                    'cliente' => $usuario->detalle_venta?->venta?->cliente?->nombrecli ?? 'N/A',
                    'perfil' => $usuario->perfil,
                    'precio' => $usuario->detalle_venta?->precio ?? 0,
                    'fecha_vencimiento' => $usuario->fecha_vencimiento,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'cuenta' => [
                        'id' => $cuenta->idcue,
                        'servicio' => $cuenta->valor->servicio->nombreser ?? 'N/A',
                        'tipo' => $cuenta->valor->tipoval ?? 'N/A',
                        'proveedor' => $cuenta->valor->proveedor->nombrepro ?? 'N/A',
                        'usuario' => $cuenta->usuariocue,
                        'fecha_vencimiento' => $cuenta->fechavencue,
                        'activa' => $cuenta->activocue,
                        'caidas' => $cuenta->caidacue,
                    ],
                    'capacidad' => [
                        'minima' => $cuenta->valor->pantminval ?? 0,
                        'maxima' => $cuenta->valor->pantmaxval ?? 0,
                        'usuarios_activos' => $usuariosActivos->count(),
                        'espacios_disponibles' => max(0, ($cuenta->valor->pantmaxval ?? 0) - $usuariosActivos->count()),
                    ],
                    'financiero' => [
                        'costo_mensual' => round($costoMensual, 2),
                        'ingreso_mensual' => round($ingresoMensual, 2),
                        'ganancia' => round($ganancia, 2),
                        'rentable' => $usuariosActivos->count() >= ($cuenta->valor->pantminval ?? 0),
                    ],
                    'clientes' => $clientes
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle de cuenta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // =====================================================================
    // ACCIONES AUTOMÁTICAS - Operaciones masivas para el técnico
    // =====================================================================

    /**
     * Mover todos los clientes de un servicio específico a la Mesa de Trabajo
     * POST /api/v2/tech-accounts/acciones/mover-servicio-a-mesa
     *
     * Body: {"servicio_id": "NETFLIX"}
     */
    public function moverServicioAMesa(Request $request)
    {
        try {
            $request->validate([
                'servicio_id' => 'required|string'
            ]);

            $servicioId = strtoupper($request->servicio_id);

            // Obtener la mesa de trabajo para este servicio
            $mesaTrabajo = Cuenta::where('idcue', 'LIKE', "%{$servicioId}%Atencion")
                ->where('activocue', true)
                ->first();

            if (!$mesaTrabajo) {
                return response()->json([
                    'success' => false,
                    'message' => "No existe mesa de trabajo para el servicio {$servicioId}"
                ], 404);
            }

            // Obtener todas las cuentas activas de este servicio (excepto la mesa)
            $cuentas = Cuenta::whereHas('valor.servicio', function($q) use ($servicioId) {
                $q->where('idser', $servicioId);
            })
            ->where('activocue', true)
            ->where('idcue', 'NOT LIKE', '%Atencion')
            ->get();

            $usuariosMovidos = 0;
            $errores = [];

            foreach ($cuentas as $cuenta) {
                // Obtener usuarios activos de esta cuenta
                $usuarios = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                    ->where('fecha_vencimiento', '>', Carbon::now())
                    ->get();

                foreach ($usuarios as $usuario) {
                    try {
                        // Buscar perfil destino en la mesa de trabajo
                        $perfilDestino = DB::table('perfiles')
                            ->where('idcue', $mesaTrabajo->idcue)
                            ->where('numeroper', $usuario->perfil)
                            ->first();

                        if ($perfilDestino) {
                            // Mover el usuario actualizando el perfil en DetalleVenta
                            DB::table('detalles_venta')
                                ->where('iddet', $usuario->iddet)
                                ->update(['idper' => $perfilDestino->idper]);

                            $usuariosMovidos++;
                        }
                    } catch (\Exception $e) {
                        $errores[] = "Error moviendo usuario {$usuario->iddet}: {$e->getMessage()}";
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'servicio' => $servicioId,
                    'mesa_trabajo' => $mesaTrabajo->idcue,
                    'cuentas_procesadas' => $cuentas->count(),
                    'usuarios_movidos' => $usuariosMovidos,
                    'errores' => $errores
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mover usuarios a mesa de trabajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar masivamente usuarios de cuentas específicas
     * POST /api/v2/tech-accounts/acciones/desactivar-usuarios
     *
     * Body: {"cuenta_ids": ["NET001", "NET002"], "razon": "Cuentas a borrar"}
     */
    public function desactivarUsuariosMasivo(Request $request)
    {
        try {
            $request->validate([
                'cuenta_ids' => 'required|array',
                'cuenta_ids.*' => 'string',
                'razon' => 'nullable|string'
            ]);

            $cuentaIds = $request->cuenta_ids;
            $razon = $request->razon ?? 'Desactivación masiva';

            $usuariosDesactivados = 0;
            $detalles = [];

            foreach ($cuentaIds as $cuentaId) {
                $cuenta = Cuenta::find($cuentaId);

                if (!$cuenta) {
                    $detalles[] = [
                        'cuenta' => $cuentaId,
                        'status' => 'no_encontrada'
                    ];
                    continue;
                }

                // Obtener todos los usuarios activos de esta cuenta
                $usuarios = ViewUsuarioActivo::where('idcue', $cuentaId)->get();

                $desactivados = 0;
                foreach ($usuarios as $usuario) {
                    DB::table('detalles_venta')
                        ->where('iddet', $usuario->iddet)
                        ->update(['activodet' => false]);
                    $desactivados++;
                }

                $usuariosDesactivados += $desactivados;
                $detalles[] = [
                    'cuenta' => $cuentaId,
                    'servicio' => $cuenta->valor->servicio->nombreser ?? 'N/A',
                    'usuarios_desactivados' => $desactivados,
                    'status' => 'completado'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_cuentas_procesadas' => count($cuentaIds),
                    'total_usuarios_desactivados' => $usuariosDesactivados,
                    'razon' => $razon,
                    'detalles' => $detalles
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar y preparar cuentas para eliminación
     * POST /api/v2/tech-accounts/acciones/limpiar-cuentas
     *
     * Body: {"servicio_id": "NETFLIX", "solo_vacias": true}
     */
    public function limpiarCuentas(Request $request)
    {
        try {
            $request->validate([
                'servicio_id' => 'required|string',
                'solo_vacias' => 'nullable|boolean'
            ]);

            $servicioId = strtoupper($request->servicio_id);
            $soloVacias = $request->solo_vacias ?? true;

            // Obtener cuentas del servicio
            $query = Cuenta::whereHas('valor.servicio', function($q) use ($servicioId) {
                $q->where('idser', $servicioId);
            })
            ->where('activocue', true)
            ->where('idcue', 'NOT LIKE', '%Atencion');

            if ($soloVacias) {
                // Solo cuentas sin usuarios activos
                $query->whereDoesntHave('usuarios_activos');
            }

            $cuentas = $query->get();
            $cuentasLimpias = [];
            $cuentasConUsuarios = [];

            foreach ($cuentas as $cuenta) {
                $usuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)
                    ->where('fecha_vencimiento', '>', Carbon::now())
                    ->count();

                if ($usuariosActivos === 0) {
                    $cuentasLimpias[] = [
                        'id' => $cuenta->idcue,
                        'servicio' => $cuenta->valor->servicio->nombreser ?? 'N/A',
                        'tipo' => $cuenta->valor->tipoval ?? 'N/A',
                        'fecha_vencimiento' => $cuenta->fechavencue,
                        'lista_para_borrar' => true
                    ];
                } else {
                    $cuentasConUsuarios[] = [
                        'id' => $cuenta->idcue,
                        'usuarios_activos' => $usuariosActivos,
                        'requiere_limpieza' => true
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'servicio' => $servicioId,
                    'total_cuentas_evaluadas' => $cuentas->count(),
                    'cuentas_limpias' => count($cuentasLimpias),
                    'cuentas_con_usuarios' => count($cuentasConUsuarios),
                    'listas_para_borrar' => $cuentasLimpias,
                    'requieren_atencion' => $cuentasConUsuarios
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar cuentas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios de un servicio listos para mover
     * GET /api/v2/tech-accounts/acciones/usuarios-por-mover?servicio_id=NETFLIX
     */
    public function usuariosPorMover(Request $request)
    {
        try {
            $request->validate([
                'servicio_id' => 'required|string'
            ]);

            $servicioId = strtoupper($request->servicio_id);

            // view_usuarios_activos ya tiene idcue, conectar directamente
            $usuarios = DB::table('view_usuarios_activos')
                ->join('cuentas', 'view_usuarios_activos.idcue', '=', 'cuentas.idcue')
                ->join('valores', 'cuentas.idval', '=', 'valores.idval')
                ->join('servicios', 'valores.idser', '=', 'servicios.idser')
                ->where('servicios.idser', $servicioId)
                ->where('cuentas.idcue', 'NOT LIKE', '%Atencion')
                ->where('view_usuarios_activos.fecha_vencimiento', '>', Carbon::now())
                ->select(
                    'view_usuarios_activos.iddet',
                    'view_usuarios_activos.nombre_cliente',
                    'view_usuarios_activos.perfil',
                    'cuentas.idcue',
                    'servicios.nombreser',
                    'view_usuarios_activos.fecha_vencimiento'
                )
                ->get()
                ->groupBy('idcue')
                ->map(function($usuarios, $idcue) {
                    return [
                        'cuenta' => $idcue,
                        'total_usuarios' => $usuarios->count(),
                        'usuarios' => $usuarios->map(function($u) {
                            return [
                                'iddet' => $u->iddet,
                                'cliente' => $u->nombre_cliente,
                                'perfil' => $u->perfil,
                                'vencimiento' => $u->fecha_vencimiento
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'servicio' => $servicioId,
                    'total_cuentas' => $usuarios->count(),
                    'total_usuarios' => $usuarios->sum('total_usuarios'),
                    'cuentas' => $usuarios->values()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clientes que vencen HOY (para enviar notificaciones)
     * GET /api/v2/tech-accounts/clientes-vencen-hoy
     *
     * Query params:
     * - servicio: Filtrar por servicio específico (opcional)
     *
     * Retorna: Lista de clientes únicos con sus datos de contacto y servicios que vencen
     */
    public function clientesVencenHoy(Request $request)
    {
        try {
            $hoy = Carbon::today();
            $finDelDia = Carbon::today()->endOfDay();

            // Query base: usuarios que vencen HOY
            // view_usuarios_activos tiene: idcli, nombre_cliente, idven, iddet, idcue, perfil, fecha_vencimiento
            $query = DB::table('view_usuarios_activos')
                ->join('detalles_venta', 'view_usuarios_activos.iddet', '=', 'detalles_venta.iddet')
                ->join('ventas', 'detalles_venta.idven', '=', 'ventas.idven')
                ->join('clientes', 'ventas.idcli', '=', 'clientes.idcli')
                ->join('cuentas', 'view_usuarios_activos.idcue', '=', 'cuentas.idcue')
                ->join('valores', 'cuentas.idval', '=', 'valores.idval')
                ->join('servicios', 'valores.idser', '=', 'servicios.idser')
                ->whereBetween('view_usuarios_activos.fecha_vencimiento', [$hoy, $finDelDia])
                ->where('detalles_venta.activodet', true);

            // Filtro opcional por servicio
            if ($request->has('servicio')) {
                $query->where('servicios.idser', strtoupper($request->servicio));
            }

            $usuarios = $query->select(
                'clientes.idcli',
                'clientes.nombrecli',
                'clientes.telefonocli',
                'clientes.email',
                'clientes.telegram_chat_id',
                'servicios.nombreser',
                'view_usuarios_activos.iddet',
                'view_usuarios_activos.fecha_vencimiento',
                'detalles_venta.montodet'
            )
            ->get();

            // Agrupar por cliente
            $clientesAgrupados = $usuarios->groupBy('idcli')->map(function($servicios, $idcli) {
                $cliente = $servicios->first();

                return [
                    'idcli' => $cliente->idcli,
                    'nombre' => $cliente->nombrecli,
                    'telefono' => $cliente->telefonocli,
                    'email' => $cliente->email,
                    'telegram_chat_id' => $cliente->telegram_chat_id,
                    'servicios_vencen_hoy' => $servicios->map(function($s) {
                        return [
                            'iddet' => $s->iddet,
                            'servicio' => $s->nombreser,
                            'fecha_vencimiento' => $s->fecha_vencimiento,
                            'monto' => round($s->montodet, 2)
                        ];
                    })->values(),
                    'total_servicios' => $servicios->count(),
                    'monto_total' => round($servicios->sum('montodet'), 2)
                ];
            })->values();

            return response()->json([
                'success' => true,
                'fecha' => $hoy->format('Y-m-d'),
                'total_clientes' => $clientesAgrupados->count(),
                'total_servicios' => $usuarios->count(),
                'clientes' => $clientesAgrupados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes que vencen hoy',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
