<?php

namespace App\Http\Controllers;

use App\Models\Cuenta;
use App\Models\Valor;
use App\Models\Servicio;
use App\Models\Perfil;
use App\Models\Costo;
use App\Models\ViewUsuarioActivo;
use App\Models\Producto;
use App\Models\Historial;
use App\Models\Deuda;
use App\Services\CuentaService;
use App\Services\BancoService;
use App\Services\NetflixCodigoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class CuentaController extends Controller
{
    protected $cuentaService;
    protected $bancoService;
    protected $netflixCodigoService;

    public function __construct(CuentaService $cuentaService, BancoService $bancoService, NetflixCodigoService $netflixCodigoService)
    {
        $this->cuentaService = $cuentaService;
        $this->bancoService = $bancoService;
        $this->netflixCodigoService = $netflixCodigoService;
    }
    public function index(Request $request)
    {
        if (!Gate::allows('cuentas')) {
            abort(403, 'No tienes permiso para ver las cuentas.');
        }
        $cuentas = $this->cuentaService->obtenerCuentasSegunPermiso($empleado = Auth::user());

        // Separar cuentas individuales y excluirlas de las demás pestañas
        $cuentasIndividuales = $cuentas->filter(fn($c) => $c->tipo_cuenta === 'individual');
        $cuentas = $cuentas->filter(fn($c) => $c->tipo_cuenta !== 'individual');

        //$this->cuentaService->asignarUsuarios($cuentas);
        // Filtrar las cuentas en diferentes categorías
        $cuentasColapsadas = $this->cuentaService->obtenerCuentasColapsadas($cuentas);
        $cuentasDisponibles = $this->cuentaService->obtenerCuentasDisponibles($cuentas);
        $cuentasSinOcupar = $this->cuentaService->obtenerCuentasSinOcupar($cuentas);
        $cuentasPorVencer = $this->cuentaService->obtenerCuentasPorVencer($cuentas);
        $cuentasConUsuariosVencidos = $this->cuentaService->obtenerCuentasConUsuariosVencidos($cuentas);
        $cuentasCaidas = $this->cuentaService->obtenerCuentasCaidas($cuentas);
        $mesa = $this->cuentaService->obtenerMesasDeTrabajo();

        $espacios_por_servicio = $this->cuentaService->calcularEspaciosPorServicio();

        // Obtener valores activos para los modales
        $valores = Valor::where('activoval', true)->get();

        // Obtener servicios y proveedores para el modal de crear valor
        $servicios = \App\Models\Servicio::all();
        $proveedores = \App\Models\Proveedor::all();

        // Obtener bancos para los modales de crear y renovar cuenta
        $bancos = \App\Models\Banco::orderBy('nombreban')->get();

        return view('inventory.cuentas.index', compact(
            'cuentas',
            'cuentasIndividuales',
            'cuentasDisponibles',
            'cuentasColapsadas',
            'cuentasSinOcupar',
            'cuentasPorVencer',
            'cuentasConUsuariosVencidos',
            'cuentasCaidas',
            'mesa',
            'espacios_por_servicio',
            'valores',
            'servicios',
            'proveedores',
            'bancos'
        ));
    }

    public function create()
    {
        if (!Gate::allows('cuentas.store')) {
            abort(403, 'No tienes permiso para crear cuentas.');
        }
        $valores = Valor::where('activoval', true)->get();
        return view('inventory.cuentas.create', compact('valores'));
    }
    public function show($idcue)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para ver una cuenta con sus perfiles.');
        }
        $cuenta = Cuenta::with(['valor', 'costos'])->findOrFail($idcue);
        $perfiles = $this->cuentaService->calcularUsuariosPorPerfil($cuenta);
        return view('inventory.cuentas.show', compact('cuenta', 'perfiles'));
    }

    public function loadPerfiles(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver esta cuenta.'
            ], 403);
        }

        try {
            $cuenta = Cuenta::with(['valor', 'costos'])->findOrFail($idcue);
            $perfiles = $this->cuentaService->calcularUsuariosPorPerfil($cuenta);

            $html = view('inventory.cuentas.partials.perfiles-content', compact('cuenta', 'perfiles'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'cuenta' => [
                    'idcue' => $cuenta->idcue,
                    'usuariocue' => $cuenta->usuariocue,
                    'servicio' => $cuenta->valor->idser ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los perfiles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function PerfilesSpotify()
    {
        if (!Gate::allows('todas_las_cuentas') || !Gate::allows('spotify')) {
            abort(403, 'No tienes permiso para ver las cuentas de spotify.');
        }
        $cuentas = $this->cuentaService->obtenerCuentasPorServicioSinFiltros("SPOTIFY");
        return view('inventory.cuentas.spotify', compact('cuentas'));
    }

    public function store(Request $request)
    {
        // Log detallado para debugging
        Log::info('=== INICIO STORE CUENTA ===');
        Log::info('Método: ' . $request->method());
        Log::info('Content-Type: ' . $request->header('Content-Type'));
        Log::info('Accept: ' . $request->header('Accept'));
        Log::info('Datos recibidos: ' . json_encode($request->all()));

        if (!Gate::allows('cuentas.store')) {
            Log::warning('Permiso denegado para crear cuentas');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para crear cuentas.'
                ], 403);
            }
            abort(403, 'No tienes permiso para crear cuentas.');
        }

        try {
            // Convertir a mayúsculas solo si se proporciona idcue manualmente
            if ($request->filled('idcue')) {
                $request->merge([
                    'idcue' => strtoupper($request->idcue)
                ]);
                Log::info('ID cuenta manual (mayúsculas): ' . $request->idcue);
            } else {
                Log::info('ID cuenta será generado automáticamente por el trigger');
            }

            $validated = $request->validate([
                'idcue' => 'nullable|string|max:20|unique:cuentas,idcue',
                'tipo_cuenta' => 'required|in:completa,individual',
                'idval' => 'required|exists:valores,idval',
                'fechavencue' => 'required|date',
                'usuariocue' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('cuentas', 'usuariocue')->where(function ($query) {
                        return $query->where('activocue', true);
                    })
                ],
                'contrasenacue' => 'required|string|max:50',
                'caidacue' => 'required|boolean',
            ]);

            Log::info('Validación exitosa');

            $cuenta = Cuenta::create($validated);

            // Cuando idcue se genera por trigger, Eloquent puede mantener 0/null en memoria.
            // Resolver el ID real persistido antes de crear registros relacionados (costos/deudas).
            $cuentaId = $cuenta->idcue;
            if (empty($cuentaId) || (string) $cuentaId === '0') {
                $cuentaPersistida = Cuenta::where('usuariocue', $validated['usuariocue'])
                    ->where('activocue', true)
                    ->latest('created_at')
                    ->first();

                if (!$cuentaPersistida || empty($cuentaPersistida->idcue) || (string) $cuentaPersistida->idcue === '0') {
                    throw new \Exception('No se pudo resolver el ID real de la cuenta creada.');
                }

                $cuenta = $cuentaPersistida;
                $cuentaId = $cuentaPersistida->idcue;
            }

            Historial::create([
                'accion' => 'Se creó la cuenta con ID: ' . $cuenta->idcue,
                'descripcion' => 'Datos: ' . json_encode($cuenta),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            // Actualizar estado de productos relacionados con el servicio de la cuenta creada
            $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

            if ($request->filled('descripcioncos') || $request->filled('montocos')) {
                $validatedCosto = $request->validate([
                    'descripcioncos' => 'required|string|max:50',
                    'montocos' => 'required|numeric|min:0',
                ]);

                $sePago = $request->has('se_pago') && $request->se_pago == '1';
                $transaccionId = null;

                // Si se pagó, validar banco y crear transacción
                if ($sePago) {
                    $request->validate([
                        'banco_id' => 'required|exists:bancos,idban',
                    ]);

                    try {
                        $transaccion = $this->bancoService->registrarTransaccion(
                            $request->banco_id,
                            $validatedCosto['montocos'],
                            'egreso',
                            'Compra/Creación de cuenta: ' . $cuentaId . ' - ' . $validatedCosto['descripcioncos']
                        );
                        $transaccionId = $transaccion->id;
                    } catch (\Exception $e) {
                        throw new \Exception('Error al procesar transacción bancaria: ' . $e->getMessage());
                    }
                }

                $costo = Costo::create([
                    'idcue' => $cuentaId,
                    'fechacos' => now(),
                    'montocos' => $validatedCosto['montocos'],
                    'descripcioncos' => $validatedCosto['descripcioncos'],
                    'transaccion_id' => $transaccionId,
                ]);

                Historial::create([
                    'accion' => 'Se creó el costo con ID: ' . $costo->idcos,
                    'descripcion' => 'Datos: ' . json_encode($costo),
                    'empleado_id' => Auth::user()->idemp,
                    'created_at' => now(),
                ]);

                // Si NO se pagó, crear/acumular deuda
                if (!$sePago) {
                    // Obtener proveedor desde: cuenta recién creada -> valor -> proveedor
                    $cuenta->loadMissing('valor.proveedor');
                    $proveedor = $cuenta->valor->proveedor ?? null;

                    if ($proveedor) {
                        // Buscar o crear deuda pendiente para este proveedor
                        $deuda = Deuda::firstOrCreate(
                            [
                                'proveedor_id' => $proveedor->idpro,
                                'estado' => 'pendiente'
                            ],
                            [
                                'monto' => 0,
                                'monto_pagado' => 0
                            ]
                        );

                        // ACUMULAR el monto a la deuda existente
                        $deuda->monto += $validatedCosto['montocos'];
                        $deuda->save();

                        Historial::create([
                            'accion' => 'Deuda acumulada a proveedor',
                            'descripcion' => 'Proveedor: ' . $proveedor->nombrepro . ' - Monto acumulado: $' . $validatedCosto['montocos'] . ' - Total deuda: $' . $deuda->monto,
                            'empleado_id' => Auth::user()->idemp,
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            Log::info('Cuenta creada exitosamente: ' . $cuenta->idcue);
            Log::info('=== FIN STORE CUENTA (ÉXITO) ===');

            // Triple verificación AJAX
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta creada con éxito.',
                    'cuenta' => $cuenta
                ], 200);
            }

            return redirect()->route('cuentas')->with('success', 'Cuenta creada con éxito.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación: ' . json_encode($e->errors()));
            Log::error('=== FIN STORE CUENTA (ERROR VALIDACIÓN) ===');

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación en los datos.',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Error general al crear cuenta: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN STORE CUENTA (ERROR GENERAL) ===');

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    public function pdf()
    {
        $cuentas = Cuenta::with('valor.proveedor', 'valor.servicio')->where('activocue', true)->get();

        $cuentasRenovar = $cuentas->filter(function ($c) {
            return $c->is_conveniente_renovar && $c->costo_mes > 0;
        })->sortBy([
            fn($a) => $a->valor->proveedor->nombrepro ?? 'Desconocido',
            fn($a) => $a->valor->servicio->nombreser ?? 'Servicio'
        ]);

        $agrupadas = $cuentasRenovar->groupBy(fn($c) => $c->valor->proveedor->nombrepro ?? 'Sin proveedor');

        $fecha = now()->toDateString();
        $pdf = Pdf::loadView('inventory.cuentas.pdf', compact('cuentas', 'agrupadas', 'fecha'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif']);
        $nombreArchivo = "Inventario-Cuentas-{$fecha}.pdf";
        return $pdf->download($nombreArchivo);
    }

    public function status($idcue)
    {
        if (!Gate::allows('cuentas.status')) {
            abort(403, 'No tienes permiso para actualizar el estado de la cuenta.');
        }
        $cuenta = Cuenta::with('valor')->findOrFail($idcue);
        $cuenta->caidacue = !$cuenta->caidacue;
        $cuenta->save();

        Historial::create([
            'accion' => 'Se actualizó el estado de cuenta con ID: ' . $cuenta->idcue,
            'descripcion' => 'Estado cambiado a ' . ($cuenta->caidacue ? 'Dañada' : 'Activa'),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

        // Triple verificación AJAX - TIEMPO REAL
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            // Calcular nuevo estado para badge
            $fechaVencimiento = Carbon::parse($cuenta->fechavencue);
            $diasRestantes = Carbon::today()->diffInDays($fechaVencimiento, false);

            if ($cuenta->caidacue) {
                $statusClass = 'dark';
                $statusText = 'Dañada';
            } elseif ($diasRestantes <= 0) {
                $statusClass = 'danger';
                $statusText = 'Vencida';
            } elseif ($diasRestantes <= 5) {
                $statusClass = 'warning';
                $statusText = 'Ya vence';
            } else {
                $statusClass = 'success';
                $statusText = 'Activa';
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado de la cuenta actualizado correctamente.',
                'cuenta' => $cuenta,
                'statusClass' => $statusClass,
                'statusText' => $statusText
            ]);
        }

        return redirect()->route('cuentas')->with('success', 'Estado de la cuenta actualizado correctamente.');
    }

    public function moverClientes(Request $request) //mesa de trabajo
    {
        if (!Gate::allows('usuarios.change')) {
            abort(403, 'No tienes permiso para cambiar usuarios de la cuenta.');
        }
        $cuentaOrigen = Cuenta::find($request->input('cuenta_origen'));
        $cuentaDestino = $this->cuentaService->obtenerCuentaDestino($cuentaOrigen);

        // Actualiza todos los clientes/perfiles de la cuenta origen a la cuenta destino
        $respuesta = $this->cuentaService->moverClientesDeCuenta($cuentaOrigen, $cuentaDestino);
        if (!$respuesta) {
            return redirect()->back()->with('error', 'No se pudieron mover los clientes a la mesa de trabajo.');
        }
        Historial::create([
            'accion' => 'Mover-Usuarios-Mesa',
            'descripcion' => 'Se movieron clientes de la cuenta con ID: ' . $cuentaOrigen->idcue . ' a la cuenta con ID: ' . $cuentaDestino->idcue,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Clientes movidos a la mesa de trabajo correctamente.');
    }

    public function moverClientesDisperso(Request $request)
    {
        if (!Gate::allows('usuarios.change')) {
            abort(403, 'No tienes permiso para cambiar usuarios de la cuenta.');
        }
        $cuentaOrigen = Cuenta::find($request->input('cuenta_origen'));

        $respuesta = $this->cuentaService->mudarClientesAOtraCuenta($cuentaOrigen);
        if (($respuesta['status'] ?? null) === 'empty') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron mover los clientes a otro espacio.',
                ], 422);
            }

            return redirect()->back()->with('error', 'No se pudieron mover los clientes a otro espacio.');
        }

        if (($respuesta['status'] ?? null) === 'partial') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Algunos usuarios fueron movidos correctamente.',
                    'movements' => $respuesta['movements'] ?? [],
                    'partial' => true,
                ]);
            }

            return redirect()->back()->with('warning', 'Algunos usuarios fueron movidos correctamente.');
        }

        if (($request->expectsJson() || $request->ajax()) && ($respuesta['status'] ?? null) === 'success') {
            return response()->json([
                'success' => true,
                'message' => 'Clientes movidos correctamente.',
                'movements' => $respuesta['movements'] ?? [],
            ]);
        }

        if (($respuesta['status'] ?? null) === 'success') {
            return redirect()->back()->with('success', 'Clientes movidos correctamente.');
        }

        return redirect()->back()->with('info', 'Resultado desconocido.');
    }

    public function enviarMensajeClientes(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para enviar mensajes a clientes de la cuenta.');
        }

        $validated = $request->validate([
            'mensaje' => 'required|string|min:3|max:1200',
        ]);

        $cuenta = Cuenta::with(['valor.proveedor'])->findOrFail($idcue);

        $cooldownKey = 'cuenta_msg_cooldown_' . $cuenta->idcue;
        $nowTimestamp = now()->timestamp;
        $cooldownUntil = (int) Cache::get($cooldownKey, 0);

        if ($cooldownUntil > $nowTimestamp) {
            $secondsRemaining = max(1, $cooldownUntil - $nowTimestamp);

            return response()->json([
                'success' => false,
                'message' => 'Este botón está temporalmente bloqueado para evitar spam. Intenta nuevamente en unos minutos.',
                'remaining_seconds' => $secondsRemaining,
                'cooldown_until' => now()->addSeconds($secondsRemaining)->toIso8601String(),
            ], 429);
        }

        $usuariosActivos = ViewUsuarioActivo::with('cliente')
            ->where('idcue', $cuenta->idcue)
            ->where('fecha_vencimiento', '>', now())
            ->get();

        if ($usuariosActivos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'La cuenta no tiene clientes activos para mensajear.',
            ], 422);
        }

        $clientes = $usuariosActivos->sortByDesc('fecha_vencimiento')->map(function ($usuario) {
            $telefono = $usuario->cliente?->telefonocli;
            $fechaVencimiento = $usuario->fecha_vencimiento
                ? Carbon::parse($usuario->fecha_vencimiento)->toDateString()
                : null;

            return [
                'idcli' => $usuario->idcli,
                'nombre' => $usuario->nombre_cliente,
                'telefono' => $telefono,
                'telefono_normalizado' => $telefono ? preg_replace('/\D+/', '', $telefono) : null,
                'idven' => $usuario->idven,
                'iddet' => $usuario->iddet,
                'perfil' => $usuario->perfil,
                'fecha_vencimiento' => $fechaVencimiento,
            ];
        })->unique(function ($cliente) {
            if (!empty($cliente['idcli'])) {
                return 'idcli:' . $cliente['idcli'];
            }

            if (!empty($cliente['telefono_normalizado'])) {
                return 'tel:' . $cliente['telefono_normalizado'];
            }

            return 'nom:' . strtolower(trim((string) ($cliente['nombre'] ?? '')));
        })->values();

        $webhookUrl = config('services.n8n.account_message_webhook');
        if (empty($webhookUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'No está configurado el webhook para mensajear clientes.',
            ], 500);
        }

        $empleado = Auth::user();
        $payload = [
            'event' => 'cuenta.custom_message',
            'trace_id' => 'cuenta-msg-' . $cuenta->idcue . '-' . now()->format('Ymd-His'),
            'mensaje_personalizado' => trim($validated['mensaje']),
            'cuenta' => [
                'idcue' => $cuenta->idcue,
                'idval' => $cuenta->idval,
                'usuario' => $cuenta->usuariocue,
                'servicio' => $cuenta->valor->idser ?? null,
                'proveedor' => $cuenta->valor->proveedor->nombrepro ?? null,
                'estado_cuenta' => $cuenta->caidacue ? 'danada' : 'activa',
                'fecha_vencimiento' => $cuenta->fechavencue
                    ? Carbon::parse($cuenta->fechavencue)->toDateString()
                    : null,
            ],
            'cliente_count' => $clientes->count(),
            'clientes' => $clientes,
            'empleado' => [
                'idemp' => $empleado->idemp,
                'nombreemp' => $empleado->nombreemp,
                'usuarioemp' => $empleado->usuarioemp,
            ],
            'sent_at' => now()->toIso8601String(),
        ];

        try {
            $requestN8n = Http::acceptJson()
                ->timeout(10)
                ->retry(1, 300);

            $webhookSecret = config('services.n8n.payment_webhook_secret');
            if (!empty($webhookSecret)) {
                $requestN8n = $requestN8n->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                ]);
            }

            $response = $requestN8n->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook n8n de mensaje a clientes devolvió error', [
                    'idcue' => $cuenta->idcue,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar al webhook. Intenta nuevamente.',
                ], 502);
            }

            $cooldownExpiresAt = now()->addMinutes(10);
            Cache::put($cooldownKey, $cooldownExpiresAt->timestamp, $cooldownExpiresAt);

            Historial::create([
                'accion' => 'Mensaje masivo a clientes de cuenta',
                'descripcion' => 'Cuenta: ' . $cuenta->idcue . ' | Clientes: ' . $clientes->count(),
                'empleado_id' => $empleado->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente al webhook de n8n.',
                'cooldown_until' => $cooldownExpiresAt->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error enviando mensaje de cuenta a n8n', [
                'idcue' => $cuenta->idcue,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al enviar el mensaje a n8n.',
            ], 500);
        }
    }

    public function enviarMensajeProveedor(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para enviar mensajes al proveedor de la cuenta.');
        }

        $validated = $request->validate([
            'proveedor' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:50',
            'mensaje' => 'required|string|min:3|max:4000',
        ]);

        $telefonoNormalizado = preg_replace('/\D+/', '', $validated['telefono']);
        if (empty($telefonoNormalizado)) {
            return response()->json([
                'success' => false,
                'message' => 'El proveedor no tiene un teléfono válido para enviar el mensaje.',
            ], 422);
        }

        $cuenta = Cuenta::with(['valor.proveedor', 'valor.servicio'])->findOrFail($idcue);

        $webhookUrl = config('services.n8n.client_message_webhook');
        if (empty($webhookUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'No está configurado el webhook para enviar mensajes.',
            ], 500);
        }

        $empleado = Auth::user();
        $payload = [
            'event' => 'provider.account_message',
            'trace_id' => 'proveedor-msg-' . $cuenta->idcue . '-' . now()->format('Ymd-His'),
            'cliente' => [
                'idcli' => null,
                'nombre' => $validated['proveedor'] ?: ($cuenta->valor->proveedor->nombrepro ?? 'Proveedor'),
                'telefono' => $validated['telefono'],
                'telefono_normalizado' => $telefonoNormalizado,
                'tipo' => 'proveedor',
            ],
            'cuenta' => [
                'idcue' => $cuenta->idcue,
                'usuario' => $cuenta->usuariocue,
                'servicio' => $cuenta->valor->servicio->nombreser ?? ($cuenta->valor->idser ?? null),
                'proveedor' => $cuenta->valor->proveedor->nombrepro ?? null,
            ],
            'mensaje' => trim($validated['mensaje']),
            'empleado' => [
                'idemp' => $empleado->idemp,
                'nombreemp' => $empleado->nombreemp,
                'usuarioemp' => $empleado->usuarioemp,
            ],
            'sent_at' => now()->toIso8601String(),
        ];

        try {
            $requestN8n = Http::acceptJson()
                ->timeout(10)
                ->retry(1, 300);

            $webhookSecret = config('services.n8n.payment_webhook_secret');
            if (!empty($webhookSecret)) {
                $requestN8n = $requestN8n->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                ]);
            }

            $response = $requestN8n->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook n8n de mensaje a proveedor devolvió error', [
                    'idcue' => $cuenta->idcue,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar el mensaje al webhook.',
                ], 502);
            }

            Historial::create([
                'accion' => 'Mensaje directo a proveedor',
                'descripcion' => 'Cuenta: ' . $cuenta->idcue . ' | Proveedor: ' . ($payload['cliente']['nombre'] ?? 'Proveedor') . ' | Teléfono: ' . $validated['telefono'],
                'empleado_id' => $empleado->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente al proveedor por n8n.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error enviando mensaje a proveedor vía n8n', [
                'idcue' => $cuenta->idcue,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al enviar el mensaje al proveedor.',
            ], 500);
        }
    }

    public function enviarInventarioProveedor(Request $request)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para enviar inventario al proveedor.');
        }

        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,idpro',
            'cuentas' => 'required|array|min:1',
            'cuentas.*' => 'required|string|exists:cuentas,idcue',
            'servicios' => 'required|array|min:1',
            'servicios.*' => 'required|string|max:50',
        ]);

        $idsCuentas = collect($validated['cuentas'])
            ->filter(fn($id) => !empty($id))
            ->map(fn($id) => strtoupper(trim((string) $id)))
            ->unique()
            ->values();

        if ($idsCuentas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibieron cuentas válidas para enviar inventario.',
            ], 422);
        }

        $serviciosSeleccionados = collect($validated['servicios'])
            ->filter(fn($servicio) => !empty($servicio))
            ->map(fn($servicio) => strtoupper(trim((string) $servicio)))
            ->unique()
            ->values();

        if ($serviciosSeleccionados->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes seleccionar al menos un servicio para enviar.',
            ], 422);
        }

        $cuentas = Cuenta::with(['valor.servicio', 'valor.proveedor'])
            ->whereIn('idcue', $idsCuentas)
            ->where('activocue', true)
            ->get();

        if ($cuentas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron cuentas activas para el inventario.',
            ], 422);
        }

        $cuentasProveedor = $cuentas->filter(function ($cuenta) use ($validated) {
            return (int) ($cuenta->valor->proveedor->idpro ?? 0) === (int) $validated['proveedor_id'];
        })->values();

        if ($cuentasProveedor->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Las cuentas seleccionadas no pertenecen al proveedor indicado.',
            ], 422);
        }

        $cuentasPorServicio = $cuentasProveedor->filter(function ($cuenta) use ($serviciosSeleccionados) {
            $idServicio = strtoupper((string) ($cuenta->valor->idser ?? ''));
            return $serviciosSeleccionados->contains($idServicio);
        })->values();

        if ($cuentasPorServicio->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cuentas del proveedor en los servicios seleccionados.',
            ], 422);
        }

        $proveedor = $cuentasPorServicio->first()->valor->proveedor;
        $telefonoProveedor = (string) ($proveedor->telefonopro ?? '');
        $telefonoNormalizado = preg_replace('/\D+/', '', $telefonoProveedor);

        if (empty($telefonoNormalizado)) {
            return response()->json([
                'success' => false,
                'message' => 'El proveedor no tiene un teléfono válido para enviar el inventario.',
            ], 422);
        }

        $mensaje = $cuentasPorServicio
            ->sortBy([
                fn($cuenta) => strtoupper((string) ($cuenta->valor->idser ?? '')),
                fn($cuenta) => strtoupper((string) ($cuenta->usuariocue ?? '')),
            ])
            ->groupBy(fn($cuenta) => strtoupper((string) ($cuenta->valor->idser ?? 'SERVICIO')))
            ->map(function ($cuentasServicio) {
                $primeraCuenta = $cuentasServicio->first();
                $tituloServicio = $primeraCuenta->valor->servicio->nombreser
                    ?? $primeraCuenta->valor->idser
                    ?? 'SERVICIO';

                $lineas = [$tituloServicio];

                foreach ($cuentasServicio as $cuenta) {
                    $fecha = $cuenta->fechavencue
                        ? Carbon::parse($cuenta->fechavencue)->toDateString()
                        : '-';

                    $lineas[] = trim(($cuenta->usuariocue ?? '') . '  ' . $fecha);
                }

                return implode("\n", $lineas);
            })
            ->implode("\n\n");

        $webhookUrl = config('services.n8n.client_message_webhook');
        if (empty($webhookUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'No está configurado el webhook para enviar mensajes.',
            ], 500);
        }

        $empleado = Auth::user();
        $payload = [
            'event' => 'provider.inventory_message',
            'trace_id' => 'proveedor-inventario-' . $proveedor->idpro . '-' . now()->format('Ymd-His'),
            'cliente' => [
                'idcli' => null,
                'nombre' => $proveedor->nombrepro,
                'telefono' => $telefonoProveedor,
                'telefono_normalizado' => $telefonoNormalizado,
                'tipo' => 'proveedor',
            ],
            'mensaje' => $mensaje,
            'inventario' => [
                'proveedor_id' => $proveedor->idpro,
                'proveedor_nombre' => $proveedor->nombrepro,
                'servicios' => $serviciosSeleccionados->values(),
                'cuentas_ids' => $cuentasPorServicio->pluck('idcue')->values(),
                'total_cuentas' => $cuentasPorServicio->count(),
            ],
            'empleado' => [
                'idemp' => $empleado->idemp,
                'nombreemp' => $empleado->nombreemp,
                'usuarioemp' => $empleado->usuarioemp,
            ],
            'sent_at' => now()->toIso8601String(),
        ];

        try {
            $requestN8n = Http::acceptJson()
                ->timeout(12)
                ->retry(1, 300);

            $webhookSecret = config('services.n8n.payment_webhook_secret');
            if (!empty($webhookSecret)) {
                $requestN8n = $requestN8n->withHeaders([
                    'X-Webhook-Secret' => $webhookSecret,
                ]);
            }

            $response = $requestN8n->post($webhookUrl, $payload);

            if (!$response->successful()) {
                Log::warning('Webhook n8n de inventario a proveedor devolvió error', [
                    'proveedor_id' => $proveedor->idpro,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo enviar el inventario al webhook.',
                ], 502);
            }

            Historial::create([
                'accion' => 'Inventario enviado a proveedor',
                'descripcion' => 'Proveedor: ' . $proveedor->nombrepro . ' | Servicios: ' . $serviciosSeleccionados->implode(', ') . ' | Cuentas: ' . $cuentasPorServicio->count(),
                'empleado_id' => $empleado->idemp,
                'created_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inventario enviado correctamente al proveedor.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error enviando inventario a proveedor vía n8n', [
                'proveedor_id' => $proveedor->idpro,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al enviar el inventario al proveedor.',
            ], 500);
        }
    }

    public function pedirCodigoNetflix(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para pedir codigos de Netflix.');
        }

        $cuenta = Cuenta::with(['valor.proveedor', 'valor.servicio'])->findOrFail($idcue);

        $resultado = $this->netflixCodigoService->requestCode($cuenta, [
            'type' => 'empleado',
            'id' => Auth::user()->idemp,
            'name' => Auth::user()->nombreemp,
            'username' => Auth::user()->usuarioemp,
            'phone' => Auth::user()->telefonoemp,
        ]);

        if (!($resultado['success'] ?? false)) {
            return response()->json($resultado, 422);
        }

        Historial::create([
            'accion' => 'Solicitud de codigo Netflix',
            'descripcion' => 'Cuenta: ' . $cuenta->idcue . ' | Proveedor: ' . ($cuenta->valor->proveedor->nombrepro ?? 'N/A') . ' | Codigo obtenido',
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        return response()->json($resultado);
    }

    public function mensaje($perfilId)
    {
        if (!Gate::allows('cuentas.mensaje')) {
            abort(403, 'No tienes permiso para solicitar datos de perfil.');
        }
        $perfil = Perfil::find($perfilId);
        $cuenta = Cuenta::where('idcue', $perfil->idcue)->first();
        $valor = Valor::find($cuenta->idval);
        $servicio = Servicio::find($valor->idser);
        $bot = $valor->botval;
        dd($bot);

        $mensaje = "*{$servicio->nombre}*\n";
        $mensaje .= "Usuario: {$cuenta->usuariocue}\n";
        $mensaje .= "Clave: {$cuenta->contrasenacue}\n";
        $mensaje .= "PIN de perfil {$perfil->numeroper}: {$perfil->pinper}\n";

        // Agregar mensaje adicional si tiene un bot de códigos
        if (!empty($bot)) {
            $mensaje .= "\n\n*Nota importante:*\n";
            $mensaje .= "Esta cuenta incluye un bot de códigos. Si en algún momento se te solicita un código de acceso, puedes obtenerlo ingresando al siguiente enlace:\n";
            $mensaje .= "{$bot}\n";
            $mensaje .= "Por favor, sigue las instrucciones del bot para obtener el código de acceso. ¡Gracias por tu confianza!";
        }
        Historial::create([
            'accion' => 'Se solicitó los datos de perfil ' . $perfil->numeroper . ' de la cuenta: ' . $cuenta->idcue,
            'descripcion' => null,
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);
        return response()->json(['mensaje' => $mensaje]);
    }

    public function edit($idcue)
    {
        if (!Gate::allows('cuentas.update')) {
            abort(403, 'No tienes permiso para editar cuentas.');
        }
        $cuenta = Cuenta::with(['valor.proveedor'])->findOrFail($idcue);
        $valores = Valor::where('activoval', true)->get();

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'cuenta' => [
                    'idcue' => $cuenta->idcue,
                    'idval' => $cuenta->idval,
                    'usuariocue' => $cuenta->usuariocue,
                    'contrasenacue' => $cuenta->contrasenacue,
                    'fechavencue' => $cuenta->fechavencue,
                    'caidacue' => $cuenta->caidacue,
                    'usuarios_activos' => $cuenta->usuarios_activos,
                    'valor' => [
                        'idser' => $cuenta->valor->idser ?? null,
                        'proveedor' => [
                            'nombrepro' => $cuenta->valor->proveedor->nombrepro ?? null,
                        ]
                    ]
                ]
            ]);
        }

        return view('inventory.cuentas.edit', compact('cuenta', 'valores'));
    }

    public function renew($idcue)
    {
        if (!Gate::allows('cuentas.renew')) {
            abort(403, 'No tienes permiso para renovar cuentas.');
        }
        $cuenta = Cuenta::with(['valor.proveedor'])->findOrFail($idcue);
        $valor = $cuenta->idval;

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'cuenta' => [
                    'idcue' => $cuenta->idcue,
                    'idval' => $cuenta->idval,
                    'usuariocue' => $cuenta->usuariocue,
                    'contrasenacue' => $cuenta->contrasenacue,
                    'fechavencue' => $cuenta->fechavencue,
                    'caidacue' => $cuenta->caidacue,
                    'valor' => [
                        'idser' => $cuenta->valor->idser ?? null,
                        'proveedor' => [
                            'nombrepro' => $cuenta->valor->proveedor->nombrepro ?? null,
                        ]
                    ]
                ]
            ]);
        }

        return view('inventory.cuentas.renew', compact('cuenta', 'valor'));
    }

    public function saveRenew(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.renew')) {
            abort(403, 'No tienes permiso para renovar cuentas.');
        }

        try {
            $request->validate([
                'nuevafechavencue' => 'required|date|after:today',
                'montocos' => 'required|numeric|min:0',
                'descripcioncos' => 'required|string|max:50'
            ]);

            $cuenta = Cuenta::findOrFail($idcue);

            // Crear historial
            Historial::create([
                'accion' => 'Renovación de Cuenta',
                'descripcion' => 'Fecha antigua: ' . $cuenta->fechavencue . ' | Nueva fecha: ' . $request->nuevafechavencue,
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            // Actualizar fecha de vencimiento
            $cuenta->update([
                'fechavencue' => $request->nuevafechavencue
            ]);

            // Procesar pago y crear costo
            $sePago = $request->has('se_pago') && $request->se_pago == '1';
            $transaccionId = null;

            // Si se pagó, validar banco y crear transacción
            if ($sePago) {
                $request->validate([
                    'banco_id' => 'required|exists:bancos,idban',
                ]);

                try {
                    $transaccion = $this->bancoService->registrarTransaccion(
                        $request->banco_id,
                        $request->montocos,
                        'egreso',
                        'Renovación de cuenta: ' . $cuenta->idcue . ' - ' . $request->descripcioncos
                    );
                    $transaccionId = $transaccion->id;
                } catch (\Exception $e) {
                    throw new \Exception('Error al procesar transacción bancaria: ' . $e->getMessage());
                }
            }

            // Crear registro de costo
            $costo = Costo::create([
                'idcue' => $cuenta->idcue,
                'descripcioncos' => $request->descripcioncos,
                'montocos' => $request->montocos,
                'fechacos' => now(),
                'transaccion_id' => $transaccionId,
            ]);

            // Si NO se pagó, crear/acumular deuda
            if (!$sePago) {
                // Obtener proveedor desde: cuenta -> valor -> proveedor
                $cuenta = Cuenta::with('valor.proveedor')->find($cuenta->idcue);
                $proveedor = $cuenta->valor->proveedor;

                if ($proveedor) {
                    // Buscar o crear deuda pendiente para este proveedor
                    $deuda = Deuda::firstOrCreate(
                        [
                            'proveedor_id' => $proveedor->idpro,
                            'estado' => 'pendiente'
                        ],
                        [
                            'monto' => 0,
                            'monto_pagado' => 0
                        ]
                    );

                    // ACUMULAR el monto a la deuda existente
                    $deuda->monto += $request->montocos;
                    $deuda->save();

                    Historial::create([
                        'accion' => 'Deuda acumulada a proveedor',
                        'descripcion' => 'Proveedor: ' . $proveedor->nombrepro . ' - Monto acumulado: $' . $request->montocos . ' - Total deuda: $' . $deuda->monto,
                        'empleado_id' => Auth::user()->idemp,
                        'created_at' => now(),
                    ]);
                }
            }

            // Actualizar estado de productos
            $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

            // Triple verificación AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta renovada exitosamente',
                    'cuenta' => $cuenta->fresh()->load('valor')
                ]);
            }

            return redirect()->route('cuentas')->with('success', 'Cuenta renovada con éxito.');
        } catch (\Exception $e) {
            // Triple verificación AJAX para errores
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al renovar la cuenta: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al renovar la cuenta: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $idcue)
    {
        if (!Gate::allows('cuentas.update')) {
            abort(403, 'No tienes permiso para actualizar cuentas.');
        }
        try {
            $request->validate([
                'idval' => 'required|exists:valores,idval',
                'fechavencue' => 'required|date',
                'usuariocue' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('cuentas', 'usuariocue')
                        ->ignore($idcue, 'idcue')
                        ->where(function ($query) {
                            return $query->where('activocue', true);
                        })
                ],
                'contrasenacue' => 'required|string|max:50',
                'caidacue' => 'required|boolean|min:1'
            ]);
            $request->merge([
                'idcue' => strtoupper($request->idcue)
            ]);
            $cuenta = Cuenta::findOrFail($idcue);

            Historial::create([
                'accion' => 'Actualización de Cuenta',
                'descripcion' => 'Datos antiguos: ' . json_encode($cuenta),
                'empleado_id' => Auth::user()->idemp,
                'created_at' => now(),
            ]);

            $cuenta->update($request->all());
            $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);
            if (!empty($request->descripcioncos) && !empty($request->montocos)) {
                $validatedCosto = $request->validate([
                    'descripcioncos' => 'string|max:50',
                    'montocos' => 'numeric|min:0',
                ]);
                Costo::create([
                    'idcue' => $cuenta->idcue,
                    'descripcioncos' => $request->descripcioncos,
                    'montocos' => $request->montocos,
                    'fechacos' => now(),
                ]);
            }

            // Triple verificación AJAX
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta actualizada exitosamente',
                    'cuenta' => $cuenta->fresh()->load('valor')
                ]);
            }

            return redirect()->route('cuentas')->with('success', 'Cuenta actualizada con éxito.');
        } catch (\Exception $e) {
            // Triple verificación AJAX para errores
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la cuenta: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Hubo un problema al crear la cuenta: ' . $e->getMessage()]);
        }
    }

    public function destroy($idcue)
    {
        if (!Gate::allows('cuentas.destroy')) {
            abort(403, 'No tienes permiso para eliminar cuentas.');
        }
        $cuenta = Cuenta::with(['valor.servicio', 'perfiles'])->findOrFail($idcue);
        $cuentaInUsuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->exists();

        if ($cuentaInUsuariosActivos) {
            // Triple verificación AJAX para error
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la cuenta porque uno o más clientes aun la usan'
                ], 422);
            }
            return redirect()->route('cuentas')->with('error', 'No se puede eliminar la cuenta porque uno o más clientes aun la usan');
        }

        if (!$this->historicoDesacopladoDisponible()) {
            $message = 'No se puede eliminar la cuenta todavía. Falta ejecutar la migración de desacople histórico para preservar ventas, costos y mantenimientos.';

            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 409);
            }

            return redirect()->route('cuentas')->with('error', $message);
        }

        Historial::create([
            'accion' => 'Se eliminó la cuenta con ID: ' . $cuenta->idcue,
            'descripcion' => 'Cuenta eliminada preservando histórico: ' . json_encode($cuenta),
            'empleado_id' => Auth::user()->idemp,
            'created_at' => now(),
        ]);

        $servicioNombre = $cuenta->valor?->servicio?->nombreser ?? $cuenta->valor?->idser;
        $perfiles = $cuenta->perfiles;
        $idsPerfiles = $perfiles->pluck('idper')->filter()->values();

        DB::transaction(function () use ($cuenta, $servicioNombre, $perfiles, $idsPerfiles) {
            foreach ($perfiles as $perfil) {
                DB::table('detalles_venta')
                    ->where('idper', $perfil->idper)
                    ->update([
                        'idper_snapshot' => DB::raw("COALESCE(idper_snapshot, '" . addslashes($perfil->idper) . "')"),
                        'idcue_snapshot' => DB::raw("COALESCE(idcue_snapshot, '" . addslashes($cuenta->idcue) . "')"),
                        'idval_snapshot' => DB::raw("COALESCE(idval_snapshot, '" . addslashes((string) $cuenta->idval) . "')"),
                        'servicio_snapshot' => DB::raw("COALESCE(servicio_snapshot, '" . addslashes((string) $servicioNombre) . "')"),
                        'cuenta_usuario_snapshot' => DB::raw("COALESCE(cuenta_usuario_snapshot, '" . addslashes((string) $cuenta->usuariocue) . "')"),
                        'perfil_numeroper_snapshot' => DB::raw('COALESCE(perfil_numeroper_snapshot, ' . (int) $perfil->numeroper . ')'),
                    ]);
            }

            DB::table('costos')
                ->where('idcue', $cuenta->idcue)
                ->update([
                    'idcue_snapshot' => DB::raw("COALESCE(idcue_snapshot, '" . addslashes($cuenta->idcue) . "')"),
                    'idval_snapshot' => DB::raw("COALESCE(idval_snapshot, '" . addslashes((string) $cuenta->idval) . "')"),
                    'servicio_snapshot' => DB::raw("COALESCE(servicio_snapshot, '" . addslashes((string) $servicioNombre) . "')"),
                    'cuenta_usuario_snapshot' => DB::raw("COALESCE(cuenta_usuario_snapshot, '" . addslashes((string) $cuenta->usuariocue) . "')"),
                ]);

            DB::table('mantenimientos')
                ->where('idcue', $cuenta->idcue)
                ->update([
                    'idcue_snapshot' => DB::raw("COALESCE(idcue_snapshot, '" . addslashes($cuenta->idcue) . "')"),
                    'idval_snapshot' => DB::raw("COALESCE(idval_snapshot, '" . addslashes((string) $cuenta->idval) . "')"),
                    'servicio_snapshot' => DB::raw("COALESCE(servicio_snapshot, '" . addslashes((string) $servicioNombre) . "')"),
                    'cuenta_usuario_snapshot' => DB::raw("COALESCE(cuenta_usuario_snapshot, '" . addslashes((string) $cuenta->usuariocue) . "')"),
                ]);

            if ($idsPerfiles->isNotEmpty()) {
                Perfil::whereIn('idper', $idsPerfiles)->delete();
            }

            $cuenta->delete();
        });

        $this->cuentaService->actualizarEstadoProductos($cuenta->valor->idser);

        // Triple verificación AJAX
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Cuenta eliminada exitosamente preservando histórico'
            ]);
        }

        return redirect()->route('cuentas')->with('success', 'Cuenta eliminada con éxito preservando histórico.');
    }

    private function historicoDesacopladoDisponible(): bool
    {
        return Schema::hasColumn('detalles_venta', 'idper_snapshot')
            && Schema::hasColumn('detalles_venta', 'idcue_snapshot')
            && Schema::hasColumn('detalles_venta', 'idval_snapshot')
            && Schema::hasColumn('detalles_venta', 'servicio_snapshot')
            && Schema::hasColumn('detalles_venta', 'cuenta_usuario_snapshot')
            && Schema::hasColumn('detalles_venta', 'perfil_numeroper_snapshot')
            && Schema::hasColumn('costos', 'idcue_snapshot')
            && Schema::hasColumn('costos', 'idval_snapshot')
            && Schema::hasColumn('costos', 'servicio_snapshot')
            && Schema::hasColumn('costos', 'cuenta_usuario_snapshot')
            && Schema::hasColumn('mantenimientos', 'idcue_snapshot')
            && Schema::hasColumn('mantenimientos', 'idval_snapshot')
            && Schema::hasColumn('mantenimientos', 'servicio_snapshot')
            && Schema::hasColumn('mantenimientos', 'cuenta_usuario_snapshot');
    }
}
