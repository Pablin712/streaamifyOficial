<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TimezoneCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timezone:check {--connection= : Nombre de la conexion DB (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica consistencia de zona horaria entre Laravel y la base de datos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = $this->option('connection') ?: config('database.default');
        $appTimezone = (string) config('app.timezone');
        $dbTimezoneConfig = (string) config("database.connections.{$connection}.timezone", 'N/A');

        $this->info('=== Verificacion de zona horaria ===');
        $this->line('Conexion DB: ' . $connection);
        $this->line('APP_TIMEZONE: ' . $appTimezone);
        $this->line('DB_TIMEZONE (config): ' . $dbTimezoneConfig);
        $this->line('PHP date_default_timezone_get(): ' . date_default_timezone_get());
        $this->newLine();

        try {
            $db = DB::connection($connection);
            $row = $db->selectOne("SELECT NOW() AS db_now, @@session.time_zone AS session_tz, @@system_time_zone AS system_tz");

            $appNow = Carbon::now($appTimezone);
            $dbNow = Carbon::parse($row->db_now, $appTimezone);

            $wallClockDiffSeconds = abs($appNow->diffInSeconds($dbNow, false));
            $ok = $wallClockDiffSeconds <= 60;

            $this->table(
                ['Metrica', 'Valor'],
                [
                    ['Laravel now (' . $appTimezone . ')', $appNow->format('Y-m-d H:i:s')],
                    ['DB NOW() (sesion actual)', (string) $row->db_now],
                    ['DB @@session.time_zone', (string) $row->session_tz],
                    ['DB @@system_time_zone', (string) $row->system_tz],
                    ['Diferencia (segundos)', (string) $wallClockDiffSeconds],
                ]
            );

            $this->newLine();
            if ($ok) {
                $this->info('OK: Laravel y DB estan alineados para operar con hora local.');
                return self::SUCCESS;
            }

            $this->error('NO OK: existe desfase de hora entre Laravel y DB en esta conexion.');
            $this->line('Sugerencia: revisar APP_TIMEZONE, DB_TIMEZONE y limpiar cache de configuracion.');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('No se pudo verificar la timezone: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
