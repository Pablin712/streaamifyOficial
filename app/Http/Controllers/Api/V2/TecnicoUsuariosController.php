<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\ViewUsuarioActivo;
use App\Models\Cliente;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Controlador para gestión de Usuarios (Agente IA - Técnico)
 * Maneja operaciones sobre detalles_venta y usuarios activos
 *
 * Rutas: /api/v2/tech-usuarios/*
 */
class TecnicoUsuariosController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    /**
     * Desactivar usuarios con fecha vencida
     * POST /api/v2/tech-usuarios/desactivar-vencidos
     *
     * Body (opcional):
     * {
     *   "servicio": "NETFLIX", // Filtrar por servicio específico
     *   "dry_run": true // Modo simulación, no aplica cambios
     * }
     */
    public function desactivarVencidos(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'servicio' => 'nullable|string',
                'dry_run' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $dryRun = $request->input('dry_run', false);
            $servicio = $request->input('servicio');

            // Buscar usuarios vencidos activos
            $query = DetalleVenta::where('activodet', true)
                ->where('fechavendet', '<', Carbon::now());

            if ($servicio) {
                $query->whereHas('perfil.cuenta.valor.servicio', function($q) use ($servicio) {
                    $q->where('idser', strtoupper($servicio));
                });
            }

            $usuariosVencidos = $query->with([
                'cliente',
                'perfil.cuenta.valor.servicio'
            ])->get();

            $count = $usuariosVencidos->count();

            if ($dryRun) {
                // Modo simulación - solo mostrar qué se desactivaría
                return response()->json([
                    'success' => true,
                    'mode' => 'simulacion',
                    'message' => "Se desactivarían {$count} usuarios",
                    'usuarios' => $usuariosVencidos->map(function($det) {
                        return [
                            'iddet' => $det->iddet,
                            'cliente' => $det->cliente->nombrecli ?? 'N/A',
                            'cuenta' => $det->perfil->cuenta->idcue ?? 'N/A',
                            'servicio' => $det->perfil->cuenta->valor->servicio->nombreser ?? 'N/A',
                            'fecha_vencimiento' => $det->fechavendet,
                            'dias_vencido' => Carbon::parse($det->fechavendet)->diffInDays(Carbon::now())
                        ];
                    })
                ]);
            }

            // Aplicar desactivación
            $desactivados = [];
            foreach ($usuariosVencidos as $detalle) {
                $detalle->activodet = false;
                $detalle->save();

                $desactivados[] = [
                    'iddet' => $detalle->iddet,
                    'cliente' => $detalle->cliente->nombrecli ?? 'N/A',
                    'cuenta' => $detalle->perfil->cuenta->idcue ?? 'N/A'
                ];
            }

            return response()->json([
                'success' => true,
                'message' => "Se desactivaron {$count} usuarios vencidos",
                'count' => $count,
                'desactivados' => $desactivados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar usuarios vencidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios vencidos hoy que no han renovado
     * GET /api/v2/tech-usuarios/vencidos-hoy
     *
     * Query params:
     * - servicio: Filtrar por servicio específico
     */
    public function vencidosHoy(Request $request)
    {
        try {
            $servicio = $request->query('servicio');
            $hoy = Carbon::today();

            $query = DetalleVenta::where('activodet', true)
                ->whereDate('fechavendet', $hoy)
                ->with([
                    'cliente',
                    'venta',
                    'perfil.cuenta.valor.servicio'
                ]);

            if ($servicio) {
                $query->whereHas('perfil.cuenta.valor.servicio', function($q) use ($servicio) {
                    $q->where('idser', strtoupper($servicio));
                });
            }

            $usuariosVencidosHoy = $query->get()->map(function($det) {
                return [
                    'iddet' => $det->iddet,
                    'idcli' => $det->idcli,
                    'cliente' => [
                        'nombre' => $det->cliente->nombrecli ?? 'N/A',
                        'email' => $det->cliente->correocli ?? null,
                        'telefono' => $det->cliente->telefonocli ?? null,
                        'telegram' => $det->cliente->telegramcli ?? null
                    ],
                    'venta' => [
                        'idven' => $det->idven,
                        'fecha_venta' => $det->venta->fechaven ?? null
                    ],
                    'cuenta' => $det->perfil->cuenta->idcue ?? 'N/A',
                    'servicio' => $det->perfil->cuenta->valor->servicio->nombreser ?? 'N/A',
                    'fecha_vencimiento' => $det->fechavendet,
                    'monto_original' => $det->montodet
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $usuariosVencidosHoy->count(),
                'fecha' => $hoy->format('Y-m-d'),
                'usuarios' => $usuariosVencidosHoy
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios vencidos hoy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar cuenta y perfil de un usuario
     * POST /api/v2/tech-usuarios/cambiar-perfil
     *
     * Body:
     * {
     *   "iddet": "string",      // ID del detalle de venta
     *   "nuevo_idper": "string" // ID del nuevo perfil
     * }
     */
    public function cambiarPerfil(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'iddet' => 'required|string|exists:detalles_venta,iddet',
                'nuevo_idper' => 'required|string|exists:perfiles,idper'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $detalle = DetalleVenta::with([
                'cliente',
                'perfil.cuenta',
                'venta'
            ])->findOrFail($request->iddet);

            $perfilAnterior = $detalle->perfil;
            $nuevoPerfil = \App\Models\Perfil::with('cuenta')->findOrFail($request->nuevo_idper);

            // Validar que el nuevo perfil esté disponible
            $perfilOcupado = DetalleVenta::where('idper', $nuevoPerfil->idper)
                ->where('activodet', true)
                ->where('fechavendet', '>', Carbon::now())
                ->exists();

            if ($perfilOcupado) {
                return response()->json([
                    'success' => false,
                    'message' => 'El perfil destino ya está ocupado por otro usuario activo'
                ], 409);
            }

            // Realizar el cambio
            $detalle->idper = $nuevoPerfil->idper;
            $detalle->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuario movido exitosamente',
                'data' => [
                    'iddet' => $detalle->iddet,
                    'cliente' => $detalle->cliente->nombrecli ?? 'N/A',
                    'anterior' => [
                        'cuenta' => $perfilAnterior->cuenta->idcue ?? 'N/A',
                        'perfil' => $perfilAnterior->numeroper ?? 'N/A'
                    ],
                    'nuevo' => [
                        'cuenta' => $nuevoPerfil->cuenta->idcue ?? 'N/A',
                        'perfil' => $nuevoPerfil->numeroper ?? 'N/A'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar perfil de usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios por cliente
     * GET /api/v2/tech-usuarios/por-cliente/{idcli}
     */
    public function usuariosPorCliente($idcli)
    {
        try {
            $cliente = Cliente::findOrFail($idcli);

            $usuarios = DetalleVenta::where('idcli', $idcli)
                ->with([
                    'venta',
                    'perfil.cuenta.valor.servicio'
                ])
                ->orderBy('fechavendet', 'desc')
                ->get()
                ->map(function($det) {
                    $ahora = Carbon::now();
                    $vencimiento = Carbon::parse($det->fechavendet);

                    return [
                        'iddet' => $det->iddet,
                        'idven' => $det->idven,
                        'cuenta' => $det->perfil->cuenta->idcue ?? 'N/A',
                        'perfil' => $det->perfil->numeroper ?? 'N/A',
                        'servicio' => $det->perfil->cuenta->valor->servicio->nombreser ?? 'N/A',
                        'activo' => $det->activodet,
                        'fecha_vencimiento' => $det->fechavendet,
                        'dias_restantes' => $vencimiento->diffInDays($ahora, false),
                        'estado' => $vencimiento > $ahora ? 'vigente' : 'vencido',
                        'monto' => $det->montodet
                    ];
                });

            return response()->json([
                'success' => true,
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombre' => $cliente->nombrecli,
                    'email' => $cliente->correocli,
                    'telefono' => $cliente->telefonocli
                ],
                'count' => $usuarios->count(),
                'usuarios' => $usuarios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios del cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un usuario específico por ID de detalle
     * GET /api/v2/tech-usuarios/obtener/{iddet}
     *
     * Retorna toda la información del usuario activo
     */
    public function obtener($iddet)
    {
        try {
            $detalle = DetalleVenta::with([
                'cliente',
                'venta',
                'perfil.cuenta.valor.servicio',
                'perfil.cuenta.proveedor'
            ])->findOrFail($iddet);

            $ahora = Carbon::now();
            $vencimiento = Carbon::parse($detalle->fechavendet);
            $diasRestantes = $vencimiento->diffInDays($ahora, false);

            return response()->json([
                'success' => true,
                'data' => [
                    'iddet' => $detalle->iddet,
                    'activo' => $detalle->activodet,
                    'cliente' => [
                        'idcli' => $detalle->cliente->idcli ?? null,
                        'nombre' => $detalle->cliente->nombrecli ?? 'N/A',
                        'email' => $detalle->cliente->correocli ?? null,
                        'telefono' => $detalle->cliente->telefonocli ?? null,
                        'telegram' => $detalle->cliente->telegramcli ?? null,
                        'pais' => $detalle->cliente->pais ?? null
                    ],
                    'venta' => [
                        'idven' => $detalle->idven,
                        'fecha_venta' => $detalle->venta->fechaven ?? null,
                        'monto_total' => $detalle->venta->totven ?? null
                    ],
                    'cuenta' => [
                        'idcue' => $detalle->perfil->cuenta->idcue ?? 'N/A',
                        'usuario' => $detalle->perfil->cuenta->usuariocue ?? null,
                        'correo_cuenta' => $detalle->perfil->cuenta->correocue ?? null,
                        'activa' => $detalle->perfil->cuenta->activocue ?? false,
                        'fecha_vencimiento_cuenta' => $detalle->perfil->cuenta->fechavencue ?? null,
                        'proveedor' => $detalle->perfil->cuenta->proveedor->nombre ?? 'N/A'
                    ],
                    'perfil' => [
                        'idper' => $detalle->perfil->idper ?? 'N/A',
                        'numero' => $detalle->perfil->numeroper ?? 'N/A',
                        'pin' => $detalle->perfil->pinper ?? null,
                        'disponible' => $detalle->perfil->disponibleper ?? false
                    ],
                    'servicio' => [
                        'idser' => $detalle->perfil->cuenta->valor->servicio->idser ?? null,
                        'nombre' => $detalle->perfil->cuenta->valor->servicio->nombreser ?? 'N/A'
                    ],
                    'suscripcion' => [
                        'fecha_inicio' => $detalle->fechadet ?? null,
                        'fecha_vencimiento' => $detalle->fechavendet,
                        'dias_restantes' => $diasRestantes,
                        'estado' => $vencimiento > $ahora ? 'vigente' : 'vencido',
                        'monto_pagado' => $detalle->montodet
                    ],
                    'created_at' => $detalle->created_at,
                    'updated_at' => $detalle->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de usuarios activos
     * GET /api/v2/tech-usuarios/estadisticas
     */
    public function estadisticas()
    {
        try {
            $ahora = Carbon::now();

            $totalActivos = DetalleVenta::where('activodet', true)
                ->where('fechavendet', '>=', $ahora)
                ->count();

            $vencidosActivos = DetalleVenta::where('activodet', true)
                ->where('fechavendet', '<', $ahora)
                ->count();

            $porServicio = DB::table('detalles_venta as dv')
                ->join('perfiles as p', 'dv.idper', '=', 'p.idper')
                ->join('cuentas as c', 'p.idcue', '=', 'c.idcue')
                ->join('valores as v', 'c.idval', '=', 'v.idval')
                ->join('servicios as s', 'v.idser', '=', 's.idser')
                ->where('dv.activodet', true)
                ->where('dv.fechavendet', '>=', $ahora)
                ->groupBy('s.nombreser')
                ->select('s.nombreser as servicio', DB::raw('COUNT(*) as cantidad'))
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'activos_vigentes' => $totalActivos,
                    'vencidos_sin_desactivar' => $vencidosActivos,
                    'total_registros_activos' => $totalActivos + $vencidosActivos,
                    'por_servicio' => $porServicio
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas de usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
