<?php

namespace App\Notifications;

use App\Models\Soporte;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevoSoporteCliente extends Notification
{
    use Queueable;

    public function __construct(public Soporte $soporte)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $cliente = $this->soporte->cliente;
        $cuenta = $this->soporte->cuenta;
        $servicio = optional(optional(optional($cuenta)->valor)->servicio)->nombreser ?? 'Servicio sin identificar';

        return [
            'titulo' => 'Nuevo soporte de cliente',
            'mensaje' => "{$cliente?->nombrecli} creó un soporte de {$servicio} para la cuenta {$cuenta?->usuariocue}.",
            'url' => route('soportes.index'),
            'idsop' => $this->soporte->idsop,
        ];
    }
}
