<?php
namespace App\Services;

use App\Models\MneRecarga;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

/**
 * Orquesta el ciclo completo de una recarga de Mi Negocio Efectivo:
 * el egreso del fondo operativo (costo real) y, opcionalmente, el ingreso
 * de lo cobrado al cliente (banco real o fondo Efectivo).
 * Ver docs/finanzas/miNegocioEfectivo.md
 */
class MneRecargaService
{
    public function __construct(
        private FondoService $fondoService,
        private BancoService $bancoService,
    ) {
    }

    /**
     * @param array $data operadora, cliente_nombre, cliente_telefono, valor_cobrado,
     *   costo_fondo, fondo_id, cobro_banco_id (nullable), cobro_fondo_id (nullable),
     *   fecha, notas
     */
    public function registrarRecarga(array $data): MneRecarga
    {
        if (!empty($data['cobro_banco_id']) && !empty($data['cobro_fondo_id'])) {
            throw new Exception('El cobro debe registrarse en un banco o en un fondo, no en ambos.');
        }

        return DB::transaction(function () use ($data) {
            $fecha = $data['fecha'] ?? Carbon::now();
            $referencia = 'Recarga ' . $data['operadora'] . (!empty($data['cliente_nombre']) ? ' - ' . $data['cliente_nombre'] : '');

            $fondoTransaccion = $this->fondoService->registrarTransaccion(
                $data['fondo_id'],
                $data['costo_fondo'],
                'egreso',
                $referencia,
                $fecha
            );

            $bancoTransaccionId = null;
            $fondoCobroTransaccionId = null;

            if (!empty($data['cobro_banco_id'])) {
                $bancoTransaccion = $this->bancoService->registrarTransaccion(
                    $data['cobro_banco_id'],
                    $data['valor_cobrado'],
                    'ingreso',
                    'Cobro ' . $referencia,
                    $fecha
                );
                $bancoTransaccionId = $bancoTransaccion->id;
            } elseif (!empty($data['cobro_fondo_id'])) {
                $fondoCobroTransaccion = $this->fondoService->registrarTransaccion(
                    $data['cobro_fondo_id'],
                    $data['valor_cobrado'],
                    'ingreso',
                    'Cobro ' . $referencia,
                    $fecha
                );
                $fondoCobroTransaccionId = $fondoCobroTransaccion->id;
            }

            return MneRecarga::create([
                'operadora' => $data['operadora'],
                'cliente_nombre' => $data['cliente_nombre'] ?? null,
                'cliente_telefono' => $data['cliente_telefono'] ?? null,
                'valor_cobrado' => $data['valor_cobrado'],
                'costo_fondo' => $data['costo_fondo'],
                'ganancia' => $data['valor_cobrado'] - $data['costo_fondo'],
                'fondo_id' => $data['fondo_id'],
                'fondo_transaccion_id' => $fondoTransaccion->id,
                'banco_id' => $data['cobro_banco_id'] ?? null,
                'banco_transaccion_id' => $bancoTransaccionId,
                'fondo_cobro_id' => $data['cobro_fondo_id'] ?? null,
                'fondo_cobro_transaccion_id' => $fondoCobroTransaccionId,
                'fecha' => $fecha,
                'notas' => $data['notas'] ?? null,
            ]);
        });
    }

    public function anularRecarga(int $id): void
    {
        DB::transaction(function () use ($id) {
            $recarga = MneRecarga::findOrFail($id);

            if ($recarga->anulada) {
                return;
            }

            if ($recarga->fondo_transaccion_id) {
                $this->fondoService->anularTransaccion($recarga->fondo_transaccion_id);
            }
            if ($recarga->banco_transaccion_id) {
                $this->bancoService->anularTransaccion($recarga->banco_transaccion_id);
            }
            if ($recarga->fondo_cobro_transaccion_id) {
                $this->fondoService->anularTransaccion($recarga->fondo_cobro_transaccion_id);
            }

            $recarga->anulada = true;
            $recarga->save();
        });
    }
}
