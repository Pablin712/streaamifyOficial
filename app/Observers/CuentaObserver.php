<?php

namespace App\Observers;

use App\Models\Cuenta;
use App\Models\CuentaIncidencia;
use Illuminate\Support\Str;

/**
 * Registra cuanto tiempo pasa una cuenta marcada como danada (caidacue),
 * sin importar desde donde se togglee el flag (CuentaController::status,
 * UsuarioController::marcarCuentaDanada, o cualquier otro lugar futuro).
 * Ver docs/finanzas/dashboardInteligenciaNegocio.md.
 */
class CuentaObserver
{
    public function updated(Cuenta $cuenta): void
    {
        if (!$cuenta->wasChanged('caidacue') || Str::endsWith($cuenta->idcue, 'Atencion')) {
            return;
        }

        if ($cuenta->caidacue) {
            $cuenta->loadMissing('valor.servicio');

            CuentaIncidencia::create([
                'idcue' => $cuenta->idcue,
                'servicio_idser' => $cuenta->valor->idser ?? null,
                'inicio' => now(),
            ]);

            return;
        }

        $abierta = CuentaIncidencia::where('idcue', $cuenta->idcue)
            ->whereNull('fin')
            ->latest('inicio')
            ->first();

        if ($abierta) {
            $abierta->fin = now();
            $abierta->duracion_minutos = $abierta->inicio->diffInMinutes($abierta->fin);
            $abierta->save();
        }
    }
}
