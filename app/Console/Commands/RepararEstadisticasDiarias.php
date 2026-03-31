<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Costo;
use App\Models\DailyStatistic;
use App\Models\Gasto;
use App\Models\Venta;
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

    public function __construct()
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
            $this->repairFinancialForDate($date->toDateString());
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

    private function repairFinancialForDate(string $date): void
    {
        $financial = $this->calculateFinancials($date);

        $stat = DailyStatistic::whereDate('date', $date)->first();
        if ($stat) {
            $stat->update($financial);
            return;
        }

        $seed = DailyStatistic::whereDate('date', '<', $date)
            ->orderByDesc('date')
            ->first();

        DailyStatistic::create(array_merge([
            'date' => $date,
            // Preservar logica operativa: no recalcular usuarios en este comando
            'active_users' => (int) ($seed->active_users ?? 0),
            'affected_customers' => (int) ($seed->affected_customers ?? 0),
            'pending_payments' => (int) ($seed->pending_payments ?? 0),
            'danger_accounts' => (int) ($seed->danger_accounts ?? 0),
            'accounts' => (int) ($seed->accounts ?? 0),
            'usuarios_a_cobrar' => (int) ($seed->usuarios_a_cobrar ?? 0),
            'espacios' => (int) ($seed->espacios ?? 0),
            'cliente_mas_facturado' => (string) ($seed->cliente_mas_facturado ?? ''),
            'total_customers' => (int) ($seed->total_customers ?? 0),
        ], $financial));
    }

    private function calculateFinancials(string $date): array
    {
        return [
            'daily_revenue' => (float) Venta::whereDate('fechaven', $date)->sum('totalpagoven'),
            'daily_cost' => (float) Costo::whereDate('fechacos', $date)->sum('montocos'),
            'daily_bill' => (float) Gasto::whereDate('fechagas', $date)->sum('montogas'),
            'daily_sales' => (int) Venta::whereDate('fechaven', $date)->count(),
            'new_customers' => (int) Cliente::whereDate('created_at', $date)->count(),
        ];
    }
}
