<?php

namespace App\Observers;

use App\Models\Gasto;
use App\Services\DashboardService;
use Carbon\Carbon;

class GastoObserver
{
    private function scheduleSync(string $date): void
    {
        app()->terminating(function () use ($date) {
            app(DashboardService::class)->guardar($date);
        });
    }

    public function created(Gasto $gasto): void
    {
        $this->scheduleSync(Carbon::parse($gasto->fechagas)->toDateString());
    }

    public function updated(Gasto $gasto): void
    {
        $this->scheduleSync(Carbon::parse($gasto->fechagas)->toDateString());

        if ($gasto->wasChanged('fechagas')) {
            $oldDate = Carbon::parse($gasto->getOriginal('fechagas'))->toDateString();
            $this->scheduleSync($oldDate);
        }
    }

    public function deleted(Gasto $gasto): void
    {
        $this->scheduleSync(Carbon::parse($gasto->fechagas)->toDateString());
    }
}
