<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Define las tareas aquí
        $schedule->command('statistics:daily')->dailyAt('23:59'); // Ejecutar todos los días a la medianoche

        // Tarea para el día 10, 20 y el último día del mes
        $schedule->command('statistics:monthly')
            ->dailyAt('23:50') // Se evalúa diariamente a las 23:50
            ->when(function () {
                $today = now()->day;
                return $today === 10 || $today === 20 || now()->isLastOfMonth();
            });
        // Tarea de prueba
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
