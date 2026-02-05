<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\ViewUsuarioActivo;
use App\Models\Cliente;
use App\Models\Venta;
use App\Services\CuentaService;
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
    protected $cuentaService;

    public function __construct(CuentaService $cuentaService)
    {
        $this->cuentaService = $cuentaService;
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

    /**
     * Mover cliente de mesa de trabajo a otro servicio
     * POST /api/v2/tech-usuarios/mover-otro-servicio
     *
     * Body:
     * {
     *   "iddet": 12345,          // ID del detalle de venta (usuario)
     *   "servicio_destino": "MAX" // Servicio al que se moverá (NETFLIX, DISNEYP, MAX, etc)
     * }
     *
     * Respuesta exitosa:
     * {
     *   "success": true,
     *   "message": "Cliente movido exitosamente",
     *   "data": {
     *     "cliente": "Juan Pérez",
     *     "servicio_origen": "NETFLIX",
     *     "servicio_destino": "MAX",
     *     "credenciales": {
     *       "cuenta": "usuario@email.com",
     *       "contrasena": "password123",
     *       "perfil": 2,
     *       "pin": "1234"
     *     }
     *   }
     * }
     */
    public function moverClienteOtroServicio(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'iddet' => 'required|integer|exists:detalles_venta,iddet',
                'servicio_destino' => 'required|string|in:NETFLIX,DISNEYP,DISNEYS,MAX,PRIME,PARAMOUNT,CRUNCHY,SPOTIFY,MAGIS'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            $iddet = $request->input('iddet');
            $servicioDestino = strtoupper($request->input('servicio_destino'));

            // Obtener el usuario activo
            $usuario = ViewUsuarioActivo::where('iddet', $iddet)->first();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado o no está activo'
                ], 404);
            }

            // Validar que no sea el mismo servicio
            $servicioOrigen = $usuario->cuenta->valor->idser;
            if ($servicioOrigen === $servicioDestino) {
                return response()->json([
                    'success' => false,
                    'message' => "El cliente ya está en el servicio {$servicioDestino}. Use el endpoint de cambio de perfil si desea moverlo dentro del mismo servicio."
                ], 400);
            }

            // Ejecutar la mudanza usando el servicio
            $respuesta = $this->cuentaService->mudarClienteAOtroServicio($usuario, $servicioDestino);

            // Verificar el resultado
            if ($respuesta === 'error_no_disponible') {
                return response()->json([
                    'success' => false,
                    'message' => "No hay cuentas disponibles en el servicio {$servicioDestino}",
                    'detalle' => 'No se encontraron cuentas activas con espacios libres en el servicio destino'
                ], 404);
            } elseif ($respuesta === 'error_sin_perfil') {
                return response()->json([
                    'success' => false,
                    'message' => "No hay perfiles disponibles en las cuentas del servicio {$servicioDestino}",
                    'detalle' => 'Se encontraron cuentas pero no tienen perfiles libres'
                ], 404);
            }

            // Obtener los datos actualizados del usuario
            $usuarioActualizado = ViewUsuarioActivo::where('iddet', $iddet)->first();

            return response()->json([
                'success' => true,
                'message' => 'Cliente movido exitosamente a otro servicio',
                'data' => [
                    'cliente' => $usuario->nombre_cliente,
                    'iddet' => $iddet,
                    'servicio_origen' => $servicioOrigen,
                    'servicio_destino' => $servicioDestino,
                    'cuenta_anterior' => [
                        'idcue' => $usuario->idcue,
                        'usuario' => $usuario->usuariocue
                    ],
                    'cuenta_nueva' => [
                        'idcue' => $usuarioActualizado->idcue,
                        'usuario' => $usuarioActualizado->usuariocue,
                        'contrasena' => $usuarioActualizado->contrasenacue,
                        'perfil' => $usuarioActualizado->perfil,
                        'pin' => $usuarioActualizado->profile->pinper ?? null
                    ],
                    'mensaje_completo' => $respuesta
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mover cliente a otro servicio',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Listar usuarios activos con filtros simplificados
     *
     * @endpoint GET /api/v2/tech-usuarios/listar
     * @access Bot Telegram (Bearer Token)
     *
     * @queryParam servicio string Filtrar por servicio específico (NETFLIX, MAX, PRIME, CRUNCHY, etc.)
     * @queryParam cliente string Buscar por nombre de cliente (búsqueda parcial, case insensitive)
     * @queryParam limit integer Límite de resultados por página (default: 50, max: 200)
     * @queryParam offset integer Offset para paginación (default: 0)
     *
     * @examples
     * GET /api/v2/tech-usuarios/listar?servicio=NETFLIX
     * GET /api/v2/tech-usuarios/listar?cliente=Juan
     * GET /api/v2/tech-usuarios/listar?servicio=CRUNCHY&cliente=Pablo
     * GET /api/v2/tech-usuarios/listar?servicio=MAX&limit=20&offset=20
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Se encontraron 15 usuarios",
     *   "data": [
     *     {
     *       "iddet": 12345,
     *       "cliente": {
     *         "idcli": 789,
     *         "nombre": "Juan Pérez",
     *         "whatsapp": "573001234567",
     *         "telegram_id": 123456789
     *       },
     *       "servicio": "Netflix",
     *       "cuenta": {
     *         "idcue": "NETFLIX#001",
     *         "usuario": "netflix@email.com",
     *         "contrasena": "password123",
     *         "estado": "activa",
     *         "caida": false
     *       },
     *       "perfil": {
     *         "numero": 2,
     *         "pin": "1234"
     *       },
     *       "vencimiento": {
     *         "fecha": "2026-02-15",
     *         "dias_restantes": 11,
     *         "estado": "vigente"
     *       },
     *       "venta": {
     *         "idven": 456,
     *         "monto": 15000,
     *         "fecha_venta": "2026-01-15"
     *       },
     *       "estado_cobro": "COBRADO",
     *       "activo": true
     *     }
     *   ],
     *   "pagination": {
     *     "total": 150,
     *     "count": 15,
     *     "limit": 50,
     *     "offset": 0,
     *     "has_more": true
     *   },
     *   "filters_applied": {
     *     "servicio": "NETFLIX",
     *     "cliente": "Juan"
     *   }
     * }
     *
     * @note Orden fijo por fecha_vencimiento ASC
     * @note Sin filtros retorna todos los usuarios activos
     * @see docs/37-API-LISTAR-USUARIOS-FILTROS.md
     */
    public function listarUsuariosConFiltros(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'servicio' => 'nullable|string|max:50',
                'cliente' => 'nullable|string|max:100',
                'limit' => 'nullable|integer|min:1|max:200',
                'offset' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validación fallida',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Parámetros de paginación
            $limit = $request->input('limit', 50);
            $offset = $request->input('offset', 0);

            // Construir query
            $query = ViewUsuarioActivo::with(['cuenta.valor.servicio', 'cliente', 'venta', 'profile', 'detalle_venta']);

            // Filtro por servicio (importante)
            if ($request->filled('servicio')) {
                $servicio = strtoupper(trim($request->input('servicio')));
                $query->whereHas('cuenta.valor.servicio', function($q) use ($servicio) {
                    $q->where('idser', '=', $servicio);
                });
            }

            // Filtro por nombre de cliente (búsqueda parcial - importante)
            if ($request->filled('cliente')) {
                $nombreCliente = trim($request->input('cliente'));
                $query->where('nombre_cliente', 'LIKE', "%{$nombreCliente}%");
            }

            // Contar total antes de paginar
            $total = $query->count();

            // Ordenar por fecha de vencimiento
            $query->orderBy('fecha_vencimiento', 'asc');

            // Paginación
            $usuarios = $query->offset($offset)
                ->limit($limit)
                ->get();

            // Formatear resultados
            $resultados = $usuarios->map(function($usuario) {
                $ahora = Carbon::now();
                $fechaVencimiento = Carbon::parse($usuario->fecha_vencimiento);
                $diasRestantes = $ahora->diffInDays($fechaVencimiento, false);

                $estadoUsuario = 'vencido';
                if ($diasRestantes > 7) {
                    $estadoUsuario = 'vigente';
                } elseif ($diasRestantes >= 0) {
                    $estadoUsuario = 'por_vencer';
                }

                return [
                    'iddet' => $usuario->iddet,
                    'cliente' => [
                        'idcli' => $usuario->idcli,
                        'nombre' => $usuario->nombre_cliente,
                        'whatsapp' => $usuario->cliente->whatsappcli ?? null,
                        'telegram_id' => $usuario->cliente->telegram_id ?? null
                    ],
                    'servicio' => $usuario->cuenta->valor->servicio->nombreser ?? $usuario->cuenta->valor->idser,
                    'cuenta' => [
                        'idcue' => $usuario->idcue,
                        'usuario' => $usuario->usuariocue,
                        'contrasena' => $usuario->contrasenacue,
                        'estado' => $usuario->cuenta->activocue ? 'activa' : 'inactiva',
                        'caida' => $usuario->cuenta->caidacue > 0
                    ],
                    'perfil' => [
                        'numero' => $usuario->perfil,
                        'pin' => $usuario->profile->pinper ?? null
                    ],
                    'vencimiento' => [
                        'fecha' => $usuario->fecha_vencimiento,
                        'dias_restantes' => (int)$diasRestantes,
                        'estado' => $estadoUsuario
                    ],
                    'venta' => [
                        'idven' => $usuario->idven,
                        'monto' => $usuario->venta->total ?? null,
                        'fecha_venta' => $usuario->venta->fechaven ?? null
                    ],
                    'estado_cobro' => $usuario->detalle_venta->estado ?? 'PENDIENTE',
                    'activo' => $usuario->detalle_venta->activodet ?? true
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Se encontraron {$resultados->count()} usuarios",
                'data' => $resultados,
                'pagination' => [
                    'total' => $total,
                    'count' => $resultados->count(),
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total
                ],
                'filters_applied' => array_filter([
                    'servicio' => $request->input('servicio'),
                    'cliente' => $request->input('cliente')
                ])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar usuarios',
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
