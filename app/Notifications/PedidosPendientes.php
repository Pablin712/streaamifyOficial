<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PedidosPendientes extends Notification
{
    use Queueable;
    public $pedido;

    /**
     * Create a new notification instance.
     */
    public function __construct($pedido)
    {
        $this->pedido = $pedido;
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
    public function toDatabase($notifiable)
    {
        return [
            'titulo' => 'Nuevo Pedido Pendiente',
            'mensaje' => "El cliente {$this->pedido->cliente->nombrecli} ha solicitado un pedido de \${$this->pedido->producto->nombrepro}.",
            'url' => route('empleado.pedidos.index'), // Ruta para ver la recarga
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
