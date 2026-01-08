<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramAuthService;

class LimpiarTelegramSesionesExpiradas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:clean-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia las sesiones de autenticación de Telegram expiradas (más de 10 minutos sin actividad)';

    /**
     * Execute the console command.
     */
    public function handle(TelegramAuthService $authService)
    {
        $this->info('🧹 Limpiando sesiones expiradas de Telegram...');

        $eliminadas = $authService->limpiarSesionesExpiradas();

        if ($eliminadas > 0) {
            $this->info("✅ Se eliminaron {$eliminadas} sesiones expiradas.");
        } else {
            $this->info('✅ No hay sesiones expiradas para eliminar.');
        }

        return Command::SUCCESS;
    }
}
