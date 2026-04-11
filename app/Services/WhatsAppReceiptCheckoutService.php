<?php

namespace App\Services;

use App\Jobs\TriggerRecargaVerificationJob;
use App\Mail\facturaMail;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\DetalleVenta;
use App\Models\Empleado;
use App\Models\Historial;
use App\Models\Pedido;
use App\Models\Perfil;
use App\Models\Producto;
use App\Models\Recarga;
use App\Models\Venta;
use App\Models\ViewUsuarioActivo;
use App\Notifications\ComprasRealizadas;
use App\Notifications\NotificacionNueva;
use App\Notifications\PedidosPendientes;
use App\Support\ClienteAuth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class WhatsAppReceiptCheckoutService
{
    private const ESTADO_PENDIENTE = 1;
    private const ESTADO_RECHAZADA = 2;
    private const ESTADO_APROBADA = 3;

    public function process(array $payload): array
    {
        $cliente = $this->resolveCliente($payload);
        $producto = Producto::with('detalles')->findOrFail($payload['producto_id']);

        $receiptData = $this->prepareReceiptIdentity(
            file: $payload['foto'],
            providedReceiptNumber: $payload['numcomprobante'] ?? null
        );

        $duplicate = $this->findDuplicateReceipt(
            comprobanteHash: $receiptData['hash'],
            receiptNumber: $receiptData['number']
        );

        if ($duplicate) {
            return $this->duplicateResponse($duplicate);
        }

        $receiptData['path'] = $this->storeReceiptFile($payload['foto'], $receiptData['hash']);

        $recarga = $this->createRecarga(
            cliente: $cliente,
            producto: $producto,
            payload: $payload,
            receiptData: $receiptData
        );

        $this->notifyRecarga($recarga);

        try {
            TriggerRecargaVerificationJob::dispatchSync($recarga->idrec);
        } catch (\Throwable $e) {
            Log::warning('No se pudo disparar verificacion automatica para checkout por comprobante', [
                'idrec' => $recarga->idrec,
                'error' => $e->getMessage(),
            ]);

            $this->mergeRecargaMetadata($recarga, [
                'checkout_result' => 'verification_dispatch_failed',
                'verification_error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'verification_dispatch_failed',
                'message' => 'La recarga fue registrada, pero no se pudo disparar la verificación automática.',
                'data' => [
                    'recarga' => $this->formatRecarga($recarga->fresh()),
                ],
                'http_status' => 202,
            ];
        }

        $recarga = $this->waitForVerificationDecision($recarga, (int) ($payload['wait_seconds'] ?? 8));

        if ((int) $recarga->idestado === self::ESTADO_PENDIENTE) {
            $this->mergeRecargaMetadata($recarga, [
                'checkout_result' => 'verification_pending',
            ]);

            return [
                'success' => true,
                'status' => 'verification_pending',
                'message' => 'La recarga fue registrada y está pendiente de verificación.',
                'data' => [
                    'recarga' => $this->formatRecarga($recarga),
                ],
                'http_status' => 202,
            ];
        }

        if ((int) $recarga->idestado === self::ESTADO_RECHAZADA) {
            $this->mergeRecargaMetadata($recarga, [
                'checkout_result' => 'payment_rejected',
            ]);

            return [
                'success' => true,
                'status' => 'payment_rejected',
                'message' => 'El comprobante fue evaluado y la recarga fue rechazada.',
                'data' => [
                    'recarga' => $this->formatRecarga($recarga),
                ],
                'http_status' => 200,
            ];
        }

        return $this->completeCheckoutAfterApproval($cliente, $producto, $recarga, $payload);
    }

    private function resolveCliente(array $payload): Cliente
    {
        if (!empty($payload['idcli'])) {
            return Cliente::findOrFail($payload['idcli']);
        }

        $telefonoNormalizado = ClienteAuth::normalizePhone($payload['cliente_telefono'] ?? '');
        $cliente = Cliente::buscarPorTelefonoNormalizado($telefonoNormalizado);

        if ($cliente) {
            $updates = [];

            if (!empty($payload['cliente_nombre']) && empty($cliente->nombrecli)) {
                $updates['nombrecli'] = trim($payload['cliente_nombre']);
            }

            if (!empty($payload['cliente_email']) && empty($cliente->email)) {
                $updates['email'] = $payload['cliente_email'];
            }

            if ($updates !== []) {
                $cliente->update($updates);
                $cliente->refresh();
            }

            return $cliente;
        }

        return Cliente::create([
            'nombrecli' => trim((string) ($payload['cliente_nombre'] ?? 'Cliente WhatsApp')),
            'telefonocli' => $telefonoNormalizado,
            'email' => $payload['cliente_email'] ?? null,
            'password' => Str::random(16),
            'saldo' => 0,
        ]);
    }

    private function prepareReceiptIdentity(UploadedFile $file, ?string $providedReceiptNumber): array
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $receiptNumber = trim((string) $providedReceiptNumber);

        if ($receiptNumber === '') {
            $receiptNumber = 'WA-' . strtoupper(substr($hash, 0, 20));
        }

        return [
            'hash' => $hash,
            'number' => $receiptNumber,
        ];
    }

    private function storeReceiptFile(UploadedFile $file, string $hash): string
    {
        $directory = public_path('storage/comprobantes');
        File::ensureDirectoryExists($directory);

        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';
        $filename = now()->format('YmdHis') . '_' . substr($hash, 0, 16) . '.' . $extension;
        $file->move($directory, $filename);

        return 'comprobantes/' . $filename;
    }

    private function findDuplicateReceipt(string $comprobanteHash, string $receiptNumber): ?Recarga
    {
        return Recarga::query()
            ->where(function ($query) use ($comprobanteHash, $receiptNumber) {
                $query->where('comprobante_hash', $comprobanteHash)
                    ->orWhere('numcomprobante', $receiptNumber);
            })
            ->latest('created_at')
            ->first();
    }

    private function createRecarga(Cliente $cliente, Producto $producto, array $payload, array $receiptData): Recarga
    {
        $recarga = Recarga::create([
            'idcli' => $cliente->idcli,
            'idban' => $payload['idban'],
            'numcomprobante' => $receiptData['number'],
            'valor' => $payload['valor'],
            'foto' => $receiptData['path'],
            'comprobante_hash' => $receiptData['hash'],
            'idestado' => self::ESTADO_PENDIENTE,
            'origen' => $payload['canal'] ?? 'whatsapp',
            'external_reference' => $payload['external_reference'] ?? null,
            'metadata' => [
                'producto_id' => $producto->id,
                'producto_nombre' => $producto->nombrepro,
                'cliente_nombre_reportado' => $payload['cliente_nombre'] ?? null,
                'cliente_telefono_reportado' => $payload['cliente_telefono'] ?? null,
                'observacion_cliente' => $payload['observacion_cliente'] ?? null,
                'trace_id' => $payload['trace_id'] ?? ('wa-checkout-' . Str::uuid()),
            ],
        ]);

        Historial::create([
            'accion' => 'Recarga-WhatsApp-Creada',
            'descripcion' => 'Se registró recarga automática por comprobante. ID: ' . $recarga->idrec . ' | Cliente: ' . $cliente->nombrecli . ' | Producto: ' . $producto->nombrepro,
            'empleado_id' => null,
        ]);

        return $recarga;
    }

    private function notifyRecarga(Recarga $recarga): void
    {
        try {
            $empleados = Empleado::all();

            if ($empleados->isNotEmpty()) {
                Notification::send($empleados, new NotificacionNueva($recarga->load('cliente')));
                event('notificacionRecibida');
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar la nueva recarga automática', [
                'idrec' => $recarga->idrec,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function waitForVerificationDecision(Recarga $recarga, int $waitSeconds): Recarga
    {
        $deadline = microtime(true) + max(1, min($waitSeconds, 15));

        do {
            $recarga->refresh();

            if ((int) $recarga->idestado !== self::ESTADO_PENDIENTE) {
                return $recarga;
            }

            usleep(500000);
        } while (microtime(true) < $deadline);

        return $recarga->fresh();
    }

    private function completeCheckoutAfterApproval(Cliente $cliente, Producto $producto, Recarga $recarga, array $payload): array
    {
        $cliente->refresh();

        if ((float) $cliente->saldo < (float) $producto->preciopro) {
            $this->mergeRecargaMetadata($recarga, [
                'checkout_result' => 'balance_insufficient',
                'cliente_saldo' => (float) $cliente->saldo,
                'producto_precio' => (float) $producto->preciopro,
            ]);

            return [
                'success' => true,
                'status' => 'balance_insufficient',
                'message' => 'La recarga fue aprobada, pero el saldo actual no alcanza para completar la compra.',
                'data' => [
                    'recarga' => $this->formatRecarga($recarga->fresh()),
                    'cliente' => [
                        'idcli' => $cliente->idcli,
                        'saldo' => (float) $cliente->saldo,
                    ],
                    'producto' => [
                        'id' => $producto->id,
                        'nombre' => $producto->nombrepro,
                        'precio' => (float) $producto->preciopro,
                    ],
                ],
                'http_status' => 200,
            ];
        }

        if ((int) $producto->tipo_producto_id !== 1) {
            $pedido = $this->createPendingPedido($cliente, $producto, 'Pedido generado automáticamente tras recarga aprobada desde WhatsApp.');

            $this->mergeRecargaMetadata($recarga, [
                'checkout_result' => 'order_pending',
                'pedido_id' => $pedido->id,
            ]);

            return [
                'success' => true,
                'status' => 'order_pending',
                'message' => 'La recarga fue aprobada y el pedido quedó registrado para atención manual.',
                'data' => [
                    'recarga' => $this->formatRecarga($recarga->fresh()),
                    'pedido' => [
                        'id' => $pedido->id,
                        'producto' => $producto->nombrepro,
                    ],
                ],
                'http_status' => 200,
            ];
        }

        if (!$this->hasImmediateStock($producto)) {
            $pedido = $this->createPendingPedido($cliente, $producto, 'Pago aprobado desde WhatsApp pero sin stock automático. Revisar inventario y entregar manualmente.');

            $this->mergeRecargaMetadata($recarga, [
                'checkout_result' => 'stock_pending_manual',
                'pedido_id' => $pedido->id,
            ]);

            Historial::create([
                'accion' => 'Sin-Stock-WhatsApp',
                'descripcion' => 'Pago aprobado sin stock automático. Recarga ID: ' . $recarga->idrec . ' | Pedido ID: ' . $pedido->id . ' | Producto: ' . $producto->nombrepro,
                'empleado_id' => null,
            ]);

            return [
                'success' => true,
                'status' => 'stock_pending_manual',
                'message' => 'El pago fue aprobado, pero no hay cuentas disponibles ahora mismo. Se notificó al equipo para entrega manual.',
                'data' => [
                    'recarga' => $this->formatRecarga($recarga->fresh()),
                    'pedido' => [
                        'id' => $pedido->id,
                        'producto' => $producto->nombrepro,
                    ],
                ],
                'http_status' => 200,
            ];
        }

        $venta = $this->performImmediatePurchase($cliente, $producto);

        $this->mergeRecargaMetadata($recarga, [
            'checkout_result' => 'purchase_success',
            'venta_id' => $venta->idven,
        ]);

        return [
            'success' => true,
            'status' => 'purchase_success',
            'message' => 'Compra completada correctamente.',
            'data' => [
                'recarga' => $this->formatRecarga($recarga->fresh()),
                'venta' => [
                    'idven' => $venta->idven,
                    'total' => (float) ($venta->totalpagoven ?? $producto->preciopro),
                ],
                'entrega' => $this->formatDelivery($venta),
            ],
            'http_status' => 200,
        ];
    }

    private function createPendingPedido(Cliente $cliente, Producto $producto, string $respuesta): Pedido
    {
        $pedido = Pedido::create([
            'idcli' => $cliente->idcli,
            'producto_id' => $producto->id,
            'fechapedido' => now(),
            'respuesta' => $respuesta,
        ]);

        try {
            $empleados = Empleado::all();

            if ($empleados->isNotEmpty()) {
                Notification::send($empleados, new PedidosPendientes($pedido->load(['cliente', 'producto'])));
                event('notificacionRecibida');
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudo notificar pedido pendiente desde checkout WhatsApp', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $pedido;
    }

    private function hasImmediateStock(Producto $producto): bool
    {
        foreach ($producto->detalles as $detalle) {
            if (!$this->buscarCuentaDisponible($detalle->idser)) {
                return false;
            }
        }

        return true;
    }

    private function performImmediatePurchase(Cliente $cliente, Producto $producto): Venta
    {
        return DB::transaction(function () use ($cliente, $producto) {
            $venta = new Venta();
            $venta->idemp = Empleado::where('nombreemp', 'Laravel')->value('idemp') ?? 1;
            $venta->idcli = $cliente->idcli;
            $venta->fechaven = now();
            $venta->save();

            $venta->idven = DB::selectOne(
                'SELECT idven FROM ventas WHERE idcli = ? ORDER BY fechaven DESC, created_at DESC LIMIT 1',
                [$venta->idcli]
            )->idven;

            if (!$venta->idven) {
                throw new \RuntimeException('No se pudo recuperar el ID de la venta generada.');
            }

            foreach ($producto->detalles as $detalle) {
                $cuenta = $this->buscarCuentaDisponible($detalle->idser);

                if (!$cuenta) {
                    throw new \RuntimeException('No hay cuentas disponibles para el servicio.');
                }

                $perfil = $this->buscarPerfilDisponible($cuenta);

                if (!$perfil) {
                    throw new \RuntimeException('No hay perfiles disponibles en la cuenta seleccionada.');
                }

                DetalleVenta::create([
                    'idven' => $venta->idven,
                    'idper' => $perfil->idper,
                    'fechavendet' => now()->addMonths($detalle->meses)->subDay(),
                    'descripciondet' => '🤖 Venta automatizada desde comprobante WhatsApp',
                    'montodet' => $producto->preciopro / max(1, count($producto->detalles)),
                    'activodet' => true,
                ]);

                $this->verificarCuentaLlena($cuenta, $producto);
            }

            $cliente->refresh();
            $cliente->saldo -= $producto->preciopro;
            $cliente->save();

            if (!$cliente->ya_compro) {
                $cliente->ya_compro = true;
                $cliente->save();
            }

            Historial::create([
                'accion' => 'Compra-Automatica-WhatsApp',
                'descripcion' => 'Cliente realizó compra automática desde comprobante WhatsApp. Venta: ' . $venta->idven . ' | Producto: ' . $producto->nombrepro,
                'empleado_id' => Empleado::where('nombreemp', 'Laravel')->value('idemp'),
            ]);

            $venta->load(['cliente', 'detalles_venta.perfil.cuenta.valor.servicio']);

            DB::afterCommit(function () use ($venta) {
                try {
                    if (!empty($venta->cliente?->email)) {
                        Mail::to($venta->cliente->email)->send(new facturaMail($venta));
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo enviar factura desde checkout WhatsApp', [
                        'idven' => $venta->idven,
                        'error' => $e->getMessage(),
                    ]);
                }

                try {
                    $empleados = Empleado::all();

                    if ($empleados->isNotEmpty()) {
                        Notification::send($empleados, new ComprasRealizadas($venta));
                        event('notificacionRecibida');
                    }
                } catch (\Throwable $e) {
                    Log::warning('No se pudo notificar compra automática desde checkout WhatsApp', [
                        'idven' => $venta->idven,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

            return $venta->fresh(['cliente', 'detalles_venta.perfil.cuenta.valor.servicio']);
        });
    }

    private function formatDelivery(Venta $venta): array
    {
        return $venta->detalles_venta
            ->map(function (DetalleVenta $detalle) {
                $perfil = $detalle->perfil;
                $cuenta = $perfil?->cuenta;
                $servicio = $cuenta?->valor?->servicio;

                return [
                    'servicio' => $servicio?->nombreser,
                    'usuario' => $cuenta?->usuariocue,
                    'clave' => $cuenta?->contrasenacue,
                    'perfil' => $perfil?->numeroper,
                    'pin' => $perfil?->pinper,
                    'vence' => optional($detalle->fechavendet)->format('Y-m-d'),
                ];
            })
            ->values()
            ->all();
    }

    private function duplicateResponse(Recarga $recarga): array
    {
        $status = match ((int) $recarga->idestado) {
            self::ESTADO_APROBADA => 'approved',
            self::ESTADO_RECHAZADA => 'rejected',
            default => 'pending',
        };

        return [
            'success' => true,
            'status' => 'duplicate_receipt',
            'message' => 'Este comprobante ya fue evaluado previamente.',
            'data' => [
                'existing_recarga' => $this->formatRecarga($recarga),
                'evaluation_status' => $status,
                'previous_result' => $recarga->metadata['checkout_result'] ?? null,
                'previous_data' => $recarga->metadata ?? [],
            ],
            'http_status' => 200,
        ];
    }

    private function formatRecarga(Recarga $recarga): array
    {
        return [
            'idrec' => $recarga->idrec,
            'idcli' => $recarga->idcli,
            'idban' => $recarga->idban,
            'numcomprobante' => $recarga->numcomprobante,
            'valor' => (float) $recarga->valor,
            'idestado' => (int) $recarga->idestado,
            'foto' => $recarga->foto,
            'origen' => $recarga->origen,
            'external_reference' => $recarga->external_reference,
            'metadata' => $recarga->metadata ?? [],
            'created_at' => optional($recarga->created_at)->toIso8601String(),
        ];
    }

    private function mergeRecargaMetadata(Recarga $recarga, array $metadata): void
    {
        $recarga->update([
            'metadata' => array_merge($recarga->metadata ?? [], $metadata),
        ]);
    }

    private function buscarCuentaDisponible($idser): ?Cuenta
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

    private function buscarPerfilDisponible(Cuenta $cuenta): ?Perfil
    {
        $perfil = Perfil::where('idcue', $cuenta->idcue)
            ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos WHERE view_usuarios_activos.idcue = perfiles.idcue AND view_usuarios_activos.perfil = perfiles.numeroper) = 0')
            ->first();

        if (!$perfil) {
            $perfil = Perfil::where('idcue', $cuenta->idcue)
                ->whereRaw('(SELECT COUNT(*) FROM view_usuarios_activos WHERE view_usuarios_activos.idcue = perfiles.idcue AND view_usuarios_activos.perfil = perfiles.numeroper) = 1')
                ->first();
        }

        return $perfil;
    }

    private function verificarCuentaLlena(Cuenta $cuenta, Producto $producto): void
    {
        $usuariosActivos = ViewUsuarioActivo::where('idcue', $cuenta->idcue)->count();

        if ($usuariosActivos >= $cuenta->valor->pantmaxval) {
            // Mantener comportamiento actual: solo detectar, sin desactivar automáticamente.
        }
    }
}
