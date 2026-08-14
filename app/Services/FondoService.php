<?php
namespace App\Services;

use App\Models\Fondo;
use App\Models\FondoTransaccion;
use Carbon\Carbon;
use Exception;

class FondoService
{
    /**
     * Registra una transacción y actualiza el saldo del fondo.
     * Calco de BancoService::registrarTransaccion, pero con precision decimal(18,4)
     * para reflejar el fondo (ej. Mi Negocio Efectivo trabaja con milesimas de dolar).
     */
    public function registrarTransaccion($fondo_id, $monto_transaccion, $tipo, $referencia = null, $fecha = null)
    {
        $fondo = Fondo::findOrFail($fondo_id);
        $saldo_anterior = $fondo->saldo;

        if ($tipo === 'egreso' && $saldo_anterior < $monto_transaccion) {
            throw new Exception("Saldo insuficiente en el fondo {$fondo->nombre}. Saldo actual: $" . number_format($saldo_anterior, 4) . ", Monto requerido: $" . number_format($monto_transaccion, 4));
        }

        $nuevo_saldo = $tipo === 'ingreso'
            ? $saldo_anterior + $monto_transaccion
            : $saldo_anterior - $monto_transaccion;

        $fondo->saldo = $nuevo_saldo;
        $fondo->save();

        return $fondo->transacciones()->create([
            'tipo' => $tipo,
            'monto_anterior' => $saldo_anterior,
            'monto_transaccion' => $monto_transaccion,
            'monto_actualizado' => $nuevo_saldo,
            'referencia' => $referencia,
            'fecha' => $fecha ?? Carbon::now(),
            'anulada' => false,
        ]);
    }

    /**
     * Anula una transacción y revierte el saldo del fondo.
     */
    public function anularTransaccion($transaccion_id)
    {
        $transaccion = FondoTransaccion::findOrFail($transaccion_id);

        if ($transaccion->anulada) {
            return;
        }

        $fondo = $transaccion->fondo;

        if ($transaccion->tipo === 'ingreso') {
            $fondo->saldo -= $transaccion->monto_transaccion;
        } else {
            $fondo->saldo += $transaccion->monto_transaccion;
        }

        $fondo->save();

        $transaccion->anulada = true;
        $transaccion->save();
    }
}
