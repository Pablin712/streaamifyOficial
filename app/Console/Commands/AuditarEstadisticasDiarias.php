<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Costo;
use App\Models\DailyStatistic;
use App\Models\Gasto;
use App\Models\Venta;
use App\Services\DashboardService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class AuditarEstadisticasDiarias extends Command
{
    protected $signature = 'estadisticas:auditar
                            {--date= : Fecha unica (Y-m-d)}
                            {--start= : Fecha inicio (Y-m-d)}
                            {--end= : Fecha fin (Y-m-d)}
                            {--fix : Recalcular automaticamente fechas con diferencias}';

    protected $description = 'Audita daily_statistics vs ventas/costos/gastos y opcionalmente corrige diferencias';

    public function __construct(private DashboardService $dashboardService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        [$start, $end] = $this->resolveRange();

        $this->info('=== Auditoria de daily_statistics ===');
        $this->line('Rango: ' . $start->toDateString() . ' -> ' . $end->toDateString());
        $this->line('Timezone app: ' . config('app.timezone'));
        $this->newLine();

        $rows = [];
        $mismatchDates = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $dateStr = $date->toDateString();

            $expectedRevenue = (float) Venta::whereDate('fechaven', $dateStr)->sum('totalpagoven');
            $expectedCost = (float) Costo::whereDate('fechacos', $dateStr)->sum('montocos');
            $expectedBill = (float) Gasto::whereDate('fechagas', $dateStr)->sum('montogas');
            $expectedSales = (int) Venta::whereDate('fechaven', $dateStr)->count();
            $expectedNewCustomers = (int) Cliente::whereDate('created_at', $dateStr)->count();

            $stat = DailyStatistic::whereDate('date', $dateStr)->first();

            $actualRevenue = (float) ($stat->daily_revenue ?? 0);
            $actualCost = (float) ($stat->daily_cost ?? 0);
            $actualBill = (float) ($stat->daily_bill ?? 0);
            $actualSales = (int) ($stat->daily_sales ?? 0);
            $actualNewCustomers = (int) ($stat->new_customers ?? 0);

            $ok = $this->isEqual($expectedRevenue, $actualRevenue)
                && $this->isEqual($expectedCost, $actualCost)
                && $this->isEqual($expectedBill, $actualBill)
                && $expectedSales === $actualSales
                && $expectedNewCustomers === $actualNewCustomers;

            if (!$ok) {
                $mismatchDates[] = $dateStr;
            }

            $rows[] = [
                $dateStr,
                $ok ? 'OK' : 'DIFF',
                number_format($expectedRevenue, 2) . ' / ' . number_format($actualRevenue, 2),
                number_format($expectedCost, 2) . ' / ' . number_format($actualCost, 2),
                number_format($expectedBill, 2) . ' / ' . number_format($actualBill, 2),
                $expectedSales . ' / ' . $actualSales,
                $expectedNewCustomers . ' / ' . $actualNewCustomers,
            ];
        }

        $this->table(
            ['Fecha', 'Estado', 'Ingresos (real/stat)', 'Costos (real/stat)', 'Gastos (real/stat)', 'Ventas (real/stat)', 'Clientes nuevos (real/stat)'],
            $rows
        );

        $this->newLine();
        if (empty($mismatchDates)) {
            $this->info('No se encontraron diferencias. daily_statistics esta alineada.');
            return self::SUCCESS;
        }

        $this->warn('Fechas con diferencias: ' . count($mismatchDates));
        $this->line(implode(', ', $mismatchDates));

        if (!$this->option('fix')) {
            $this->line('Sugerencia: ejecutar de nuevo con --fix para corregir automaticamente.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Corrigiendo fechas con diferencias...');

        $bar = $this->output->createProgressBar(count($mismatchDates));
        $bar->start();

        foreach ($mismatchDates as $dateStr) {
            $this->dashboardService->guardar($dateStr);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Correccion aplicada.');

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
        return [$today->copy()->subDays(7), $today];
    }

    private function isEqual(float $a, float $b): bool
    {
        return abs($a - $b) < 0.005;
    }
}
