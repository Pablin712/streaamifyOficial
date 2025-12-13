<?php
namespace App\Services;

use App\Models\Banco;
use App\Models\Transaccion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BancoService
{
    /**
     * Registra una transacción y actualiza el monto del banco.
     * @param int $banco_id
     * @param float $monto_transaccion
     * @param string $tipo 'ingreso'|'egreso'
     * @param string|null $referencia
     * @param string|null $fecha
     * @return Transaccion
     * @throws Exception
     */
    public function registrarTransaccion($banco_id, $monto_transaccion, $tipo, $referencia = null, $fecha = null)
    {
        $banco = Banco::findOrFail($banco_id);
        $monto_anterior = $banco->monto;

        // Validar saldo insuficiente para egresos
        if ($tipo === 'egreso' && $monto_anterior < $monto_transaccion) {
            throw new Exception("Saldo insuficiente en el banco {$banco->nombreban}. Saldo actual: $" . number_format($monto_anterior, 2) . ", Monto requerido: $" . number_format($monto_transaccion, 2));
        }

        $nuevo_monto = $tipo === 'ingreso'
            ? $monto_anterior + $monto_transaccion
            : $monto_anterior - $monto_transaccion;

        $banco->monto = $nuevo_monto;
        $banco->save();

        $transaccion = $banco->transacciones()->create([
            'tipo' => $tipo,
            'monto_anterior' => $monto_anterior,
            'monto_transaccion' => $monto_transaccion,
            'monto_actualizado' => $nuevo_monto,
            'referencia' => $referencia,
            'fecha' => $fecha ?? Carbon::now(),
            'anulada' => false,
        ]);

        return $transaccion;
    }

    /**
     * Anula una transacción y revierte el monto del banco.
     * @param int $transaccion_id
     * @return void
     */
    public function anularTransaccion($transaccion_id)
    {
        $transaccion = Transaccion::findOrFail($transaccion_id);

        if ($transaccion->anulada) {
            return; // Ya está anulada, no hacer nada
        }

        $banco = $transaccion->banco;

        // Revertir el monto: si fue ingreso, restamos; si fue egreso, sumamos
        if ($transaccion->tipo === 'ingreso') {
            $banco->monto -= $transaccion->monto_transaccion;
        } else {
            $banco->monto += $transaccion->monto_transaccion;
        }

        $banco->save();

        // Marcar transacción como anulada
        $transaccion->anulada = true;
        $transaccion->save();
    }
}
