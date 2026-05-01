<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Historial;
use App\Models\Recarga;
use App\Services\BancoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentVerificationController extends Controller
{
    private const BOT_ACTOR_LABEL = '🤖 Empleado Laravel (bot IA)';

    public function __construct(private BancoService $bancoService)
    {
        request()->headers->set('Accept', 'application/json');
    }

    public function comprobante(int $idrec)
    {
        $recarga = Recarga::find($idrec);

        if (!$recarga) {
            return response()->json([
                'success' => false,
                'message' => 'Recarga no encontrada.',
            ], 404);
        }

        if (empty($recarga->foto)) {
            return response()->json([
                'success' => false,
                'message' => 'La recarga no tiene comprobante asociado.',
            ], 404);
        }

        $filePath = public_path('storage/' . ltrim($recarga->foto, '/'));
        if (!is_file($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo de comprobante no encontrado.',
            ], 404);
        }

        return response()->file($filePath, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function detalleRecarga(int $idrec)
    {
        $recarga = Recarga::with(['cliente', 'banco', 'estado'])->find($idrec);

        if (!$recarga) {
            return response()->json([
                'success' => false,
                'message' => 'Recarga no encontrada.',
            ], 404);
        }

        $repetidos = Recarga::where('numcomprobante', $recarga->numcomprobante)
            ->where('idrec', '!=', $recarga->idrec)
            ->orderByDesc('created_at')
            ->get(['idrec', 'idestado', 'created_at']);

        $filePath = empty($recarga->foto) ? null : public_path('storage/' . ltrim($recarga->foto, '/'));
        $fileExists = $filePath ? is_file($filePath) : false;
        $fileSize = $fileExists ? filesize($filePath) : null;
        $fileMime = $fileExists ? mime_content_type($filePath) : null;
        $banco = $recarga->banco;
        $bankAliases = $this->resolveBankAliases($banco?->nombreban);

        return response()->json([
            'success' => true,
            'data' => [
                'idrec' => $recarga->idrec,
                'idcli' => $recarga->idcli,
                'cliente' => [
                    'nombre' => optional($recarga->cliente)->nombrecli,
                    'telefono' => optional($recarga->cliente)->telefonocli,
                    'email' => optional($recarga->cliente)->email,
                ],
                'idban' => $recarga->idban,
                'banco' => optional($recarga->banco)->nombreban,
                'banco_aliases' => $bankAliases,
                'banco_data' => $banco ? [
                    'idban' => $banco->idban,
                    'nombreban' => $banco->nombreban,
                    'alias' => $bankAliases,
                    'propietarioban' => $banco->propietarioban,
                    'cedulaban' => $banco->cedulaban,
                    'numeroban' => $banco->numeroban,
                    'tipoban' => $banco->tipoban,
                    'detalleban' => $banco->detalleban,
                    'foto' => $banco->foto,
                    'monto' => isset($banco->monto) ? (float) $banco->monto : null,
                    'created_at' => optional($banco->created_at)->toIso8601String(),
                    'updated_at' => optional($banco->updated_at)->toIso8601String(),
                ] : null,
                'numcomprobante' => $recarga->numcomprobante,
                'valor' => (float) $recarga->valor,
                'verification_hints' => [
                    'allow_bank_alias_match' => true,
                    'allow_multiple_receipts_total_match' => true,
                    'expected_total_amount' => (float) $recarga->valor,
                    'bank_match_terms' => $bankAliases,
                ],
                'idestado' => $recarga->idestado,
                'estado' => optional($recarga->estado)->nombre,
                'foto' => [
                    'path' => $recarga->foto,
                    'url' => url('/api/v2/payments/n8n/recargas/' . $recarga->idrec . '/comprobante'),
                    'download_url' => url('/api/v2/payments/n8n/recargas/' . $recarga->idrec . '/comprobante/download'),
                    'exists' => $fileExists,
                    'mime' => $fileMime,
                    'size_bytes' => $fileSize,
                ],
                'comprobante_repetido' => $repetidos->isNotEmpty(),
                'comprobante_repetido_count' => $repetidos->count(),
                'comprobante_repetido_en' => $repetidos->map(function ($item) {
                    return [
                        'idrec' => $item->idrec,
                        'idestado' => $item->idestado,
                        'created_at' => optional($item->created_at)->toIso8601String(),
                    ];
                })->values(),
                'created_at' => optional($recarga->created_at)->toIso8601String(),
                'updated_at' => optional($recarga->updated_at)->toIso8601String(),
            ],
        ]);
    }

    public function descargarComprobante(int $idrec)
    {
        $recarga = Recarga::find($idrec);

        if (!$recarga || empty($recarga->foto)) {
            return response()->json([
                'success' => false,
                'message' => 'Comprobante no encontrado para la recarga solicitada.',
            ], 404);
        }

        $filePath = public_path('storage/' . ltrim($recarga->foto, '/'));
        if (!is_file($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo de comprobante no encontrado.',
            ], 404);
        }

        return response()->download($filePath, basename($filePath));
    }

    private function resolveBankAliases(?string $bankName): array
    {
        $normalized = str()->of((string) $bankName)
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();

        return match ($normalized) {
            'banco guayaquil' => ['banco guayaquil', 'guayaquil', 'banco del barrio', 'del barrio'],
            default => array_values(array_filter([$bankName])),
        };
    }

    public function aprobar(Request $request, int $idrec)
    {
        $request->validate([
            'observacion' => 'nullable|string|max:500',
            'trace_id' => 'nullable|string|max:120',
        ]);

        try {
            DB::beginTransaction();

            $recarga = Recarga::where('idrec', $idrec)
                ->where('idestado', 1)
                ->lockForUpdate()
                ->first();

            if (!$recarga) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'La recarga no esta pendiente o no existe.',
                    'notification_text' => '⚠️ No se pudo aprobar: la recarga ya no está pendiente o no existe.',
                ], 409);
            }

            $recarga->update(['idestado' => 3]);

            $cliente = $recarga->cliente;
            if (!$cliente) {
                throw new \RuntimeException('No se encontro cliente asociado a la recarga.');
            }

            $cliente->saldo += $recarga->valor;
            $cliente->save();

            if ($recarga->idban) {
                $transaccion = $this->bancoService->registrarTransaccion(
                    $recarga->idban,
                    $recarga->valor,
                    'ingreso',
                    'Recarga #' . $recarga->idrec . ' - Cliente: ' . $cliente->nombrecli
                );

                $recarga->transaccion_id = $transaccion->id;
                $recarga->save();
            }

            Historial::create([
                'accion' => 'Recarga-Procesada-API',
                'descripcion' => self::BOT_ACTOR_LABEL . ' aprobó recarga ID: ' . $recarga->idrec . '. Valor: $' . $recarga->valor
                    . ($request->filled('trace_id') ? ' | trace_id: ' . $request->trace_id : '')
                    . ($request->filled('observacion') ? ' | obs: ' . $request->observacion : ''),
                'empleado_id' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recarga aprobada exitosamente.',
                'notification_text' => '✅🤖 Se aprobó la recarga de ' . $cliente->nombrecli . ' por $' . number_format((float) $recarga->valor, 2),
                'data' => [
                    'idrec' => $recarga->idrec,
                    'idestado' => 3,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar recarga.',
                'notification_text' => '❌ Error al aprobar la recarga.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function rechazar(Request $request, int $idrec)
    {
        $request->validate([
            'motivo' => 'nullable|string|max:500',
            'trace_id' => 'nullable|string|max:120',
        ]);

        try {
            DB::beginTransaction();

            $recarga = Recarga::where('idrec', $idrec)
                ->where('idestado', 1)
                ->lockForUpdate()
                ->first();

            if (!$recarga) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'La recarga no esta pendiente o no existe.',
                    'notification_text' => '⚠️ No se pudo rechazar: la recarga ya no está pendiente o no existe.',
                ], 409);
            }

            $recarga->update(['idestado' => 2]);

            $clienteNombre = optional($recarga->cliente)->nombrecli ?? 'cliente';

            Historial::create([
                'accion' => 'Recarga-Rechazada-API',
                'descripcion' => self::BOT_ACTOR_LABEL . ' rechazó recarga ID: ' . $recarga->idrec . '. Valor: $' . $recarga->valor
                    . ($request->filled('trace_id') ? ' | trace_id: ' . $request->trace_id : '')
                    . ($request->filled('motivo') ? ' | motivo: ' . $request->motivo : ''),
                'empleado_id' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Recarga rechazada exitosamente.',
                'notification_text' => '⛔🤖 Se rechazó la recarga de ' . $clienteNombre . ' por $' . number_format((float) $recarga->valor, 2),
                'data' => [
                    'idrec' => $recarga->idrec,
                    'idestado' => 2,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar recarga.',
                'notification_text' => '❌ Error al rechazar la recarga.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
