<?php

namespace App\Console;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SaveDailyStatistics;
use App\Console\Commands\SaveMontlyStatistics;
class Kernel extends ConsoleKernel
{
    protected $commands = [
        SaveDailyStatistics::class,
        SaveMontlyStatistics::class,
    ];
    protected function schedule(Schedule $schedule)
    {
        // Define las tareas aquí
        $schedule->command('statistics:daily')->dailyAt('23:59'); // Ejecutar todos los días a la medianoche

        // Tarea para el día 10, 20 y el último día del mes
        $schedule->command('statistics:monthly')
            ->dailyAt('23:59') // Se evalúa diariamente a las 23:50
            ->when(function () {
                $today = now()->day;
                return $today === 10 || $today === 20 || now()->isLastOfMonth();
            });
        // Tarea de prueba

        $schedule->call(function () {
            // Inserta un usuario en la tabla 'users'
            \App\Models\User::create([
                'name' => 'Usuario ' . now()->format('Y-m-d H:i:s'),
                'email' => 'user_' . now()->timestamp . '@example.com',
                'password' => bcrypt('password'), // Contraseña cifrada
            ]);
        })->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
