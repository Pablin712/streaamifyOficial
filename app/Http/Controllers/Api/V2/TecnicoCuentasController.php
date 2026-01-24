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
            $cuentasActivas = Cuenta::where('activocue', true);

            $vencidas = (clone $cuentasActivas)->where('fechavencue', '<', $ahora)->count();
            $vigentes = (clone $cuentasActivas)->where('fechavencue', '>=', $ahora)->count();
            $porVencer = (clone $cuentasActivas)
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
            $servicios = DB::table('view_usuarios_activos')
                ->join('perfiles', 'view_usuarios_activos.idper', '=', 'perfiles.idper')
                ->join('cuentas', 'perfiles.idcue', '=', 'cuentas.idcue')
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
            $estadisticas = DB::table('view_usuarios_activos')
                ->join('perfiles', 'view_usuarios_activos.idper', '=', 'perfiles.idper')
                ->join('cuentas', 'perfiles.idcue', '=', 'cuentas.idcue')
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
}
