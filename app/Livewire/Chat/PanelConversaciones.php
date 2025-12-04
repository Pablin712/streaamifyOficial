<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversacion;
use App\Models\Mensaje;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class PanelConversaciones extends Component
{
    use WithPagination;

    public $conversacionActiva;
    public $mensajes = [];
    public $nuevoMensaje = '';
    public $filtroEstado = 'todas';
    public $busqueda = '';
    public $pollingEnabled = true;
    public $ultimoConteoMensajes = 0;
    public $vistaMobile = 'lista'; // 'lista' o 'chat'

    public function mount()
    {
        // Verificar permiso
        if (!auth()->user() || !auth()->user()->can('chat.ver')) {
            abort(403, 'No tienes permiso para acceder al chat');
        }
    }

    public function render()
    {
        $query = Conversacion::with(['cliente', 'ultimoMensaje', 'ultimoEmpleado'])
            ->orderBy('ultima_actividad', 'desc');

        // Filtro por estado
        if ($this->filtroEstado !== 'todas') {
            $query->where('estado', $this->filtroEstado);
        } else {
            // Por defecto, solo conversaciones no cerradas
            $query->whereIn('estado', ['abierta', 'en_atencion', 'en_espera']);
        }

        // Búsqueda por cliente
        if ($this->busqueda) {
            $query->where(function ($q) {
                $q->whereHas('cliente', function ($subQ) {
                    $subQ->where('nombrecli', 'like', "%{$this->busqueda}%")
                         ->orWhere('telefonocli', 'like', "%{$this->busqueda}%");
                })
                ->orWhere('metadata->session_id', 'like', "%{$this->busqueda}%");
            });
        }

        $conversaciones = $query->paginate(15);

        return view('livewire.chat.panel-conversaciones', [
            'conversaciones' => $conversaciones,
        ]);
    }

    public function seleccionarConversacion($idconv)
    {
        $this->conversacionActiva = Conversacion::with('cliente')->find($idconv);
        $this->mensajes = $this->conversacionActiva->mensajes()->with(['empleado', 'cliente'])->get()->toArray();

        // Marcar como leída
        $this->conversacionActiva->marcarComoLeida();

        // En móvil, cambiar a vista de chat
        $this->vistaMobile = 'chat';

        $this->dispatch('mensajesActualizados');
    }

    public function volverALista()
    {
        $this->vistaMobile = 'lista';
        $this->conversacionActiva = null;
        $this->mensajes = [];
    }

    public function actualizarMensajes()
    {
        // Verificar mensajes nuevos en todas las conversaciones
        $totalMensajesNoLeidos = Conversacion::abiertas()->sum('mensajes_no_leidos');

        if ($totalMensajesNoLeidos > $this->ultimoConteoMensajes) {
            $this->dispatch('nuevoMensajeRecibido', [
                'count' => $totalMensajesNoLeidos
            ]);
        }

        $this->ultimoConteoMensajes = $totalMensajesNoLeidos;

        if ($this->conversacionActiva) {
            $mensajesCollection = collect($this->mensajes);
            $ultimoIdMensaje = $mensajesCollection->last()['idmsg'] ?? 0;

            $nuevosMensajes = $this->conversacionActiva->mensajes()
                ->with(['empleado', 'cliente'])
                ->where('idmsg', '>', $ultimoIdMensaje)
                ->get();

            if ($nuevosMensajes->isNotEmpty()) {
                $this->mensajes = array_merge($this->mensajes, $nuevosMensajes->toArray());
                $this->conversacionActiva->marcarComoLeida();
                $this->dispatch('mensajesActualizados');
            }
        }
    }

    public function enviarMensaje()
    {
        if (empty($this->nuevoMensaje)) {
            return;
        }

        if (!auth()->user()->can('chat.responder')) {
            $this->addError('mensaje', 'No tienes permiso para responder');
            return;
        }

        $mensaje = Mensaje::create([
            'idconv' => $this->conversacionActiva->idconv,
            'tipo_remitente' => 'empleado',
            'idemp' => auth()->id(),
            'contenido' => $this->nuevoMensaje,
            'tipo_contenido' => 'texto',
        ]);

        // Actualizar estado de conversación
        $this->conversacionActiva->cambiarEstado('en_atencion', auth()->id());

        $this->mensajes[] = $mensaje->load('empleado')->toArray();
        $this->nuevoMensaje = '';
        $this->dispatch('mensajesActualizados');
    }

    public function cerrarConversacion()
    {
        if (!auth()->user()->can('chat.cerrar')) {
            $this->addError('mensaje', 'No tienes permiso para cerrar conversaciones');
            return;
        }

        $this->conversacionActiva->cambiarEstado('cerrada', auth()->id());

        // Mensaje del sistema
        Mensaje::create([
            'idconv' => $this->conversacionActiva->idconv,
            'tipo_remitente' => 'sistema',
            'contenido' => 'Conversación cerrada por ' . auth()->user()->nombreemp,
            'tipo_contenido' => 'sistema',
        ]);

        $this->conversacionActiva = null;
        $this->mensajes = [];
    }
}
