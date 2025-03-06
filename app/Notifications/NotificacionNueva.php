<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
class NotificacionNueva extends Notification
{
    use Queueable;
    public $recarga;
    /**
     * Create a new notification instance.
     */
    public function __construct($recarga)
    {
        $this->recarga = $recarga;
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

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'titulo' => 'Nueva Recarga Pendiente',
            'mensaje' => "El cliente {$this->recarga->cliente->nombrecli} ha solicitado una recarga de \${$this->recarga->valor}.",
            'url' => route('empleado.recargas.index', $this->recarga->idrec), // Ruta para ver la recarga
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
