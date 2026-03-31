<?php

namespace App\Observers;

use App\Models\Costo;
use App\Services\DashboardService;
use Carbon\Carbon;

class CostoObserver
{
    private function scheduleSync(string $date): void
    {
        app()->terminating(function () use ($date) {
            app(DashboardService::class)->guardar($date);
        });
    }

    public function created(Costo $costo): void
    {
        $this->scheduleSync(Carbon::parse($costo->fechacos)->toDateString());
    }

    public function updated(Costo $costo): void
    {
        $this->scheduleSync(Carbon::parse($costo->fechacos)->toDateString());

        if ($costo->wasChanged('fechacos')) {
            $oldDate = Carbon::parse($costo->getOriginal('fechacos'))->toDateString();
            $this->scheduleSync($oldDate);
        }
    }

    public function deleted(Costo $costo): void
    {
        $this->scheduleSync(Carbon::parse($costo->fechacos)->toDateString());
    }
}
