<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TareasPendientes extends Notification
{
    use Queueable;
    public $empleado;
    /**
     * Create a new notification instance.
     */
    public function __construct($empleado)
    {
        $this->empleado = $empleado;
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
            'titulo' => 'Nueva Tarea Pendiente',
            'mensaje' => "Hola {$this->empleado->nombreemp}, te asignaron una tarea, revísala y complétala.",
            'url' => route('tareas.index'), // Ruta para ver la recarga
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
