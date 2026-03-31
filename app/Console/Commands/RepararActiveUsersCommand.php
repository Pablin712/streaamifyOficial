<?php

namespace App\Console\Commands;

use App\Models\DailyStatistic;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class RepararActiveUsersCommand extends Command
{
    protected $signature = 'estadisticas:reparar-active-users
                            {--start= : Fecha inicio (Y-m-d). Default: 1 de enero del año actual}
                            {--end= : Fecha fin (Y-m-d). Default: ayer}
                            {--bad=460 : Valor considerado danado que se debe reparar}
                            {--max-distance=90 : Maximo de dias para buscar vecinos validos}
                            {--dry-run : Solo mostrar cambios, sin guardar}';

    protected $description = 'Repara active_users usando promedio de fechas vecinas validas (ignorando valores danados)';

    public function handle(): int
    {
        [$start, $end] = $this->resolveRange();
        $badValue = (int) $this->option('bad');
        $maxDistance = max(1, (int) $this->option('max-distance'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info('=== Reparacion de active_users ===');
        $this->line('Rango: ' . $start->toDateString() . ' -> ' . $end->toDateString());
        $this->line('Valor danado: ' . $badValue);
        $this->line('Max distancia vecinos: ' . $maxDistance . ' dias');
        $this->line('Modo: ' . ($dryRun ? 'DRY-RUN' : 'APLICAR'));

        $queryStart = $start->copy()->subDays($maxDistance)->toDateString();
        $queryEnd = $end->copy()->addDays($maxDistance)->toDateString();

        $stats = DailyStatistic::whereBetween('date', [$queryStart, $queryEnd])
            ->orderBy('date')
            ->get(['id', 'date', 'active_users']);

        if ($stats->isEmpty()) {
            $this->warn('No hay registros en daily_statistics para el rango consultado.');
            return self::SUCCESS;
        }

        $byDate = $stats->mapWithKeys(function (DailyStatistic $s) {
            return [$s->date->toDateString() => $s];
        });

        $fixed = 0;
        $skipped = 0;
        $checked = 0;

        $days = $start->diffInDays($end) + 1;
        $bar = $this->output->createProgressBar($days);
        $bar->start();

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $bar->advance();
            $d = $date->toDateString();
            $current = $byDate->get($d);

            if (!$current) {
                continue;
            }

            $checked++;
            if ((int) $current->active_users !== $badValue) {
                continue;
            }

            $prev = $this->findPreviousValid($byDate, $date->copy(), $badValue, $maxDistance);
            $next = $this->findNextValid($byDate, $date->copy(), $badValue, $maxDistance);

            $newValue = $this->estimateValue($prev, $next);
            if ($newValue === null) {
                $skipped++;
                $this->newLine();
                $this->warn("Sin vecinos validos para $d. Se omite.");
                continue;
            }

            $oldValue = (int) $current->active_users;
            if (!$dryRun) {
                $current->active_users = $newValue;
                $current->save();
            }

            $fixed++;
            $this->newLine();
            $this->line(sprintf(
                '%s | %d -> %d | prev=%s next=%s',
                $d,
                $oldValue,
                $newValue,
                $prev !== null ? (string) $prev : 'n/a',
                $next !== null ? (string) $next : 'n/a'
            ));
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Revision completada.');
        $this->line('Fechas revisadas: ' . $checked);
        $this->line('Fechas reparadas: ' . $fixed);
        $this->line('Fechas omitidas: ' . $skipped);

        return self::SUCCESS;
    }

    private function resolveRange(): array
    {
        $startOpt = $this->option('start');
        $endOpt = $this->option('end');

        $start = $startOpt ? Carbon::parse($startOpt)->startOfDay() : Carbon::now()->startOfYear();
        $end = $endOpt ? Carbon::parse($endOpt)->startOfDay() : Carbon::yesterday()->startOfDay();

        if ($start->gt($end)) {
            throw new \InvalidArgumentException('La fecha --start no puede ser mayor que --end.');
        }

        return [$start, $end];
    }

    private function findPreviousValid($byDate, Carbon $baseDate, int $badValue, int $maxDistance): ?int
    {
        for ($i = 1; $i <= $maxDistance; $i++) {
            $d = $baseDate->copy()->subDays($i)->toDateString();
            $row = $byDate->get($d);
            if (!$row) {
                continue;
            }

            $value = (int) $row->active_users;
            if ($value > 0 && $value !== $badValue) {
                return $value;
            }
        }

        return null;
    }

    private function findNextValid($byDate, Carbon $baseDate, int $badValue, int $maxDistance): ?int
    {
        for ($i = 1; $i <= $maxDistance; $i++) {
            $d = $baseDate->copy()->addDays($i)->toDateString();
            $row = $byDate->get($d);
            if (!$row) {
                continue;
            }

            $value = (int) $row->active_users;
            if ($value > 0 && $value !== $badValue) {
                return $value;
            }
        }

        return null;
    }

    private function estimateValue(?int $prev, ?int $next): ?int
    {
        if ($prev !== null && $next !== null) {
            return (int) round(($prev + $next) / 2);
        }

        if ($prev !== null) {
            return $prev;
        }

        if ($next !== null) {
            return $next;
        }

        return null;
    }
}
