<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Notifications\ComprasRealizadas;
use App\Notifications\NuevoSoporteCliente;
use App\Notifications\PedidosPendientes;
use App\Http\Controllers\Controller;
use App\Models\Banco;
use App\Models\Cuenta;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\Pedido;
use App\Models\Perfil;
use App\Models\Producto;
use App\Models\Recarga;
use App\Models\Soporte;
use App\Models\Venta;
use App\Models\ViewUsuarioActivo;
use App\Support\ClienteAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class ChatAssistantController extends Controller
{
    public function __construct()
    {
        request()->headers->set('Accept', 'application/json');
    }

    public function clientePorTelefono(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:50',
            'include_renovables' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $telefono = ClienteAuth::normalizePhone($request->input('telefono'));
        $cliente = Cliente::buscarPorTelefonoNormalizado($telefono);
        $includeRenovables = $request->boolean('include_renovables', true);

        if (!$cliente) {
            return response()->json([
                'success' => true,
                'found' => false,
                'message' => 'Cliente no encontrado por telefono.',
                'data' => [
                    'telefono_consultado' => $telefono,
                ],
            ]);
        }

        $ventasRenovables = [];
        $resumenRenovables = [
            'ventas_con_detalles_activos' => 0,
            'detalles_activos' => 0,
            'detalles_renovables' => 0,
            'por_vencer' => 0,
            'vencidos' => 0,
        ];

        if ($includeRenovables) {
            $hoy = Carbon::today();

            $detallesActivos = DetalleVenta::query()
                ->with(['perfil.cuenta.valor.servicio'])
                ->where('activodet', true)
                ->whereHas('venta', function ($query) use ($cliente) {
                    $query->where('idcli', $cliente->idcli);
                })
                ->orderByDesc('idven')
                ->orderBy('fechavendet')
                ->get();

            $resumenRenovables['detalles_activos'] = $detallesActivos->count();

            $ventasRenovables = $detallesActivos
                ->groupBy('idven')
                ->map(function ($detalles, $idven) use ($hoy, &$resumenRenovables) {
                    $detallesFormateados = $detalles->map(function (DetalleVenta $detalle) use ($hoy, &$resumenRenovables) {
                        $fechaVencimiento = $detalle->fechavendet ? Carbon::parse($detalle->fechavendet)->startOfDay() : null;
                        $diasRestantes = $fechaVencimiento ? $hoy->diffInDays($fechaVencimiento, false) : null;

                        $estado = 'activo';
                        if ($diasRestantes !== null && $diasRestantes < 0) {
                            $estado = 'vencido';
                            $resumenRenovables['vencidos']++;
                        } elseif ($diasRestantes !== null && $diasRestantes <= 3) {
                            $estado = 'por_vencer';
                            $resumenRenovables['por_vencer']++;
                        }

                        $renovable = in_array($estado, ['vencido', 'por_vencer'], true);
                        if ($renovable) {
                            $resumenRenovables['detalles_renovables']++;
                        }

                        return [
                            'iddet' => (int) $detalle->iddet,
                            'idven' => $detalle->idven,
                            'idper' => $detalle->idper,
                            'servicio' => $detalle->perfil?->cuenta?->valor?->servicio?->nombreser,
                            'cuenta' => $detalle->perfil?->cuenta?->usuariocue,
                            'perfil' => $detalle->perfil?->numeroper,
                            'fecha_vencimiento' => $fechaVencimiento?->toDateString(),
                            'dias_restantes' => $diasRestantes,
                            'estado' => $estado,
                            'renovable' => $renovable,
                            'monto_mensual_actual' => round((float) $detalle->montodet, 2),
                        ];
                    })->values();

                    $detallesRenovables = $detallesFormateados->where('renovable', true)->values();

                    if ($detallesFormateados->isNotEmpty()) {
                        $resumenRenovables['ventas_con_detalles_activos']++;
                    }

                    return [
                        'idven' => $idven,
                        'cantidad_detalles_activos' => $detallesFormateados->count(),
                        'cantidad_detalles_renovables' => $detallesRenovables->count(),
                        'puede_renovar' => $detallesRenovables->isNotEmpty(),
                        'detalles' => $detallesFormateados,
                        'detalles_renovables' => $detallesRenovables,
                    ];
                })
                ->values();
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'message' => 'Cliente encontrado.',
            'data' => [
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombrecli' => $cliente->nombrecli,
                    'telefonocli' => $cliente->telefonocli,
                    'email' => $cliente->email,
                    'saldo' => (float) $cliente->saldo,
                ],
                'ventas_renovables' => $ventasRenovables,
                'resumen_renovables' => $resumenRenovables,
            ],
        ]);
    }

    public function crearCliente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefonocli' => 'required|string|max:50',
            'nombrecli' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $telefono = ClienteAuth::normalizePhone($request->input('telefonocli'));
        $clienteExistente = Cliente::buscarPorTelefonoNormalizado($telefono);

        if ($clienteExistente) {
            $updates = [];

            if ($request->filled('nombrecli') && empty($clienteExistente->nombrecli)) {
                $updates['nombrecli'] = ClienteAuth::normalizeName($request->input('nombrecli'));
            }

            if ($request->filled('email') && empty($clienteExistente->email)) {
                $updates['email'] = $request->input('email');
            }

            if ($updates !== []) {
                $clienteExistente->update($updates);
                $clienteExistente->refresh();
            }

            return response()->json([
                'success' => true,
                'created' => false,
                'message' => 'Cliente ya existia. Se reutilizo el registro.',
                'data' => [
                    'cliente' => [
                        'idcli' => $clienteExistente->idcli,
                        'nombrecli' => $clienteExistente->nombrecli,
                        'telefonocli' => $clienteExistente->telefonocli,
                        'email' => $clienteExistente->email,
                    ],
                ],
            ]);
        }

        $nombre = $request->filled('nombrecli')
            ? ClienteAuth::normalizeName($request->input('nombrecli'))
            : $this->nextWhatsappClientName();

        $cliente = Cliente::create([
            'nombrecli' => $nombre,
            'telefonocli' => $telefono,
            'email' => $request->input('email'),
            'saldo' => 0,
        ]);

        return response()->json([
            'success' => true,
            'created' => true,
            'message' => 'Cliente creado correctamente.',
            'data' => [
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombrecli' => $cliente->nombrecli,
                    'telefonocli' => $cliente->telefonocli,
                    'email' => $cliente->email,
                ],
            ],
        ], 201);
    }

    public function usuariosActivosPorTelefono(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $telefono = ClienteAuth::normalizePhone($request->input('telefono'));
        $cliente = Cliente::buscarPorTelefonoNormalizado($telefono);

        if (!$cliente) {
            return response()->json([
                'success' => true,
                'found' => false,
                'message' => 'Cliente no encontrado por telefono.',
                'data' => [
                    'telefono_consultado' => $telefono,
                    'usuarios' => [],
                    'resumen' => [
                        'total' => 0,
                        'activos' => 0,
                        'por_vencer' => 0,
                        'vencidos' => 0,
                    ],
                ],
            ]);
        }

        $detalles = DetalleVenta::query()
            ->with(['perfil.cuenta.valor.servicio'])
            ->where('activodet', true)
            ->whereHas('venta', function ($query) use ($cliente) {
                $query->where('idcli', $cliente->idcli);
            })
            ->orderByDesc('fechavendet')
            ->get();

        $hoy = Carbon::today();
        $resumen = [
            'total' => 0,
            'activos' => 0,
            'por_vencer' => 0,
            'vencidos' => 0,
            'cuentas_caidas' => 0,
        ];

        $usuarios = $detalles->map(function (DetalleVenta $detalle) use ($hoy, &$resumen, $cliente) {
            $fechaVencimiento = $detalle->fechavendet ? Carbon::parse($detalle->fechavendet)->startOfDay() : null;
            $diasRestantes = $fechaVencimiento ? $hoy->diffInDays($fechaVencimiento, false) : null;

            $estado = 'activo';
            if ($diasRestantes !== null && $diasRestantes < 0) {
                $estado = 'vencido';
            } elseif ($diasRestantes !== null && $diasRestantes <= 3) {
                $estado = 'por_vencer';
            }

            $resumen['total']++;
            if ($estado === 'activo') {
                $resumen['activos']++;
            } elseif ($estado === 'por_vencer') {
                $resumen['por_vencer']++;
            } else {
                $resumen['vencidos']++;
            }

            $cuentaCaida = (bool) ($detalle->perfil?->cuenta?->caidacue ?? false);
            if ($cuentaCaida) {
                $resumen['cuentas_caidas']++;
            }

            $servicio = $detalle->perfil?->cuenta?->valor?->servicio?->nombreser;
            $perfilNumero = is_numeric($detalle->perfil?->numeroper) ? (int) $detalle->perfil?->numeroper : null;
            $credencialesEntrega = $this->resolverCredencialesEntrega(
                servicio: $servicio,
                perfilNumero: $perfilNumero,
                usuarioCuenta: $detalle->perfil?->cuenta?->usuariocue,
                contrasenaCuenta: $detalle->perfil?->cuenta?->contrasenacue,
                pinPerfil: $detalle->perfil?->pinper,
            );

            return [
                'iddet' => $detalle->iddet,
                'idven' => $detalle->idven,
                'idcli' => $cliente->idcli,
                'idcue' => $detalle->perfil?->cuenta?->idcue,
                'idper' => $detalle->idper,
                'servicio' => $servicio,
                'cuenta' => $credencialesEntrega['cuenta'],
                'contrasenacue' => $credencialesEntrega['contrasenacue'],
                'perfil' => $detalle->perfil?->numeroper,
                'pinper' => $credencialesEntrega['pinper'],
                'credencial_regla' => $credencialesEntrega['regla'],
                'fecha_vencimiento' => $fechaVencimiento?->toDateString(),
                'dias_restantes' => $diasRestantes,
                'estado' => $estado,
                'cuenta_caidacue' => $cuentaCaida,
                'cuenta_activa' => (bool) ($detalle->perfil?->cuenta?->activocue ?? false),
                'soporte_ref' => [
                    'idcli' => $cliente->idcli,
                    'idcue' => $detalle->perfil?->cuenta?->idcue,
                    'iddet' => $detalle->iddet,
                ],
            ];
        })->values();

        return response()->json([
            'success' => true,
            'found' => true,
            'message' => 'Usuarios del cliente obtenidos correctamente.',
            'data' => [
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombrecli' => $cliente->nombrecli,
                    'telefonocli' => $cliente->telefonocli,
                ],
                'usuarios' => $usuarios,
                'resumen' => $resumen,
            ],
        ]);
    }

    public function recargasCliente(Request $request, string $idcli)
    {
        $validator = Validator::make([
            'idcli' => $idcli,
            'limit' => $request->query('limit', 10),
        ], [
            'idcli' => 'required|string|exists:clientes,idcli',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $limit = (int) $request->query('limit', 10);

        $recargas = Recarga::query()
            ->with(['estado:idestado,nombre', 'banco:idban,nombreban', 'transaccion.banco'])
            ->where('idcli', $idcli)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Recargas del cliente obtenidas correctamente.',
            'data' => [
                'idcli' => $idcli,
                'total' => $recargas->count(),
                'recargas' => $recargas->map(function (Recarga $recarga) {
                    return [
                        'idrec' => $recarga->idrec,
                        'numcomprobante' => $recarga->numcomprobante,
                        'valor' => (float) $recarga->valor,
                        'idestado' => $recarga->idestado,
                        'estado' => $recarga->estado->nombre ?? null,
                        'idban' => $recarga->idban,
                        'banco' => $recarga->transaccion->banco->nombreban ?? $recarga->banco->nombreban ?? null,
                        'origen' => $recarga->origen,
                        'external_reference' => $recarga->external_reference,
                        'transaccion_id' => $recarga->transaccion_id,
                        'created_at' => optional($recarga->created_at)?->toDateTimeString(),
                    ];
                })->values(),
            ],
        ]);
    }

    public function soportesPorCliente(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcli' => 'nullable|string|exists:clientes,idcli',
            'telefono' => 'nullable|string|max:50',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $idcliInput = $request->input('idcli');
        $telefono = ClienteAuth::normalizePhone($request->input('telefono'));

        if (!$idcliInput && !$telefono) {
            return response()->json([
                'success' => false,
                'message' => 'Debes enviar idcli o telefono para identificar al cliente.',
            ], 422);
        }

        $cliente = $idcliInput
            ? Cliente::findOrFail($idcliInput)
            : Cliente::buscarPorTelefonoNormalizado($telefono);

        if (!$cliente) {
            return response()->json([
                'success' => true,
                'found' => false,
                'message' => 'Cliente no encontrado para consultar soportes.',
                'data' => [
                    'soportes' => [],
                    'resumen' => [
                        'total' => 0,
                        'pendientes' => 0,
                        'atendidos' => 0,
                    ],
                ],
            ]);
        }

        $limit = (int) $request->query('limit', 10);

        $soportes = Soporte::query()
            ->with(['cuenta.valor.servicio'])
            ->where('idcli', $cliente->idcli)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $resumen = [
            'total' => $soportes->count(),
            'pendientes' => $soportes->where('estado', 'pendiente')->count(),
            'atendidos' => $soportes->where('estado', 'atendido')->count(),
        ];

        return response()->json([
            'success' => true,
            'found' => true,
            'message' => 'Soportes del cliente obtenidos correctamente.',
            'data' => [
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombrecli' => $cliente->nombrecli,
                    'telefonocli' => $cliente->telefonocli,
                ],
                'soportes' => $soportes->map(function (Soporte $soporte) {
                    return [
                        'idsop' => $soporte->idsop,
                        'idcue' => $soporte->idcue,
                        'servicio' => $soporte->cuenta?->valor?->servicio?->nombreser,
                        'tipo' => $soporte->tipo,
                        'descripcion' => $soporte->descripcion,
                        'solucion' => $soporte->solucion,
                        'estado' => $soporte->estado,
                        'cuenta_caidacue' => (bool) ($soporte->cuenta?->caidacue ?? false),
                        'created_at' => optional($soporte->created_at)?->toDateTimeString(),
                        'updated_at' => optional($soporte->updated_at)?->toDateTimeString(),
                    ];
                })->values(),
                'resumen' => $resumen,
            ],
        ]);
    }

    public function contextoPostventaPorTelefono(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:50',
            'soportes_limit' => 'nullable|integer|min:1|max:20',
            'recargas_limit' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $telefono = ClienteAuth::normalizePhone($request->input('telefono'));
        $cliente = Cliente::buscarPorTelefonoNormalizado($telefono);

        if (!$cliente) {
            return response()->json([
                'success' => true,
                'found' => false,
                'message' => 'Cliente no encontrado por telefono.',
                'data' => [
                    'telefono_consultado' => $telefono,
                    'usuarios' => [],
                    'soportes_recientes' => [],
                    'recargas_recientes' => [],
                ],
            ]);
        }

        $hoy = Carbon::today();
        $usuarios = DetalleVenta::query()
            ->with(['perfil.cuenta.valor.servicio'])
            ->where('activodet', true)
            ->whereHas('venta', function ($query) use ($cliente) {
                $query->where('idcli', $cliente->idcli);
            })
            ->orderByDesc('fechavendet')
            ->get()
            ->map(function (DetalleVenta $detalle) use ($hoy) {
                $fechaVencimiento = $detalle->fechavendet ? Carbon::parse($detalle->fechavendet)->startOfDay() : null;
                $diasRestantes = $fechaVencimiento ? $hoy->diffInDays($fechaVencimiento, false) : null;

                $estado = 'activo';
                if ($diasRestantes !== null && $diasRestantes < 0) {
                    $estado = 'vencido';
                } elseif ($diasRestantes !== null && $diasRestantes <= 3) {
                    $estado = 'por_vencer';
                }

                return [
                    'iddet' => $detalle->iddet,
                    'idcue' => $detalle->perfil?->cuenta?->idcue,
                    'servicio' => $detalle->perfil?->cuenta?->valor?->servicio?->nombreser,
                    'perfil' => $detalle->perfil?->numeroper,
                    'fecha_vencimiento' => $fechaVencimiento?->toDateString(),
                    'dias_restantes' => $diasRestantes,
                    'estado' => $estado,
                    'cuenta_caidacue' => (bool) ($detalle->perfil?->cuenta?->caidacue ?? false),
                    'cuenta_activa' => (bool) ($detalle->perfil?->cuenta?->activocue ?? false),
                ];
            })
            ->values();

        $soportesLimit = (int) $request->query('soportes_limit', 5);
        $soportesRecientes = Soporte::query()
            ->with(['cuenta.valor.servicio'])
            ->where('idcli', $cliente->idcli)
            ->orderByDesc('created_at')
            ->limit($soportesLimit)
            ->get()
            ->map(function (Soporte $soporte) {
                return [
                    'idsop' => $soporte->idsop,
                    'idcue' => $soporte->idcue,
                    'servicio' => $soporte->cuenta?->valor?->servicio?->nombreser,
                    'tipo' => $soporte->tipo,
                    'estado' => $soporte->estado,
                    'created_at' => optional($soporte->created_at)?->toDateTimeString(),
                ];
            })
            ->values();

        $recargasLimit = (int) $request->query('recargas_limit', 5);
        $recargasRecientes = Recarga::query()
            ->with(['estado:idestado,nombre', 'banco:idban,nombreban'])
            ->where('idcli', $cliente->idcli)
            ->orderByDesc('created_at')
            ->limit($recargasLimit)
            ->get()
            ->map(function (Recarga $recarga) {
                return [
                    'idrec' => $recarga->idrec,
                    'numcomprobante' => $recarga->numcomprobante,
                    'valor' => (float) $recarga->valor,
                    'estado' => $recarga->estado->nombre ?? null,
                    'banco' => $recarga->banco->nombreban ?? null,
                    'created_at' => optional($recarga->created_at)?->toDateTimeString(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'found' => true,
            'message' => 'Contexto postventa obtenido correctamente.',
            'data' => [
                'cliente' => [
                    'idcli' => $cliente->idcli,
                    'nombrecli' => $cliente->nombrecli,
                    'telefonocli' => $cliente->telefonocli,
                ],
                'usuarios' => $usuarios,
                'soportes_recientes' => $soportesRecientes,
                'recargas_recientes' => $recargasRecientes,
                'resumen' => [
                    'usuarios_total' => $usuarios->count(),
                    'usuarios_activos' => $usuarios->where('estado', 'activo')->count(),
                    'usuarios_por_vencer' => $usuarios->where('estado', 'por_vencer')->count(),
                    'usuarios_vencidos' => $usuarios->where('estado', 'vencido')->count(),
                    'cuentas_caidas' => $usuarios->where('cuenta_caidacue', true)->count(),
                    'soportes_pendientes' => $soportesRecientes->where('estado', 'pendiente')->count(),
                ],
            ],
        ]);
    }

    public function cambioServicioPostventa(Request $request)
    {
        // Normalize acepta_garantia: n8n/agentes pueden enviar texto libre
        if ($request->has('acepta_garantia')) {
            $raw = $request->input('acepta_garantia');

            if (is_string($raw)) {
                $normalizado = mb_strtolower(trim($raw), 'UTF-8');
                $normalizado = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $normalizado);

                $afirmativos = [
                    'true', '1', 'yes', 'y', 'on', 'si', 's', 'acepto', 'acepto garantia',
                    'acepta', 'confirmo', 'confirmado', 'de acuerdo', 'ok', 'okay',
                    'procede', 'aprobado',
                ];

                $negativos = [
                    'false', '0', 'no', 'n', 'off', 'rechazo', 'no acepto',
                    'cancelar', 'denegado', 'negado',
                ];

                $valor = null;

                if (in_array($normalizado, $afirmativos, true)) {
                    $valor = true;
                } elseif (in_array($normalizado, $negativos, true)) {
                    $valor = false;
                } else {
                    // Soporta frases como: "si, acepta garantia" o "no acepta"
                    if (str_contains($normalizado, 'acepta garantia') || str_contains($normalizado, 'acepto garantia')) {
                        $valor = true;
                    } elseif (str_contains($normalizado, 'no acepta') || str_contains($normalizado, 'rechaza')) {
                        $valor = false;
                    } else {
                        $valor = filter_var($normalizado, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    }
                }

                $request->merge(['acepta_garantia' => $valor]);
            }
        }

        $validator = Validator::make($request->all(), [
            'telefono' => 'required|string|max:50',
            'iddet' => 'required|integer|exists:detalles_venta,iddet',
            'nuevo_servicio' => 'required|string|max:50',
            'acepta_garantia' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->has('acepta_garantia') && !$request->boolean('acepta_garantia')) {
            return response()->json([
                'success' => false,
                'message' => 'El cambio de servicio solo procede cuando el cliente acepta la garantia.',
            ], 422);
        }

        $telefono = ClienteAuth::normalizePhone($request->input('telefono'));
        $cliente = Cliente::buscarPorTelefonoNormalizado($telefono);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar al cliente con el telefono enviado.',
            ], 404);
        }

        $iddet = (int) $request->input('iddet');

        $usuario = ViewUsuarioActivo::query()
            ->with(['profile', 'cuenta.valor.servicio'])
            ->where('iddet', $iddet)
            ->where('idcli', $cliente->idcli)
            ->first();

        if (!$usuario || !$usuario->cuenta) {
            return response()->json([
                'success' => false,
                'message' => 'El iddet no pertenece a un usuario activo del cliente.',
            ], 422);
        }

        $servicioOrigenId = strtoupper((string) ($usuario->cuenta->valor->idser ?? ''));

        if ($servicioOrigenId === 'SPOTIFY') {
            $soporte = Soporte::query()
                ->where('idcli', $cliente->idcli)
                ->where('idcue', $usuario->idcue)
                ->where('tipo', 'otro')
                ->where('estado', 'pendiente')
                ->where('created_at', '>=', now()->subHours(12))
                ->latest('created_at')
                ->first();

            if (!$soporte) {
                $soporte = Soporte::create([
                    'idcli' => $cliente->idcli,
                    'idcue' => $usuario->idcue,
                    'tipo' => 'otro',
                    'descripcion' => 'Caso Spotify en cuenta danada. Cliente solicita garantia/cambio, requiere gestion 100% humana.',
                    'estado' => 'pendiente',
                ]);

                $soporte->load(['cliente', 'cuenta.valor.servicio']);

                $empleados = Empleado::permission('soportes')->get();
                if ($empleados->isNotEmpty()) {
                    Notification::send($empleados, new NuevoSoporteCliente($soporte));
                }

                event('notificacionRecibida');
            }

            return response()->json([
                'success' => true,
                'changed' => false,
                'manual_required' => true,
                'message' => 'Spotify se atiende de forma 100% humana. Se registro soporte y el cliente debe esperar respuesta del equipo.',
                'data' => [
                    'cliente' => [
                        'idcli' => $cliente->idcli,
                        'nombrecli' => $cliente->nombrecli,
                        'telefonocli' => $cliente->telefonocli,
                    ],
                    'origen' => [
                        'servicio_id' => $servicioOrigenId,
                        'servicio' => $usuario->cuenta?->valor?->servicio?->nombreser,
                        'idcue' => $usuario->idcue,
                        'iddet' => $usuario->iddet,
                        'cuenta_caidacue' => (bool) ($usuario->cuenta?->caidacue ?? false),
                        'fecha_vencimiento_actual' => $usuario->fecha_vencimiento,
                    ],
                    'soporte' => [
                        'idsop' => $soporte->idsop,
                        'estado' => $soporte->estado,
                        'created_at' => optional($soporte->created_at)?->toDateTimeString(),
                    ],
                ],
            ]);
        }

        $serviciosOrigenPermitidos = ['NETFLIX', 'DISNEYP', 'DISNEY', 'DISNEYS'];
        if (!in_array($servicioOrigenId, $serviciosOrigenPermitidos, true)) {
            return response()->json([
                'success' => false,
                'message' => 'La garantia por cambio de servicio hoy solo aplica a cuentas danadas de Netflix y Disney.',
                'data' => [
                    'servicio_origen' => $servicioOrigenId,
                ],
            ], 422);
        }

        if (!(bool) $usuario->cuenta->caidacue) {
            return response()->json([
                'success' => false,
                'message' => 'El cambio por garantia solo aplica cuando la cuenta origen esta marcada como danada.',
            ], 422);
        }

        $servicioDestinoId = $this->normalizarServicioIdGarantia((string) $request->input('nuevo_servicio'));
        $serviciosDestinoPermitidos = ['PRIME', 'MAX', 'CRUNCHY', 'PARAMOUNT', 'MAGIS', 'FLUJO'];

        if (!$servicioDestinoId || !in_array($servicioDestinoId, $serviciosDestinoPermitidos, true)) {
            return response()->json([
                'success' => false,
                'message' => 'El servicio destino no esta permitido para garantia automatica.',
                'data' => [
                    'servicios_permitidos' => ['prime_video', 'max', 'crunchyroll', 'paramount', 'flujo_tv'],
                ],
            ], 422);
        }

        if ($servicioDestinoId === $servicioOrigenId) {
            return response()->json([
                'success' => false,
                'message' => 'El servicio destino debe ser diferente al servicio actual del cliente.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $detalle = DetalleVenta::query()
                ->with(['venta', 'perfil.cuenta.valor.servicio'])
                ->lockForUpdate()
                ->find($iddet);

            if (!$detalle || !$detalle->activodet || !$detalle->venta || $detalle->venta->idcli !== $cliente->idcli) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'El detalle no corresponde a una suscripcion activa del cliente.',
                ], 422);
            }

            $perfilOrigen = $detalle->perfil;
            $cuentaOrigen = $perfilOrigen?->cuenta;
            $servicioOrigenActual = strtoupper((string) ($cuentaOrigen?->valor?->idser ?? ''));

            if (!$cuentaOrigen || !in_array($servicioOrigenActual, $serviciosOrigenPermitidos, true) || !(bool) $cuentaOrigen->caidacue) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'El detalle ya no cumple condiciones de garantia para cambio de servicio.',
                ], 422);
            }

            $cuentaDestino = $this->buscarCuentaDisponible($servicioDestinoId);
            if (!$cuentaDestino) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No hay cuentas disponibles en el servicio destino solicitado.',
                ], 422);
            }

            $perfilDestino = $this->buscarPerfilDisponible($cuentaDestino, true);
            if (!$perfilDestino) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No hay perfiles disponibles en la cuenta destino para completar el cambio.',
                ], 422);
            }

            $fechaAnterior = $detalle->fechavendet
                ? Carbon::parse($detalle->fechavendet)->startOfDay()
                : Carbon::today();
            $fechaNueva = $fechaAnterior->copy()->addDays(7);

            $detalle->idper = $perfilDestino->idper;
            $detalle->fechavendet = $fechaNueva;
            $detalle->save();

            DB::commit();

            $detalle->refresh();
            $perfilDestino->loadMissing(['cuenta.valor.servicio']);

            $credencialesEntrega = $this->resolverCredencialesEntrega(
                servicio: $perfilDestino->cuenta?->valor?->servicio?->nombreser,
                perfilNumero: is_numeric($perfilDestino->numeroper) ? (int) $perfilDestino->numeroper : null,
                usuarioCuenta: $perfilDestino->cuenta?->usuariocue,
                contrasenaCuenta: $perfilDestino->cuenta?->contrasenacue,
                pinPerfil: $perfilDestino->pinper,
            );

            try {
                $empleados = Empleado::all();

                if ($empleados->isNotEmpty()) {
                    Notification::send($empleados, new class([
                        'idcli' => $cliente->idcli,
                        'nombrecli' => $cliente->nombrecli,
                        'telefonocli' => $cliente->telefonocli,
                        'iddet' => $detalle->iddet,
                        'origen' => $cuentaOrigen?->valor?->servicio?->nombreser ?? $servicioOrigenActual,
                        'destino' => $perfilDestino->cuenta?->valor?->servicio?->nombreser ?? $servicioDestinoId,
                        'idcue_origen' => $cuentaOrigen?->idcue,
                        'idcue_destino' => $perfilDestino->cuenta?->idcue,
                        'fecha_vencimiento_nueva' => $fechaNueva->toDateString(),
                    ]) extends \Illuminate\Notifications\Notification {
                        use \Illuminate\Bus\Queueable;

                        public function __construct(public array $cambio)
                        {
                        }

                        public function via(object $notifiable): array
                        {
                            return ['database'];
                        }

                        public function toDatabase(object $notifiable): array
                        {
                            $cliente = $this->cambio['nombrecli'] ?? 'Cliente';
                            $origen = $this->cambio['origen'] ?? 'Servicio origen';
                            $destino = $this->cambio['destino'] ?? 'Servicio destino';

                            return [
                                'titulo' => 'Cambio de servicio por garantia',
                                'mensaje' => "{$cliente} fue cambiado de {$origen} a {$destino} por garantia.",
                                'url' => route('ventas'),
                                'contexto' => [
                                    'idcli' => $this->cambio['idcli'] ?? null,
                                    'telefonocli' => $this->cambio['telefonocli'] ?? null,
                                    'iddet' => $this->cambio['iddet'] ?? null,
                                    'idcue_origen' => $this->cambio['idcue_origen'] ?? null,
                                    'idcue_destino' => $this->cambio['idcue_destino'] ?? null,
                                    'fecha_vencimiento_nueva' => $this->cambio['fecha_vencimiento_nueva'] ?? null,
                                ],
                            ];
                        }
                    });

                    event('notificacionRecibida');
                }
            } catch (\Throwable $notifyError) {
                Log::warning('No se pudo notificar cambio de servicio por garantia', [
                    'idcli' => $cliente->idcli,
                    'iddet' => $detalle->iddet,
                    'error' => $notifyError->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'changed' => true,
                'message' => 'Cambio de servicio aplicado correctamente con compensacion de 7 dias.',
                'data' => [
                    'cliente' => [
                        'idcli' => $cliente->idcli,
                        'nombrecli' => $cliente->nombrecli,
                        'telefonocli' => $cliente->telefonocli,
                    ],
                    'origen' => [
                        'servicio_id' => $servicioOrigenActual,
                        'servicio' => $cuentaOrigen?->valor?->servicio?->nombreser,
                        'idcue' => $cuentaOrigen?->idcue,
                        'iddet' => $detalle->iddet,
                        'fecha_vencimiento_anterior' => $fechaAnterior->toDateString(),
                    ],
                    'destino' => [
                        'servicio_id' => strtoupper((string) ($perfilDestino->cuenta?->valor?->idser ?? $servicioDestinoId)),
                        'servicio' => $perfilDestino->cuenta?->valor?->servicio?->nombreser,
                        'idcue' => $perfilDestino->cuenta?->idcue,
                        'perfil' => $perfilDestino->numeroper,
                        'usuario' => $credencialesEntrega['cuenta'],
                        'contrasenacue' => $credencialesEntrega['contrasenacue'],
                        'pinper' => $credencialesEntrega['pinper'],
                        'credencial_regla' => $credencialesEntrega['regla'],
                        'fecha_vencimiento_nueva' => $fechaNueva->toDateString(),
                    ],
                    'garantia' => [
                        'compensacion_dias' => 7,
                        'mensaje_cliente' => 'Por garantia te cambiamos de servicio y te extendimos 7 dias adicionales sobre tu fecha de vencimiento anterior.',
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo aplicar el cambio de servicio por garantia.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cobranzasMetodosPago()
    {
        $bancosDisponibles = Banco::query()
            ->orderBy('nombreban')
            ->get(['idban', 'nombreban', 'tipoban']);

        return response()->json([
            'success' => true,
            'message' => 'Metodos de pago de cobranzas obtenidos correctamente.',
            'data' => [
                'metodos' => [
                    [
                        'codigo' => 'transferencia_bancaria',
                        'nombre' => 'Transferencia bancaria',
                        'requiere_comprobante' => true,
                        'tiempo_validacion' => 'manual',
                        'consulta_bancos_endpoint' => '/api/v2/chat/assistant/cobranzas/bancos',
                    ],
                ],
                'bancos_disponibles' => $bancosDisponibles,
            ],
        ]);
    }

    public function cobranzasBancos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filtro' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $filtro = trim((string) $request->input('filtro', ''));

        $query = Banco::query()->orderBy('nombreban');
        if ($filtro !== '') {
            $query->where(function ($q) use ($filtro) {
                $q->where('nombreban', 'like', '%' . $filtro . '%')
                    ->orWhere('propietarioban', 'like', '%' . $filtro . '%')
                    ->orWhere('numeroban', 'like', '%' . $filtro . '%');
            });
        }

        $bancos = $query->get([
            'idban',
            'nombreban',
            'propietarioban',
            'cedulaban',
            'numeroban',
            'tipoban',
            'detalleban',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bancos de cobranzas obtenidos correctamente.',
            'data' => [
                'total' => $bancos->count(),
                'bancos' => $bancos,
            ],
        ]);
    }

    public function crearVenta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcli' => 'required|string|exists:clientes,idcli',
            'idproducto' => 'required|integer|exists:productos,id',
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
            $venta = new Venta();
            $venta->idemp = 10;
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
                $detalleVenta->descripciondet = 'Venta automatizada por subagente vendedor_cierre';
                $detalleVenta->montodet = $montoDetalle;
                $detalleVenta->activodet = true;
                $detalleVenta->save();

                $montoTotalDetalles += (float) $detalleVenta->montodet;

                $servicioEntrega = $perfil->cuenta->valor->servicio->nombreser ?? null;
                $perfilNumeroEntrega = is_numeric($perfil->numeroper) ? (int) $perfil->numeroper : null;
                $credencialesEntrega = $this->resolverCredencialesEntrega(
                    servicio: $servicioEntrega,
                    perfilNumero: $perfilNumeroEntrega,
                    usuarioCuenta: $perfil->cuenta->usuariocue ?? null,
                    contrasenaCuenta: $perfil->cuenta->contrasenacue ?? null,
                    pinPerfil: $perfil->pinper,
                );

                $entregas[] = [
                    'servicio' => $servicioEntrega,
                    'usuario' => $credencialesEntrega['cuenta'],
                    'contrasena' => $credencialesEntrega['contrasenacue'],
                    'perfil' => $perfil->numeroper,
                    'pin' => $credencialesEntrega['pinper'],
                    'credencial_regla' => $credencialesEntrega['regla'],
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

            $venta->loadMissing('cliente');

            try {
                $empleados = Empleado::all();

                if ($empleados->isNotEmpty()) {
                    Notification::send($empleados, new ComprasRealizadas($venta));
                    event('notificacionRecibida');
                }
            } catch (\Throwable $notifyError) {
                Log::warning('No se pudo notificar venta creada por chat assistant', [
                    'idven' => $venta->idven,
                    'idcli' => $cliente->idcli,
                    'error' => $notifyError->getMessage(),
                ]);
            }

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

    public function renovarVenta(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idven' => 'required|string|exists:ventas,idven',
            'idcli' => 'required|exists:clientes,idcli',
            'meses' => 'required|integer|min:1|max:12',
            'detalles' => 'nullable|array|min:1',
            'detalles.*' => 'integer|exists:detalles_venta,iddet',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $idcli = (string) $request->input('idcli');
        $meses = (int) $request->input('meses');
        $detallesSolicitados = collect($request->input('detalles', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $ventaOriginal = Venta::findOrFail($request->input('idven'));

        if ((string) $ventaOriginal->idcli !== $idcli) {
            return response()->json([
                'success' => false,
                'message' => 'La venta indicada no pertenece al cliente enviado.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $cliente = Cliente::where('idcli', $idcli)->lockForUpdate()->firstOrFail();

            $detallesVenta = DetalleVenta::with(['perfil.cuenta.valor.servicio'])
                ->where('idven', $ventaOriginal->idven)
                ->where('activodet', true)
                ->when($detallesSolicitados->isNotEmpty(), function ($query) use ($detallesSolicitados) {
                    $query->whereIn('iddet', $detallesSolicitados->all());
                })
                ->lockForUpdate()
                ->get();

            if ($detallesSolicitados->isNotEmpty() && $detallesVenta->count() !== $detallesSolicitados->count()) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Uno o mas detalles no pertenecen a la venta o no estan activos.',
                    'data' => [
                        'idven' => $ventaOriginal->idven,
                        'detalles_solicitados' => $detallesSolicitados->all(),
                        'detalles_validos' => $detallesVenta->pluck('iddet')->map(fn ($id) => (int) $id)->values(),
                    ],
                ], 422);
            }

            if ($detallesVenta->isEmpty()) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'La venta no tiene detalles activos para renovar.',
                ], 422);
            }

            $precioRenovacion = $this->calcularPrecioRenovacionAgente($detallesVenta, $meses);
            $totalRenovacion = (float) ($precioRenovacion['total'] ?? 0);

            if ($totalRenovacion <= 0) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo calcular el valor de la renovacion.',
                ], 422);
            }

            if ((float) $cliente->saldo < $totalRenovacion) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente para realizar la renovacion.',
                    'data' => [
                        'saldo_actual' => (float) $cliente->saldo,
                        'total_renovacion' => round($totalRenovacion, 2),
                        'faltante' => round($totalRenovacion - (float) $cliente->saldo, 2),
                        'precio_mensual_base' => round((float) ($precioRenovacion['base_mensual'] ?? 0), 2),
                        'estrategia_precio' => $precioRenovacion['pricing_strategy'] ?? 'detalles_actuales',
                        'producto_base' => $precioRenovacion['producto_base'] ?? null,
                    ],
                ], 422);
            }

            $ventaNueva = new Venta();
            $ventaNueva->idemp = 10;
            $ventaNueva->idcli = $cliente->idcli;
            $ventaNueva->fechaven = now();
            $ventaNueva->totalpagoven = round($totalRenovacion, 2);
            $ventaNueva->save();

            $ventaNueva->idven = DB::selectOne(
                'SELECT idven FROM ventas WHERE idcli = ? ORDER BY created_at DESC LIMIT 1',
                [$ventaNueva->idcli]
            )?->idven;

            if (!$ventaNueva->idven) {
                throw new \RuntimeException('No se pudo obtener el id de la venta renovada.');
            }

            $idsDetalleRenovado = $detallesVenta->pluck('iddet')->map(fn ($id) => (int) $id)->values();

            DetalleVenta::where('idven', $ventaOriginal->idven)
                ->whereIn('iddet', $idsDetalleRenovado->all())
                ->where('activodet', true)
                ->update(['activodet' => false]);

            $montoPorDetalle = collect($precioRenovacion['detalles'] ?? [])->keyBy('iddet');
            $detallesRenovados = [];

            foreach ($detallesVenta as $detalle) {
                $baseFecha = Carbon::parse($detalle->fechavendet);
                if ($baseFecha->lt(Carbon::today())) {
                    $baseFecha = Carbon::today();
                }

                $nuevaFechaVencimiento = $baseFecha->copy()->addMonths($meses);
                $montoDetalle = (float) ($montoPorDetalle->get((int) $detalle->iddet)['monto'] ?? ((float) $detalle->montodet * $meses));

                $detalleNuevo = new DetalleVenta();
                $detalleNuevo->idven = $ventaNueva->idven;
                $detalleNuevo->idper = $detalle->idper;
                $detalleNuevo->fechavendet = $nuevaFechaVencimiento;
                $detalleNuevo->descripciondet = 'Renovacion automatizada (' . $meses . 'm)';
                $detalleNuevo->montodet = round($montoDetalle, 2);
                $detalleNuevo->activodet = true;
                $detalleNuevo->save();

                $servicioEntrega = $detalle->perfil?->cuenta?->valor?->servicio?->nombreser;
                $perfilNumeroEntrega = is_numeric($detalle->perfil?->numeroper) ? (int) $detalle->perfil?->numeroper : null;
                $credencialesEntrega = $this->resolverCredencialesEntrega(
                    servicio: $servicioEntrega,
                    perfilNumero: $perfilNumeroEntrega,
                    usuarioCuenta: $detalle->perfil?->cuenta?->usuariocue,
                    contrasenaCuenta: $detalle->perfil?->cuenta?->contrasenacue,
                    pinPerfil: $detalle->perfil?->pinper,
                );

                $detallesRenovados[] = [
                    'iddet_anterior' => (int) $detalle->iddet,
                    'iddet_nuevo' => (int) $detalleNuevo->iddet,
                    'servicio' => $servicioEntrega,
                    'usuario' => $credencialesEntrega['cuenta'],
                    'contrasena' => $credencialesEntrega['contrasenacue'],
                    'perfil' => $detalle->perfil?->numeroper,
                    'pinper' => $credencialesEntrega['pinper'],
                    'credencial_regla' => $credencialesEntrega['regla'],
                    'fecha_anterior' => optional($detalle->fechavendet)?->toDateString(),
                    'fecha_nueva' => optional($nuevaFechaVencimiento)?->toDateString(),
                    'monto' => round($montoDetalle, 2),
                ];
            }

            $cliente->saldo = round((float) $cliente->saldo - $totalRenovacion, 2);
            if (!$cliente->ya_compro) {
                $cliente->ya_compro = true;
            }
            $cliente->save();

            DB::commit();

            try {
                $ventaNueva->loadMissing('cliente');
                $empleados = Empleado::all();
                if ($empleados->isNotEmpty()) {
                    Notification::send($empleados, new ComprasRealizadas($ventaNueva));
                    event('notificacionRecibida');
                }
            } catch (\Throwable $notifyError) {
                Log::warning('No se pudo notificar renovacion por chat assistant', [
                    'idven' => $ventaNueva->idven ?? null,
                    'error' => $notifyError->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Venta renovada correctamente.',
                'data' => [
                    'venta_original' => [
                        'idven' => $ventaOriginal->idven,
                        'detalles_desactivados' => $idsDetalleRenovado,
                    ],
                    'venta_renovada' => [
                        'idven' => $ventaNueva->idven,
                        'idcli' => $ventaNueva->idcli,
                        'idemp' => $ventaNueva->idemp,
                        'meses' => $meses,
                        'total' => round($totalRenovacion, 2),
                        'saldo_restante' => (float) $cliente->saldo,
                        'precio_mensual_base' => round((float) ($precioRenovacion['base_mensual'] ?? 0), 2),
                        'estrategia_precio' => $precioRenovacion['pricing_strategy'] ?? 'detalles_actuales',
                        'producto_base' => $precioRenovacion['producto_base'] ?? null,
                    ],
                    'detalles_renovados' => $detallesRenovados,
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo renovar la venta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function crearPedido(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcli' => 'required|string|exists:clientes,idcli',
            'idproducto' => 'required|integer|exists:productos,id',
            'datos_extra' => 'nullable|array',
            'notas' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cliente = Cliente::findOrFail($request->input('idcli'));
        $producto = Producto::findOrFail((int) $request->input('idproducto'));

        if (!$producto->activo) {
            return response()->json([
                'success' => false,
                'message' => 'El producto no esta activo para pedidos.',
            ], 422);
        }

        $tipoProducto = (int) $producto->tipo_producto_id;

        if (!in_array($tipoProducto, [2, 3], true)) {
            return response()->json([
                'success' => false,
                'message' => 'El producto seleccionado no corresponde a tipo pedido/personalizado.',
            ], 422);
        }

        $datosExtra = $request->input('datos_extra', []);

        if ($tipoProducto === 3 && empty($datosExtra)) {
            return response()->json([
                'success' => false,
                'message' => 'Para productos personalizados se requieren datos_extra.',
            ], 422);
        }

        $payloadRespuesta = [
            'origen' => 'subagente_vendedor_cierre',
            'idemp' => 10,
            'tipo_producto_id' => $tipoProducto,
            'notas' => $request->input('notas'),
            'datos_extra' => $datosExtra,
        ];

        $pedido = Pedido::create([
            'idcli' => $cliente->idcli,
            'producto_id' => $producto->id,
            'fechapedido' => now(),
            'respuesta' => json_encode($payloadRespuesta, JSON_UNESCAPED_UNICODE),
        ]);

        $pedido->setRelation('cliente', $cliente);
        $pedido->setRelation('producto', $producto);

        try {
            $empleados = Empleado::all();
            if ($empleados->isNotEmpty()) {
                Notification::send($empleados, new PedidosPendientes($pedido));
                event('notificacionRecibida');
            }
        } catch (\Throwable $notifyError) {
            Log::warning('No se pudo notificar pedido por chat assistant', [
                'pedido_id' => $pedido->id ?? null,
                'error' => $notifyError->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido creado correctamente.',
            'data' => [
                'pedido' => [
                    'id' => $pedido->id,
                    'idcli' => $pedido->idcli,
                    'producto_id' => $pedido->producto_id,
                    'producto' => $producto->nombrepro,
                    'tipo_producto_id' => $tipoProducto,
                    'fechapedido' => optional($pedido->fechapedido)?->toDateTimeString(),
                ],
            ],
        ], 201);
    }

    public function crearSoporte(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idcli' => 'nullable|string|exists:clientes,idcli',
            'telefono' => 'nullable|string|max:50',
            'idcue' => 'nullable|string|max:100',
            'iddet' => 'nullable|integer',
            'tipo' => 'required|string|in:' . implode(',', Soporte::TIPOS),
            'descripcion' => 'required|string|min:10|max:1500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $idcliInput = $request->input('idcli');
        $telefono = ClienteAuth::normalizePhone($request->input('telefono'));

        if (!$idcliInput && !$telefono) {
            return response()->json([
                'success' => false,
                'message' => 'Debes enviar idcli o telefono para identificar al cliente.',
            ], 422);
        }

        $idcueInput = $request->input('idcue');
        $iddetInput = $request->input('iddet');

        // Compatibilidad n8n: algunos flujos envian iddet en el campo idcue por error.
        if (!$iddetInput && $idcueInput && !Cuenta::query()->where('idcue', $idcueInput)->exists() && is_numeric($idcueInput)) {
            $iddetInput = (int) $idcueInput;
            $idcueInput = null;
        }

        if (!$idcueInput && !$iddetInput) {
            return response()->json([
                'success' => false,
                'message' => 'Debes enviar idcue o iddet para identificar la cuenta afectada.',
            ], 422);
        }

        $cliente = $idcliInput
            ? Cliente::findOrFail($idcliInput)
            : Cliente::buscarPorTelefonoNormalizado($telefono);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar al cliente con el telefono enviado.',
            ], 404);
        }

        $usuarioActivo = null;
        if ($iddetInput) {
            $usuarioActivo = ViewUsuarioActivo::query()
                ->with(['profile', 'cuenta.valor.servicio'])
                ->where('iddet', $iddetInput)
                ->where('idcli', $cliente->idcli)
                ->first();

            if (!$usuarioActivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El iddet no pertenece a un usuario activo del cliente.',
                ], 422);
            }

            $idcueInput = $idcueInput ?: $usuarioActivo->idcue;
        }

        $cuenta = Cuenta::with('valor.servicio')->findOrFail($idcueInput);

        $clienteTieneCuenta = ViewUsuarioActivo::query()
            ->where('idcli', $cliente->idcli)
            ->where('idcue', $cuenta->idcue)
            ->exists();

        if (!$clienteTieneCuenta) {
            return response()->json([
                'success' => false,
                'message' => 'La cuenta indicada no pertenece a una suscripcion activa del cliente.',
            ], 422);
        }

        if (!$usuarioActivo) {
            $usuarioActivo = ViewUsuarioActivo::query()
                ->with(['profile', 'cuenta.valor.servicio'])
                ->where('idcli', $cliente->idcli)
                ->where('idcue', $cuenta->idcue)
                ->orderByDesc('fecha_vencimiento')
                ->first();
        }

        $soporteExistentePendiente = Soporte::query()
            ->where('idcli', $cliente->idcli)
            ->where('idcue', $cuenta->idcue)
            ->where('tipo', $request->input('tipo'))
            ->where('estado', 'pendiente')
            ->where('created_at', '>=', now()->subHours(12))
            ->latest('created_at')
            ->first();

        if ($soporteExistentePendiente) {
            $servicioCuenta = $cuenta->valor?->servicio?->nombreser;
            $perfilNumero = is_numeric($usuarioActivo?->perfil) ? (int) $usuarioActivo?->perfil : null;
            $credencialesEntrega = $this->resolverCredencialesEntrega(
                servicio: $servicioCuenta,
                perfilNumero: $perfilNumero,
                usuarioCuenta: $cuenta->usuariocue,
                contrasenaCuenta: $cuenta->contrasenacue,
                pinPerfil: $usuarioActivo?->profile?->pinper,
            );

            return response()->json([
                'success' => true,
                'created' => false,
                'message' => 'Ya existe un soporte pendiente reciente para este caso.',
                'data' => [
                    'soporte' => [
                        'idsop' => $soporteExistentePendiente->idsop,
                        'idcli' => $soporteExistentePendiente->idcli,
                        'idcue' => $soporteExistentePendiente->idcue,
                        'tipo' => $soporteExistentePendiente->tipo,
                        'descripcion' => $soporteExistentePendiente->descripcion,
                        'estado' => $soporteExistentePendiente->estado,
                        'created_at' => optional($soporteExistentePendiente->created_at)?->toDateTimeString(),
                    ],
                    'contexto_cuenta' => [
                        'servicio' => $servicioCuenta,
                        'usuario' => $credencialesEntrega['cuenta'],
                        'contrasenacue' => $credencialesEntrega['contrasenacue'],
                        'perfil' => $usuarioActivo?->perfil,
                        'pinper' => $credencialesEntrega['pinper'],
                        'credencial_regla' => $credencialesEntrega['regla'],
                        'iddet' => $usuarioActivo?->iddet,
                        'fecha_vencimiento' => $usuarioActivo?->fecha_vencimiento,
                        'cuenta_caidacue' => (bool) $cuenta->caidacue,
                    ],
                    'cuenta_marcada_caidacue' => false,
                ],
            ]);
        }

        $soporte = Soporte::create([
            'idcli' => $cliente->idcli,
            'idcue' => $cuenta->idcue,
            'tipo' => $request->input('tipo'),
            'descripcion' => $request->input('descripcion'),
            'estado' => 'pendiente',
        ]);

        $soporte->load(['cliente', 'cuenta.valor.servicio']);

        $empleados = Empleado::permission('soportes')->get();
        if ($empleados->isNotEmpty()) {
            Notification::send($empleados, new NuevoSoporteCliente($soporte));
        }

        event('notificacionRecibida');

        $servicioCuenta = $cuenta->valor?->servicio?->nombreser;
        $perfilNumero = is_numeric($usuarioActivo?->perfil) ? (int) $usuarioActivo?->perfil : null;
        $credencialesEntrega = $this->resolverCredencialesEntrega(
            servicio: $servicioCuenta,
            perfilNumero: $perfilNumero,
            usuarioCuenta: $cuenta->usuariocue,
            contrasenaCuenta: $cuenta->contrasenacue,
            pinPerfil: $usuarioActivo?->profile?->pinper,
        );

        return response()->json([
            'success' => true,
            'created' => true,
            'message' => 'Soporte registrado correctamente.',
            'data' => [
                'soporte' => [
                    'idsop' => $soporte->idsop,
                    'idcli' => $soporte->idcli,
                    'idcue' => $soporte->idcue,
                    'tipo' => $soporte->tipo,
                    'descripcion' => $soporte->descripcion,
                    'estado' => $soporte->estado,
                    'servicio' => $soporte->cuenta?->valor?->servicio?->nombreser,
                    'created_at' => optional($soporte->created_at)?->toDateTimeString(),
                ],
                'contexto_cuenta' => [
                    'servicio' => $servicioCuenta,
                    'usuario' => $credencialesEntrega['cuenta'],
                    'contrasenacue' => $credencialesEntrega['contrasenacue'],
                    'perfil' => $usuarioActivo?->perfil,
                    'pinper' => $credencialesEntrega['pinper'],
                    'credencial_regla' => $credencialesEntrega['regla'],
                    'iddet' => $usuarioActivo?->iddet,
                    'fecha_vencimiento' => $usuarioActivo?->fecha_vencimiento,
                    'cuenta_caidacue' => (bool) $cuenta->caidacue,
                ],
                'cuenta_marcada_caidacue' => false,
                'nota' => 'Registrar soporte no marca la cuenta como caida automaticamente.',
            ],
        ], 201);
    }

    public function consultarMemoria(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|string|in:soporte,vendedor,cobranzas,postventa',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $tipoSubagente = $request->input('tipo');
        $prefijo = $tipoSubagente . '_';

        $playbooks = \App\Models\ChatMemoriaNegocio::query()
            ->where('activo', true)
            ->where('clave', 'like', $prefijo . '%')
            ->whereIn('tipo', ['faq', 'guion'])
            ->orderBy('prioridad')
            ->orderBy('clave')
            ->get(['tipo', 'clave', 'titulo', 'resumen', 'contenido', 'tags', 'fuente', 'prioridad', 'visibilidad'])
            ->map(function ($playbook) {
                return [
                    // Compatibilidad para nodos n8n existentes
                    'codigo' => $playbook->clave,
                    'nombre' => $playbook->titulo,
                    'descripcion' => $playbook->resumen,
                    'prompt' => $playbook->contenido,
                    'criterios' => $playbook->tags ?? [],
                    'contenido' => $playbook->contenido,
                    // Campos canonicos del nuevo esquema
                    'clave' => $playbook->clave,
                    'titulo' => $playbook->titulo,
                    'tipo' => $playbook->tipo,
                    'tags' => $playbook->tags ?? [],
                    'fuente' => $playbook->fuente,
                    'prioridad' => $playbook->prioridad,
                    'visibilidad' => $playbook->visibilidad,
                ];
            });

        $reglasComunicacion = \App\Models\ChatMemoriaNegocio::query()
            ->where('activo', true)
            ->where('tipo', 'guion')
            ->where('clave', 'like', $prefijo . '%')
            ->orderBy('prioridad')
            ->orderBy('clave')
            ->get(['clave', 'titulo', 'contenido', 'tags', 'prioridad'])
            ->map(function ($regla) {
                return [
                    'codigo' => $regla->clave,
                    'nombre' => $regla->titulo,
                    'contenido' => $regla->contenido,
                    'tags' => $regla->tags ?? [],
                    'prioridad' => $regla->prioridad,
                ];
            });

        return response()->json([
            'success' => true,
            'tipo_subagente' => $tipoSubagente,
            'playbooks' => $playbooks,
            'reglas_comunicacion' => $reglasComunicacion,
            'total_playbooks' => $playbooks->count(),
            'total_reglas' => $reglasComunicacion->count(),
            'resumen' => 'Utiliza estos playbooks en orden: primero diagnostica (criterios), luego aplica el prompt correspondiente. Las reglas de comunicación son vinculantes.',
        ], 200);
    }

    public function consultarMemoriaGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'nullable|string|in:soporte,vendedor,cobranzas,postventa',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $tipoSubagente = $request->input('tipo');

        $memoriaGeneral = \App\Models\ChatMemoriaNegocio::query()
            ->where('activo', true)
            ->where('categoria', 'general')
            ->orderBy('prioridad')
            ->orderBy('clave')
            ->get(['tipo', 'categoria', 'clave', 'titulo', 'resumen', 'contenido', 'tags', 'fuente', 'prioridad', 'visibilidad'])
            ->map(function ($item) {
                return [
                    // Compatibilidad para nodos n8n existentes
                    'codigo' => $item->clave,
                    'nombre' => $item->titulo,
                    'tipo' => $item->tipo,
                    'descripcion' => $item->resumen,
                    'prompt' => $item->contenido,
                    'criterios' => $item->tags ?? [],
                    'contenido' => $item->contenido,
                    // Campos canonicos del nuevo esquema
                    'clave' => $item->clave,
                    'titulo' => $item->titulo,
                    'resumen' => $item->resumen,
                    'tags' => $item->tags ?? [],
                    'fuente' => $item->fuente,
                    'prioridad' => $item->prioridad,
                    'visibilidad' => $item->visibilidad,
                ];
            });

        $memoriaEspecifica = collect();
        if ($tipoSubagente) {
            $memoriaEspecifica = \App\Models\ChatMemoriaNegocio::query()
                ->where('activo', true)
                ->where(function ($q) use ($tipoSubagente) {
                    $q->where('categoria', $tipoSubagente)
                      ->orWhere('clave', 'like', $tipoSubagente . '_%');
                })
                ->where('categoria', '!=', 'general')
                ->orderBy('prioridad')
                ->orderBy('clave')
                ->get(['tipo', 'categoria', 'clave', 'titulo', 'resumen', 'contenido', 'tags', 'fuente', 'prioridad', 'visibilidad'])
                ->map(function ($item) {
                    return [
                        // Compatibilidad para nodos n8n existentes
                        'codigo' => $item->clave,
                        'nombre' => $item->titulo,
                        'tipo' => $item->tipo,
                        'descripcion' => $item->resumen,
                        'prompt' => $item->contenido,
                        'criterios' => $item->tags ?? [],
                        'contenido' => $item->contenido,
                        // Campos canonicos del nuevo esquema
                        'clave' => $item->clave,
                        'titulo' => $item->titulo,
                        'resumen' => $item->resumen,
                        'tags' => $item->tags ?? [],
                        'fuente' => $item->fuente,
                        'prioridad' => $item->prioridad,
                        'visibilidad' => $item->visibilidad,
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'tipo_subagente' => $tipoSubagente,
            'memoria_general' => $memoriaGeneral,
            'memoria_especifica' => $memoriaEspecifica,
            'total_general' => $memoriaGeneral->count(),
            'total_especifica' => $memoriaEspecifica->count(),
            'resumen' => 'Primero usa memoria_general para reglas globales. Luego aplica memoria_especifica del subagente cuando exista.',
        ], 200);
    }

    private function nextWhatsappClientName(): string
    {
        $prefix = 'Cliente WhatsApp';
        $names = Cliente::query()
            ->where('nombrecli', 'like', $prefix . '%')
            ->pluck('nombrecli')
            ->all();

        $used = [];
        foreach ($names as $name) {
            $normalized = trim((string) $name);

            if ($normalized === $prefix) {
                $used[0] = true;
                continue;
            }

            if (preg_match('/^Cliente WhatsApp\s+(\d+)$/', $normalized, $matches) === 1) {
                $used[(int) $matches[1]] = true;
            }
        }

        $sequence = 1;
        while (isset($used[$sequence])) {
            $sequence++;
        }

        return $prefix . ' ' . $sequence;
    }

    private function calcularPrecioRenovacionAgente(Collection $detallesVenta, int $meses): array
    {
        $baseMensual = (float) $detallesVenta->sum('montodet');
        $servicios = $detallesVenta
            ->map(fn ($detalle) => (string) ($detalle->perfil?->cuenta?->valor?->servicio?->idser ?? ''))
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        $productoMensual = $this->buscarProductoMensualParaRenovacion($servicios);
        $precioMensual = $productoMensual ? (float) $productoMensual->preciopro : $baseMensual;
        $total = round($precioMensual * $meses, 2);

        return [
            'total' => $total,
            'base_mensual' => round($precioMensual, 2),
            'pricing_strategy' => $productoMensual ? 'producto_mensual' : 'detalles_actuales',
            'producto_base' => $productoMensual ? [
                'id' => $productoMensual->id,
                'nombre' => $productoMensual->nombrepro,
                'precio_mensual' => round((float) $productoMensual->preciopro, 2),
            ] : null,
            'detalles' => $this->prorratearMontoPorDetallesAgente($detallesVenta, $total),
        ];
    }

    private function buscarProductoMensualParaRenovacion(array $serviciosOrdenados): ?Producto
    {
        if (empty($serviciosOrdenados)) {
            return null;
        }

        $serviciosUnicos = array_values(array_unique($serviciosOrdenados));
        $cantidadServicios = count($serviciosOrdenados);

        $productos = Producto::with('detalles')
            ->where('activo', true)
            ->whereHas('detalles', function ($query) use ($serviciosUnicos) {
                $query->whereIn('idser', $serviciosUnicos)
                    ->where('meses', 1);
            })
            ->get();

        foreach ($productos as $producto) {
            $serviciosProducto = $producto->detalles
                ->where('meses', 1)
                ->pluck('idser')
                ->map(fn ($idser) => (string) $idser)
                ->sort()
                ->values()
                ->toArray();

            if (count($serviciosProducto) !== $cantidadServicios) {
                continue;
            }

            if ($serviciosProducto === $serviciosOrdenados) {
                return $producto;
            }
        }

        return null;
    }

    private function prorratearMontoPorDetallesAgente(Collection $detallesVenta, float $total): array
    {
        $baseMensual = (float) $detallesVenta->sum('montodet');
        $detalles = $detallesVenta->values();

        if ($detalles->isEmpty()) {
            return [];
        }

        if ($baseMensual <= 0) {
            $montoUniforme = round($total / $detalles->count(), 2);
            $acumulado = 0;

            return $detalles->map(function ($detalle, $index) use ($detalles, $montoUniforme, $total, &$acumulado) {
                $monto = $index === $detalles->count() - 1 ? round($total - $acumulado, 2) : $montoUniforme;
                $acumulado += $monto;

                return [
                    'iddet' => (int) $detalle->iddet,
                    'monto' => $monto,
                ];
            })->values()->toArray();
        }

        $acumulado = 0;

        return $detalles->map(function ($detalle, $index) use ($detalles, $baseMensual, $total, &$acumulado) {
            $proporcion = ((float) $detalle->montodet) / $baseMensual;
            $monto = $index === $detalles->count() - 1
                ? round($total - $acumulado, 2)
                : round($total * $proporcion, 2);

            $acumulado += $monto;

            return [
                'iddet' => (int) $detalle->iddet,
                'monto' => $monto,
            ];
        })->values()->toArray();
    }

    private function resolverCredencialesEntrega(
        ?string $servicio,
        ?int $perfilNumero,
        ?string $usuarioCuenta,
        ?string $contrasenaCuenta,
        ?string $pinPerfil
    ): array {
        $esPerfilAdmin = $perfilNumero === 1;
        $servicioNormalizado = strtoupper(trim((string) $servicio));
        $esSpotify = str_contains($servicioNormalizado, 'SPOTIFY');

        if ($esPerfilAdmin) {
            return [
                'cuenta' => $usuarioCuenta,
                'contrasenacue' => $contrasenaCuenta,
                'pinper' => $pinPerfil,
                'regla' => 'perfil_admin_con_credenciales_cuenta',
            ];
        }

        // Para servicios no Spotify, siempre se entregan credenciales de la cuenta.
        if (!$esSpotify) {
            return [
                'cuenta' => $usuarioCuenta,
                'contrasenacue' => $contrasenaCuenta,
                'pinper' => $pinPerfil,
                'regla' => 'perfil_no_admin_con_credenciales_cuenta',
            ];
        }

        $credencialesPin = $this->credencialesDesdePinPerfil($pinPerfil);

        if ($credencialesPin !== null) {
            return [
                'cuenta' => $credencialesPin['usuario'],
                'contrasenacue' => $credencialesPin['clave'],
                'pinper' => $pinPerfil,
                'regla' => 'perfil_no_admin_desde_pinper',
            ];
        }

        return [
            'cuenta' => null,
            'contrasenacue' => null,
            'pinper' => $pinPerfil,
            'regla' => 'perfil_no_admin_sin_credenciales_pin',
        ];
    }

    private function credencialesDesdePinPerfil(?string $pinPerfil): ?array
    {
        $pin = trim((string) $pinPerfil);

        if ($pin === '' || strtolower($pin) === 'libre') {
            return null;
        }

        foreach (['|', ':', ';'] as $separador) {
            if (str_contains($pin, $separador)) {
                $partes = explode($separador, $pin, 2);
                $usuario = trim((string) ($partes[0] ?? ''));
                $clave = trim((string) ($partes[1] ?? ''));

                if ($usuario !== '' && $clave !== '') {
                    return [
                        'usuario' => $usuario,
                        'clave' => $clave,
                    ];
                }
            }
        }

        return [
            'usuario' => $pin,
            'clave' => $pin,
        ];
    }

    private function normalizarServicioIdGarantia(string $servicio): ?string
    {
        $raw = strtoupper(trim($servicio));
        $raw = str_replace(['-', '_', ' '], '', $raw);

        $mapa = [
            'PRIME' => 'PRIME',
            'PRIMEVIDEO' => 'PRIME',
            'AMAZONPRIME' => 'PRIME',
            'MAX' => 'MAX',
            'HBO' => 'MAX',
            'HBOMAX' => 'MAX',
            'CRUNCHY' => 'CRUNCHY',
            'CRUNCHYROLL' => 'CRUNCHY',
            'PARAMOUNT' => 'PARAMOUNT',
            'PARAMOUNTPLUS' => 'PARAMOUNT',
            'MAGIS' => 'MAGIS',
            'FLUJO' => 'FLUJO',
            'FLUJOTV' => 'FLUJO',
        ];

        return $mapa[$raw] ?? null;
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
        $queryPerfilesVacios = Perfil::where('idcue', $cuenta->idcue)
            ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos
                    WHERE view_usuarios_activos.idcue = perfiles.idcue
                    AND view_usuarios_activos.perfil = perfiles.numeroper) = 0')
            ->orderBy('numeroper');

        if ($forUpdate) {
            $queryPerfilesVacios->lockForUpdate();
        }

        $perfil = $queryPerfilesVacios->first();

        if ($perfil) {
            return $perfil;
        }

        $queryPerfilesConUno = Perfil::where('idcue', $cuenta->idcue)
            ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos
                    WHERE view_usuarios_activos.idcue = perfiles.idcue
                    AND view_usuarios_activos.perfil = perfiles.numeroper) = 1')
            ->orderBy('numeroper');

        if ($forUpdate) {
            $queryPerfilesConUno->lockForUpdate();
        }

        return $queryPerfilesConUno->first();
    }
}
