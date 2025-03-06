<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
class Notificaciones extends Component
{
    public $notificaciones;
    public $totalNoLeidas;

    protected $listeners = ['notificacionRecibida' => 'actualizarNotificaciones'];

    public function mount()
    {
        $this->actualizarNotificaciones();
    }

    public function actualizarNotificaciones()
    {
        $user = Auth::user();
        $this->notificaciones = $user->unreadNotifications;
        $this->totalNoLeidas = $this->notificaciones->count();
    }
    public function marcarComoLeida($notificacionId)
    {
        
    }

    public function marcarComoLeidas()
    {
        $user = Auth::user();

        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        $this->actualizarNotificaciones();
    }
    public function render()
    {
        return view('livewire.notificaciones');
    }
}
