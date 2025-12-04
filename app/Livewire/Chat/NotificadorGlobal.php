<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversacion;
use Illuminate\Support\Facades\Auth;

class NotificadorGlobal extends Component
{
    public $ultimoConteoMensajes = 0;
    public $totalNoLeidos = 0;

    public function mount()
    {
        // Solo para empleados con permiso de chat
        if (!auth()->user() || !auth()->user()->can('chat.ver')) {
            return;
        }

        $this->totalNoLeidos = $this->obtenerTotalNoLeidos();
        $this->ultimoConteoMensajes = $this->totalNoLeidos;
    }

    public function verificarNuevosMensajes()
    {
        if (!auth()->user() || !auth()->user()->can('chat.ver')) {
            return;
        }

        $this->totalNoLeidos = $this->obtenerTotalNoLeidos();

        if ($this->totalNoLeidos > $this->ultimoConteoMensajes) {
            $nuevos = $this->totalNoLeidos - $this->ultimoConteoMensajes;

            $this->dispatch('nuevoMensajeGlobal', [
                'count' => $this->totalNoLeidos,
                'nuevos' => $nuevos
            ]);

            $this->ultimoConteoMensajes = $this->totalNoLeidos;
        } elseif ($this->totalNoLeidos < $this->ultimoConteoMensajes) {
            // Si disminuyó (mensajes fueron leídos), actualizar sin notificar
            $this->ultimoConteoMensajes = $this->totalNoLeidos;
        }
    }

    private function obtenerTotalNoLeidos()
    {
        // Consulta directa sin caché para datos en tiempo real
        // La caché se invalida automáticamente en Mensaje::boot()
        return Conversacion::abiertas()->sum('mensajes_no_leidos');
    }

    public function render()
    {
        return view('livewire.chat.notificador-global');
    }
}
