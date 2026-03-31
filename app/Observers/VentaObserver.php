<?php

namespace App\Observers;

use App\Models\Venta;
use App\Services\DashboardService;
use Carbon\Carbon;

class VentaObserver
{
    /**
     * Programa una resincronización de daily_statistics para la fecha dada.
     * Usa app()->terminating() para que corra DESPUÉS de enviar la respuesta HTTP,
     * sin añadir latencia al usuario. Funciona en Hostinger sin queue worker.
     */
    private function scheduleSync(string $date): void
    {
        app()->terminating(function () use ($date) {
            app(DashboardService::class)->guardar($date);
        });
    }

    public function created(Venta $venta): void
    {
        $this->scheduleSync(Carbon::parse($venta->fechaven)->toDateString());
    }

    public function updated(Venta $venta): void
    {
        $this->scheduleSync(Carbon::parse($venta->fechaven)->toDateString());

        // Si cambió la fecha de venta, sincronizar también la fecha anterior
        if ($venta->wasChanged('fechaven')) {
            $oldDate = Carbon::parse($venta->getOriginal('fechaven'))->toDateString();
            $this->scheduleSync($oldDate);
        }
    }

    public function deleted(Venta $venta): void
    {
        $this->scheduleSync(Carbon::parse($venta->fechaven)->toDateString());
    }
}
