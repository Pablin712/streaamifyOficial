<?php
namespace App\Services;

use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

/**
 * Orquesta prestamos personales de Pablo a deudores: el desembolso
 * (egreso de un banco o del fondo Efectivo) y los abonos (ingreso).
 * Ver docs/finanzas/deudoresPrestamos.md
 */
class PrestamoService
{
    public function __construct(
        private FondoService $fondoService,
        private BancoService $bancoService,
    ) {
    }

    /**
     * @param array $data deudor_id, monto, banco_id (nullable), fondo_id (nullable),
     *   fecha, motivo
     */
    public function otorgar(array $data): Prestamo
    {
        if (empty($data['banco_id']) && empty($data['fondo_id'])) {
            throw new Exception('El préstamo debe salir de un banco o de un fondo.');
        }
        if (!empty($data['banco_id']) && !empty($data['fondo_id'])) {
            throw new Exception('El préstamo debe salir de un banco o de un fondo, no de ambos.');
        }

        return DB::transaction(function () use ($data) {
            $fecha = $data['fecha'] ?? Carbon::now();
            $deudorNombre = \App\Models\Deudor::findOrFail($data['deudor_id'])->nombre;
            $referencia = 'Préstamo a ' . $deudorNombre . (!empty($data['motivo']) ? ' - ' . $data['motivo'] : '');

            $bancoTransaccionId = null;
            $fondoTransaccionId = null;

            if (!empty($data['banco_id'])) {
                $transaccion = $this->bancoService->registrarTransaccion(
                    $data['banco_id'], $data['monto'], 'egreso', $referencia, $fecha
                );
                $bancoTransaccionId = $transaccion->id;
            } else {
                $transaccion = $this->fondoService->registrarTransaccion(
                    $data['fondo_id'], $data['monto'], 'egreso', $referencia, $fecha
                );
                $fondoTransaccionId = $transaccion->id;
            }

            return Prestamo::create([
                'deudor_id' => $data['deudor_id'],
                'monto' => $data['monto'],
                'monto_pagado' => 0,
                'estado' => 'pendiente',
                'banco_id' => $data['banco_id'] ?? null,
                'banco_transaccion_id' => $bancoTransaccionId,
                'fondo_id' => $data['fondo_id'] ?? null,
                'fondo_transaccion_id' => $fondoTransaccionId,
                'fecha' => $fecha,
                'motivo' => $data['motivo'] ?? null,
            ]);
        });
    }

    /**
     * Registra un abono (parcial o total). El origen del cobro (banco o fondo)
     * puede ser distinto al usado al otorgar el préstamo.
     */
    public function abonar(int $prestamoId, float $monto, ?string $bancoId, ?string $fondoId): Prestamo
    {
        if (empty($bancoId) && empty($fondoId)) {
            throw new Exception('El abono debe ingresar a un banco o a un fondo.');
        }
        if (!empty($bancoId) && !empty($fondoId)) {
            throw new Exception('El abono debe ingresar a un banco o a un fondo, no a ambos.');
        }

        return DB::transaction(function () use ($prestamoId, $monto, $bancoId, $fondoId) {
            $prestamo = Prestamo::with('deudor')->findOrFail($prestamoId);
            $restante = $prestamo->monto_restante;

            if ($monto > $restante) {
                throw new Exception('El monto a abonar ($' . number_format($monto, 2) . ') no puede ser mayor al restante ($' . number_format($restante, 2) . ').');
            }

            $referencia = 'Abono de ' . $prestamo->deudor->nombre . ' - Préstamo #' . $prestamo->id;

            if ($bancoId) {
                $this->bancoService->registrarTransaccion($bancoId, $monto, 'ingreso', $referencia);
            } else {
                $this->fondoService->registrarTransaccion($fondoId, $monto, 'ingreso', $referencia);
            }

            $prestamo->monto_pagado += $monto;
            if ($prestamo->monto_pagado >= $prestamo->monto) {
                $prestamo->estado = 'pagado';
            }
            $prestamo->save();

            return $prestamo;
        });
    }

    /**
     * Abona contra la deuda TOTAL de un deudor (puede tener varios prestamos
     * otorgados por separado). Se registra una sola transaccion de ingreso por
     * el monto total y se reparte internamente entre sus prestamos pendientes,
     * del mas antiguo al mas reciente (FIFO), hasta agotar el abono.
     */
    public function abonarDeudor(int $deudorId, float $monto, ?string $bancoId, ?string $fondoId): array
    {
        if (empty($bancoId) && empty($fondoId)) {
            throw new Exception('El abono debe ingresar a un banco o a un fondo.');
        }
        if (!empty($bancoId) && !empty($fondoId)) {
            throw new Exception('El abono debe ingresar a un banco o a un fondo, no a ambos.');
        }

        return DB::transaction(function () use ($deudorId, $monto, $bancoId, $fondoId) {
            $deudor = \App\Models\Deudor::findOrFail($deudorId);

            $prestamosPendientes = Prestamo::where('deudor_id', $deudorId)
                ->where('estado', 'pendiente')
                ->orderBy('fecha')
                ->lockForUpdate()
                ->get();

            $totalPendiente = $prestamosPendientes->sum(fn ($p) => $p->monto_restante);

            if ($prestamosPendientes->isEmpty() || $totalPendiente <= 0) {
                throw new Exception($deudor->nombre . ' no tiene préstamos pendientes de cobro.');
            }
            if ($monto > $totalPendiente) {
                throw new Exception('El monto a abonar ($' . number_format($monto, 2) . ') no puede ser mayor a la deuda total pendiente ($' . number_format($totalPendiente, 2) . ').');
            }

            $referencia = 'Abono de ' . $deudor->nombre . ' - deuda total';

            if ($bancoId) {
                $this->bancoService->registrarTransaccion($bancoId, $monto, 'ingreso', $referencia);
            } else {
                $this->fondoService->registrarTransaccion($fondoId, $monto, 'ingreso', $referencia);
            }

            $restanteAbono = $monto;
            foreach ($prestamosPendientes as $prestamo) {
                if ($restanteAbono <= 0) {
                    break;
                }
                $aplicar = min($restanteAbono, $prestamo->monto_restante);
                $prestamo->monto_pagado += $aplicar;
                if ($prestamo->monto_pagado >= $prestamo->monto) {
                    $prestamo->estado = 'pagado';
                }
                $prestamo->save();
                $restanteAbono -= $aplicar;
            }

            return [
                'deudor' => $deudor,
                'monto_abonado' => $monto,
                'restante' => $totalPendiente - $monto,
            ];
        });
    }
}
