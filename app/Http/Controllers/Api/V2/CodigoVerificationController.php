<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Codigo;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\ViewUsuarioActivo;
use App\Services\NetflixCodigoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CodigoVerificationController extends Controller
{
    private const COMPANY_PHONE_NUMBERS = [
        '593961778319',
        '593996464991',
        '593961412826',
        '593961702129',
        '593986740052',
    ];

    public function __construct(private NetflixCodigoService $netflixCodigoService)
    {
        request()->headers->set('Accept', 'application/json');
    }

    public function verificarClienteCuenta(Request $request)
    {
        try {
            $context = $this->buildVerificationContext($request);

            if (($context['error_status'] ?? null) !== null) {
                return response()->json([
                    'success' => false,
                    'message' => $context['error_message'],
                ], $context['error_status']);
            }

            return response()->json([
                'success' => true,
                'message' => $context['motivo'],
                'data' => $this->formatVerificationResponse($context),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar cliente y cuenta.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registrarCodigoEntregado(Request $request)
    {
        try {
            $registroPendienteBase = $this->findPendingCodigoRecordFromRequest($request);
            $context = $this->buildVerificationContext($request, $registroPendienteBase);
            $codigo = $this->normalizeCode($request->input('codigo'));
            $estado = $request->input('estado') === 'esperando' ? 'esperando' : 'enviado';
            $codigoEntregado = $estado === 'enviado' && $codigo !== null;

            if (($context['error_status'] ?? null) !== null) {
                return response()->json([
                    'success' => false,
                    'message' => $context['error_message'],
                ], $context['error_status']);
            }

            $registroPendiente = $registroPendienteBase ?? $this->findPendingCodigoRecord($request, $context);

            $puedeCerrarPendiente = $estado === 'enviado' && $registroPendiente !== null;

            if (!($context['puede_pedir_codigo'] ?? false) && !$puedeCerrarPendiente) {
                return response()->json([
                    'success' => false,
                    'message' => $context['motivo'] ?? 'El cliente no puede pedir un nuevo codigo en este momento.',
                    'data' => $this->formatVerificationResponse($context),
                ], 422);
            }

            $payload = [
                'codigo' => $codigo,
                'mensaje' => (string) ($request->input('detalle') ?? $request->input('mensaje_enviado') ?? $request->input('observacion') ?? $this->getIncomingMessage($request)),
                'telefono' => $context['telefono'],
                'idcli' => $context['cliente']['idcli'] ?? null,
                'idcue' => $context['cuenta']['idcue'] ?? null,
                'usuariocue' => $context['usuario_cue_normalizado'],
                'idser' => $context['cuenta']['servicio_id'] ?? $this->netflixCodigoService->getTargetServiceId(),
                'instance' => $this->normalizeNullableString($request->input('instance')),
                'apikey' => $this->normalizeNullableString($request->input('apikey')),
                'usuarios_habilitados' => $codigoEntregado ? 1 : 0,
                'estado' => $estado,
            ];

            if ($estado === 'esperando') {
                if ($registroPendiente) {
                    $registroPendiente->fill($payload);
                    $registroPendiente->save();
                    $registro = $registroPendiente;
                } else {
                    $registro = Codigo::create($payload);
                }
            } else {
                if ($registroPendiente) {
                    $registroPendiente->fill($payload);
                    $registroPendiente->estado = 'enviado';
                    $registroPendiente->save();
                    $registro = $registroPendiente;
                } else {
                    $registro = Codigo::create($payload);
                }
            }

            $context['codigos_solicitados_ultimos_10_dias'] = ($context['codigos_solicitados_ultimos_10_dias'] ?? 0) + ($codigoEntregado ? 1 : 0);
            $context['codigos_restantes'] = max(($context['usuarios_habilitados'] ?? 0) - ($context['codigos_solicitados_ultimos_10_dias'] ?? 0), 0);
            $context['ya_pidio_en_ultimos_10_dias'] = ($context['codigos_solicitados_ultimos_10_dias'] ?? 0) > 0;
            $context['puede_pedir_codigo'] = ($context['codigos_restantes'] ?? 0) > 0;
            if ($estado === 'enviado' && !$codigoEntregado) {
                $context['motivo'] = 'Intento registrado sin codigo entregado. No consume cupo en la ventana de 10 dias.';
            } else {
                $context['motivo'] = $context['puede_pedir_codigo']
                    ? 'Codigo registrado. Aun tiene cupos disponibles en la ventana de 10 dias.'
                    : 'Codigo registrado. Ya alcanzo el limite permitido para esta cuenta en la ventana de 10 dias.';
            }

            return response()->json([
                'success' => true,
                'message' => $estado === 'esperando'
                    ? 'Solicitud de codigo marcada como esperando.'
                    : 'Solicitud de codigo registrada correctamente.',
                'data' => array_merge(
                    $this->formatVerificationResponse($context),
                    [
                        'registro_id' => $registro->id,
                        'codigo' => $registro->codigo,
                        'codigo_entregado' => $codigoEntregado,
                        'estado_registrado' => $registro->estado,
                        'usuarios_habilitados_registrados' => $registro->usuarios_habilitados,
                        'instance' => $registro->instance,
                        'apikey' => $registro->apikey,
                        'fecha_solicitada' => optional($registro->created_at)->toIso8601String(),
                    ]
                ),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la solicitud del codigo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function findPendingCodigoRecord(Request $request, array $context): ?Codigo
    {
        $registroId = $request->input('registro_id');

        if ($registroId) {
            return Codigo::query()
                ->whereKey($registroId)
                ->where('estado', 'esperando')
                ->first();
        }

        return Codigo::query()
            ->where('telefono', $context['telefono'])
            ->where('usuariocue', $context['usuario_cue_normalizado'])
            ->where('idser', $context['cuenta']['servicio_id'] ?? $this->netflixCodigoService->getTargetServiceId())
            ->where('estado', 'esperando')
            ->latest('id')
            ->first();
    }

    private function findPendingCodigoRecordFromRequest(Request $request): ?Codigo
    {
        $registroId = $request->input('registro_id');

        if ($registroId) {
            return Codigo::query()
                ->whereKey($registroId)
                ->where('estado', 'esperando')
                ->first();
        }

        $telefono = $this->normalizeWhatsappNumber($this->getIncomingPhone($request));

        if ($telefono === null) {
            return null;
        }

        $usuarioCue = $this->normalizeUsuarioCue($this->getExplicitUsuarioCue($request));

        return Codigo::query()
            ->when($usuarioCue, function ($query) use ($usuarioCue) {
                $query->where('usuariocue', $usuarioCue);
            })
            ->where('telefono', $telefono)
            ->where('estado', 'esperando')
            ->latest('id')
            ->first();
    }

    private function buildVerificationContext(Request $request, ?Codigo $registroPendiente = null): array
    {
        $telefono = $this->normalizeWhatsappNumber($this->getIncomingPhone($request));
        $usuarioCue = $this->resolveUsuarioCue($request, $registroPendiente);

        if ($telefono === null) {
            return [
                'error_status' => 422,
                'error_message' => 'No se pudo interpretar el numero de WhatsApp recibido.',
            ];
        }

        if ($usuarioCue === null) {
            return [
                'error_status' => 422,
                'error_message' => 'No se pudo extraer el usuariocue desde el mensaje recibido.',
            ];
        }

        $usuarioCueNormalizado = $this->normalizeUsuarioCue($usuarioCue);

        $cuenta = Cuenta::with(['valor.servicio', 'valor.proveedor'])
            ->whereRaw('LOWER(TRIM(usuariocue)) = ?', [$usuarioCueNormalizado])
            ->first();

        $usuariosActivosCuenta = ViewUsuarioActivo::with([
            'cliente:idcli,nombrecli,telefonocli,email,pais',
            'cuenta.valor.servicio',
            'cuenta.valor.proveedor',
        ])
            ->whereHas('cuenta', function ($query) use ($usuarioCueNormalizado) {
                $query->whereRaw('LOWER(TRIM(usuariocue)) = ?', [$usuarioCueNormalizado])
                    ->where('activocue', true);
            })
            ->get();

        $usuariosActivos = $usuariosActivosCuenta
            ->filter(function ($usuario) use ($telefono) {
                return $this->phonesMatch($usuario->cliente->telefonocli ?? null, $telefono);
            })
            ->values();

        $usuarioActivo = $usuariosActivos->first();
        $esNumeroEmpresa = $this->isCompanyPhone($telefono);
        $empleadoAutorizado = $this->findAuthorizedEmployeeByPhone($telefono);
        $solicitanteInternoAutorizado = $esNumeroEmpresa || $empleadoAutorizado !== null;
        $clienteActivoEnCuenta = (bool) $usuarioActivo;
        $cuentaReferencia = $usuarioActivo->cuenta ?? $cuenta;
        $cuentaElegible = $cuentaReferencia ? $this->netflixCodigoService->isEligibleCuenta($cuentaReferencia) : false;

        $usuariosHabilitados = $cuentaElegible
            ? ($solicitanteInternoAutorizado ? $usuariosActivosCuenta->count() : $usuariosActivos->count())
            : 0;
        $fechaLimite = Carbon::now()->subDays(10);
        $codigosSolicitadosUltimos10Dias = Codigo::query()
            ->where('telefono', $telefono)
            ->where('usuariocue', $usuarioCueNormalizado)
            ->where('idser', $this->netflixCodigoService->getTargetServiceId())
            ->where('estado', 'enviado')
            ->whereNotNull('codigo')
            ->where('created_at', '>=', $fechaLimite)
            ->count();

        $codigosRestantes = max($usuariosHabilitados - $codigosSolicitadosUltimos10Dias, 0);
        $solicitanteAutorizado = $clienteActivoEnCuenta || $solicitanteInternoAutorizado;
        $puedePedirCodigo = $solicitanteAutorizado && $cuentaElegible && ($solicitanteInternoAutorizado || $codigosRestantes > 0);

        if (!$cuenta) {
            $motivo = 'No existe una cuenta con ese usuario.';
        } elseif (!$solicitanteAutorizado) {
            $motivo = 'El numero no pertenece a un cliente activo de esta cuenta.';
        } elseif (!$cuentaElegible) {
            $motivo = 'La cuenta no es elegible para pedir codigo.';
        } elseif (!$solicitanteInternoAutorizado && $codigosRestantes <= 0) {
            $motivo = 'El cliente ya agotó sus solicitudes permitidas para esta cuenta en los ultimos 10 dias.';
        } elseif ($solicitanteInternoAutorizado) {
            $motivo = 'Numero interno autorizado para validar esta cuenta.';
        } else {
            $motivo = 'Cliente activo y cuenta elegible para pedir codigo.';
        }

        return [
            'telefono' => $telefono,
            'usuario_cue' => $usuarioCue,
            'usuario_cue_normalizado' => $usuarioCueNormalizado,
            'cuenta_encontrada' => (bool) $cuenta,
            'cliente_activo_en_cuenta' => $clienteActivoEnCuenta,
            'solicitante_interno_autorizado' => $solicitanteInternoAutorizado,
            'es_numero_empresa' => $esNumeroEmpresa,
            'empleado_autorizado' => $empleadoAutorizado ? [
                'idemp' => $empleadoAutorizado->idemp,
                'nombre' => $empleadoAutorizado->nombreemp,
                'telefono' => $this->normalizePhone($empleadoAutorizado->telefonoemp),
            ] : null,
            'puede_pedir_codigo' => $puedePedirCodigo,
            'ya_pidio_en_ultimos_10_dias' => $codigosSolicitadosUltimos10Dias > 0,
            'usuarios_habilitados' => $usuariosHabilitados,
            'codigos_solicitados_ultimos_10_dias' => $codigosSolicitadosUltimos10Dias,
            'codigos_restantes' => $codigosRestantes,
            'motivo' => $motivo,
            'cliente' => [
                'idcli' => $usuarioActivo->cliente->idcli ?? null,
                'nombre' => $usuarioActivo->cliente->nombrecli ?? null,
                'telefono' => $this->normalizePhone($usuarioActivo->cliente->telefonocli ?? null),
                'email' => $usuarioActivo->cliente->email ?? null,
                'pais' => $usuarioActivo->cliente->pais ?? null,
            ],
            'cuenta' => [
                'idcue' => $usuarioActivo->cuenta->idcue ?? ($cuenta->idcue ?? null),
                'usuario' => $usuarioActivo->cuenta->usuariocue ?? ($cuenta->usuariocue ?? null),
                'servicio_id' => $usuarioActivo->cuenta->valor->idser ?? ($cuenta->valor->idser ?? null),
                'servicio' => $usuarioActivo->cuenta->valor->servicio->nombreser
                    ?? ($cuenta->valor->servicio->nombreser ?? ($usuarioActivo->cuenta->valor->idser ?? ($cuenta->valor->idser ?? null))),
                'proveedor' => $usuarioActivo->cuenta->valor->proveedor->nombrepro
                    ?? ($cuenta->valor->proveedor->nombrepro ?? null),
            ],
        ];
    }

    private function formatVerificationResponse(array $context): array
    {
        return [
            'telefono' => $context['telefono'],
            'usuario_cue' => $context['usuario_cue'],
            'cuenta_encontrada' => $context['cuenta_encontrada'],
            'cliente_activo_en_cuenta' => $context['cliente_activo_en_cuenta'],
            'solicitante_interno_autorizado' => $context['solicitante_interno_autorizado'] ?? false,
            'es_numero_empresa' => $context['es_numero_empresa'] ?? false,
            'empleado_autorizado' => $context['empleado_autorizado'] ?? null,
            'puede_pedir_codigo' => $context['puede_pedir_codigo'],
            'ya_pidio_en_ultimos_10_dias' => $context['ya_pidio_en_ultimos_10_dias'],
            'usuarios_habilitados' => $context['usuarios_habilitados'],
            'num_codigos_solicitados' => $context['codigos_solicitados_ultimos_10_dias'],
            'codigos_restantes' => $context['codigos_restantes'],
            'ventana_dias' => 10,
            'motivo' => $context['motivo'],
            'razon_rechazo' => $context['puede_pedir_codigo'] ? null : $context['motivo'],
            'cliente' => $context['cliente'],
            'cuenta' => $context['cuenta'],
        ];
    }

    private function getIncomingPhone(Request $request): string
    {
        return (string) ($request->input('numero')
            ?? $request->input('telefono')
            ?? $request->input('from')
            ?? '');
    }

    private function getIncomingMessage(Request $request): string
    {
        return (string) ($request->input('mensaje')
            ?? $request->input('message')
            ?? $request->input('texto')
            ?? '');
    }

    private function resolveUsuarioCue(Request $request, ?Codigo $registroPendiente = null): ?string
    {
        $usuarioCue = $this->getExplicitUsuarioCue($request);

        if (trim((string) $usuarioCue) !== '') {
            return trim((string) $usuarioCue);
        }

        if ($registroPendiente && trim((string) $registroPendiente->usuariocue) !== '') {
            return trim((string) $registroPendiente->usuariocue);
        }

        return $this->extractUsuarioCue($this->getIncomingMessage($request));
    }

    private function getExplicitUsuarioCue(Request $request): ?string
    {
        $usuarioCue = $request->input('usuariocue')
            ?? $request->input('usuario_cue')
            ?? $request->input('cuenta_usuario')
            ?? $request->input('usuario')
            ?? $request->input('cuenta.usuario')
            ?? null;

        return trim((string) $usuarioCue) !== '' ? trim((string) $usuarioCue) : null;
    }

    private function extractUsuarioCue(?string $message): ?string
    {
        $message = trim((string) $message);

        if ($message === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $message, 2);

        if (!$parts || count($parts) === 0) {
            return null;
        }

        if (count($parts) === 1) {
            return trim($parts[0]) !== '' ? trim($parts[0]) : null;
        }

        if (Str::lower($parts[0]) === 'net') {
            return trim($parts[1]) !== '' ? trim($parts[1]) : null;
        }

        return trim($parts[1]) !== '' ? trim($parts[1]) : trim($parts[0]);
    }

    private function normalizeUsuarioCue(?string $value): ?string
    {
        $normalized = Str::lower(trim((string) $value));

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeWhatsappNumber(?string $value): ?string
    {
        $value = trim((string) $value);
        $value = preg_replace('/@s\.whatsapp\.net$/i', '', $value);
        $value = preg_replace('/@c\.us$/i', '', $value);

        return $this->normalizePhone($value);
    }

    private function normalizePhone(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    private function normalizeCode($value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        return substr($digits, 0, 6);
    }

    private function normalizeNullableString($value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function isCompanyPhone(string $telefono): bool
    {
        return in_array($telefono, self::COMPANY_PHONE_NUMBERS, true);
    }

    private function findAuthorizedEmployeeByPhone(string $telefono): ?Empleado
    {
        return Empleado::query()
            ->with('roles')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Tecnico', 'Trabajador']);
            })
            ->get()
            ->first(function (Empleado $empleado) use ($telefono) {
                return $this->phonesMatch($empleado->telefonoemp, $telefono);
            });
    }

    private function phonesMatch(?string $databasePhone, string $incomingPhone): bool
    {
        $normalizedDatabasePhone = $this->normalizePhone($databasePhone);

        if (!$normalizedDatabasePhone) {
            return false;
        }

        return $normalizedDatabasePhone === $incomingPhone
            || Str::endsWith($normalizedDatabasePhone, $incomingPhone)
            || Str::endsWith($incomingPhone, $normalizedDatabasePhone);
    }
}
