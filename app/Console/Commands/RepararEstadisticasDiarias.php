<?php

namespace App\Console\Commands;

use App\Services\DashboardService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class RepararEstadisticasDiarias extends Command
{
    protected $signature = 'estadisticas:reparar
                            {--date= : Fecha unica (Y-m-d)}
                            {--start= : Fecha inicio (Y-m-d)}
                            {--end= : Fecha fin (Y-m-d)}';

    protected $description = 'Recalcula daily_statistics para que coincida con ventas/costos/gastos';

    public function __construct(private DashboardService $dashboardService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        [$start, $end] = $this->resolveRange();

        $this->info('=== Reparacion de daily_statistics ===');
        $this->line('Rango: ' . $start->toDateString() . ' -> ' . $end->toDateString());

        $days = $start->diffInDays($end) + 1;
        $bar = $this->output->createProgressBar($days);
        $bar->start();

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $this->dashboardService->guardar($date->toDateString());
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Reparacion completada.');

        return self::SUCCESS;
    }

    private function resolveRange(): array
    {
        $date = $this->option('date');
        $start = $this->option('start');
        $end = $this->option('end');

        if ($date) {
            $d = Carbon::parse($date)->startOfDay();
            return [$d->copy(), $d->copy()];
        }

        if ($start || $end) {
            if (!$start || !$end) {
                throw new \InvalidArgumentException('Debes enviar --start y --end juntos.');
            }

            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->startOfDay();

            if ($startDate->gt($endDate)) {
                throw new \InvalidArgumentException('La fecha --start no puede ser mayor que --end.');
            }

            return [$startDate, $endDate];
        }

        $today = Carbon::today();
        return [$today->copy()->subDays(30), $today];
    }
}
