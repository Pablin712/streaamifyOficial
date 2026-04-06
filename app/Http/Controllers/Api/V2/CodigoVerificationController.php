<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\ViewUsuarioActivo;
use App\Services\NetflixCodigoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CodigoVerificationController extends Controller
{
    public function __construct(private NetflixCodigoService $netflixCodigoService)
    {
        request()->headers->set('Accept', 'application/json');
    }

    public function verificarClienteCuenta(Request $request)
    {
        try {
            $numeroEntrada = (string) ($request->input('numero')
                ?? $request->input('telefono')
                ?? $request->input('from')
                ?? '');

            $mensajeEntrada = (string) ($request->input('mensaje')
                ?? $request->input('message')
                ?? $request->input('texto')
                ?? '');

            $telefono = $this->normalizeWhatsappNumber($numeroEntrada);
            $usuarioCue = $this->extractUsuarioCue($mensajeEntrada);

            if ($telefono === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo interpretar el numero de WhatsApp recibido.',
                ], 422);
            }

            if ($usuarioCue === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo extraer el usuariocue desde el mensaje recibido.',
                ], 422);
            }

            $cuenta = Cuenta::with(['valor.servicio', 'valor.proveedor'])
                ->whereRaw('LOWER(TRIM(usuariocue)) = ?', [Str::lower(trim($usuarioCue))])
                ->first();

            $usuariosActivos = ViewUsuarioActivo::with([
                'cliente:idcli,nombrecli,telefonocli,email,pais',
                'cuenta.valor.servicio',
                'cuenta.valor.proveedor',
            ])
                ->whereHas('cuenta', function ($query) use ($usuarioCue) {
                    $query->whereRaw('LOWER(TRIM(usuariocue)) = ?', [Str::lower(trim($usuarioCue))])
                        ->where('activocue', true);
                })
                ->get();

            $usuarioActivo = $usuariosActivos->first(function ($usuario) use ($telefono) {
                return $this->phonesMatch($usuario->cliente->telefonocli ?? null, $telefono);
            });

            if (!$usuarioActivo) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'telefono' => $telefono,
                        'usuario_cue' => $usuarioCue,
                        'cuenta_encontrada' => (bool) $cuenta,
                        'cliente_activo_en_cuenta' => false,
                        'puede_pedir_codigo' => false,
                        'motivo' => $cuenta
                            ? 'El numero no pertenece a un cliente activo de esta cuenta.'
                            : 'No existe una cuenta con ese usuario.',
                    ],
                ]);
            }

            $puedePedirCodigo = $this->netflixCodigoService->isEligibleCuenta($usuarioActivo->cuenta);

            return response()->json([
                'success' => true,
                'data' => [
                    'telefono' => $telefono,
                    'usuario_cue' => $usuarioCue,
                    'cuenta_encontrada' => true,
                    'cliente_activo_en_cuenta' => true,
                    'puede_pedir_codigo' => $puedePedirCodigo,
                    'motivo' => $puedePedirCodigo
                        ? 'Cliente activo y cuenta elegible para pedir codigo.'
                        : 'Cliente activo encontrado, pero la cuenta no es elegible para pedir codigo.',
                    'cliente' => [
                        'idcli' => $usuarioActivo->cliente->idcli ?? null,
                        'nombre' => $usuarioActivo->cliente->nombrecli ?? null,
                        'telefono' => $this->normalizePhone($usuarioActivo->cliente->telefonocli ?? null),
                        'email' => $usuarioActivo->cliente->email ?? null,
                        'pais' => $usuarioActivo->cliente->pais ?? null,
                    ],
                    'cuenta' => [
                        'idcue' => $usuarioActivo->cuenta->idcue ?? null,
                        'usuario' => $usuarioActivo->cuenta->usuariocue ?? null,
                        'servicio_id' => $usuarioActivo->cuenta->valor->idser ?? null,
                        'servicio' => $usuarioActivo->cuenta->valor->servicio->nombreser ?? ($usuarioActivo->cuenta->valor->idser ?? null),
                        'proveedor' => $usuarioActivo->cuenta->valor->proveedor->nombrepro ?? null,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar cliente y cuenta.',
                'error' => $e->getMessage(),
            ], 500);
        }
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