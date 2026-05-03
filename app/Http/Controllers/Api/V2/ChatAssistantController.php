<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Notifications\NuevoSoporteCliente;
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
use Illuminate\Support\Facades\DB;
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
                ],
            ]);
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

            return [
                'iddet' => $detalle->iddet,
                'idven' => $detalle->idven,
                'idcli' => $cliente->idcli,
                'idcue' => $detalle->perfil?->cuenta?->idcue,
                'idper' => $detalle->idper,
                'servicio' => $detalle->perfil?->cuenta?->valor?->servicio?->nombreser,
                'cuenta' => $detalle->perfil?->cuenta?->usuariocue,
                'contrasenacue' => $detalle->perfil?->cuenta?->contrasenacue,
                'perfil' => $detalle->perfil?->numeroper,
                'pinper' => $detalle->perfil?->pinper,
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
            ->with(['estado:idestado,nombreest', 'banco:idban,nombreban', 'transaccion.banco'])
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
                        'estado' => $recarga->estado->nombreest ?? null,
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
            ->with(['estado:idestado,nombreest', 'banco:idban,nombreban'])
            ->where('idcli', $cliente->idcli)
            ->orderByDesc('created_at')
            ->limit($recargasLimit)
            ->get()
            ->map(function (Recarga $recarga) {
                return [
                    'idrec' => $recarga->idrec,
                    'numcomprobante' => $recarga->numcomprobante,
                    'valor' => (float) $recarga->valor,
                    'estado' => $recarga->estado->nombreest ?? null,
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

                $entregas[] = [
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
            'idcue' => 'nullable|string|exists:cuentas,idcue',
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
                        'servicio' => $cuenta->valor?->servicio?->nombreser,
                        'usuario' => $cuenta->usuariocue,
                        'contrasenacue' => $cuenta->contrasenacue,
                        'perfil' => $usuarioActivo?->perfil,
                        'pinper' => $usuarioActivo?->profile?->pinper,
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
                    'servicio' => $cuenta->valor?->servicio?->nombreser,
                    'usuario' => $cuenta->usuariocue,
                    'contrasenacue' => $cuenta->contrasenacue,
                    'perfil' => $usuarioActivo?->perfil,
                    'pinper' => $usuarioActivo?->profile?->pinper,
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
            ->where('clave', 'like', 'general_%')
            ->orderBy('prioridad')
            ->orderBy('clave')
            ->get(['tipo', 'clave', 'titulo', 'resumen', 'contenido', 'tags', 'fuente', 'prioridad', 'visibilidad'])
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
                ->where('clave', 'like', $tipoSubagente . '_%')
                ->orderBy('prioridad')
                ->orderBy('clave')
                ->get(['tipo', 'clave', 'titulo', 'resumen', 'contenido', 'tags', 'fuente', 'prioridad', 'visibilidad'])
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
