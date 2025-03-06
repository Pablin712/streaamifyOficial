<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComprasRealizadas extends Notification
{
    use Queueable;
    public $compra;
    /**
     * Create a new notification instance.
     */
    public function __construct($compra)
    {
        $this->compra = $compra;   
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
            'titulo' => 'Nueva Compra Realizada',
            'mensaje' => "El cliente {$this->compra->cliente->nombrecli} ha realizado una compra! Revisa lo que compró.",
            'url' => route('ventas'), // Ruta para ver la recarga
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
