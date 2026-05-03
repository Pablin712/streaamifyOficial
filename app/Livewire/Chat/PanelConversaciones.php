<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\ChatMensajeCanal;
use App\Models\Conversacion;
use App\Models\Mensaje;
use App\Services\Chat\WhatsAppOutboundService;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
        if (Gate::denies('chat.ver')) {
            abort(403, 'No tienes permiso para acceder al chat');
        }
    }

    public function render()
    {
        $query = Conversacion::with(['cliente', 'contactoCanal', 'ultimoMensaje', 'ultimoEmpleado'])
            ->orderBy('ultima_actividad', 'desc');

        // Filtro por no leídos (vista tipo WhatsApp, sin abiertos/cerrados)
        if ($this->filtroEstado === 'no_leidas') {
            $query->where('mensajes_no_leidos', '>', 0);
        }

        // Búsqueda por cliente y por número de canal
        if ($this->busqueda) {
            $query->where(function ($q) {
                $q->whereHas('cliente', function ($subQ) {
                    $subQ->where('nombrecli', 'like', "%{$this->busqueda}%")
                         ->orWhere('telefonocli', 'like', "%{$this->busqueda}%");
                })
                ->orWhereHas('contactoCanal', function ($subQ) {
                    $subQ->where('canal_user_id', 'like', "%{$this->busqueda}%")
                        ->orWhere('telefono_normalizado', 'like', "%{$this->busqueda}%")
                        ->orWhere('nombre_canal', 'like', "%{$this->busqueda}%");
                });
            });
        }

        $conversaciones = $query->paginate(15);

        return view('livewire.chat.panel-conversaciones', [
            'conversaciones' => $conversaciones,
        ]);
    }

    public function seleccionarConversacion($idconv)
    {
        $this->conversacionActiva = Conversacion::with(['cliente', 'contactoCanal'])->find($idconv);
        $this->mensajes = $this->conversacionActiva->mensajes()->with(['empleado', 'cliente'])->get()->toArray();

        // Marcar como leída
        $this->conversacionActiva->marcarComoLeida();

        // En móvil, cambiar a vista de chat
        $this->vistaMobile = 'chat';

        $this->dispatch('mensajesActualizados');
        $this->dispatch('mensaje-enviado');
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
        $totalMensajesNoLeidos = Conversacion::query()->sum('mensajes_no_leidos');

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
        $contenido = trim((string) $this->nuevoMensaje);

        if ($contenido === '') {
            return;
        }

        if (!Gate::allows('chat.responder')) {
            $this->addError('mensaje', 'No tienes permiso para responder');
            return;
        }

        $empleado = Auth::guard('empleado')->user() ?? Auth::user();
        $empleadoId = $empleado?->idemp;

        $mensaje = Mensaje::create([
            'idconv' => $this->conversacionActiva->idconv,
            'tipo_remitente' => 'empleado',
            'idemp' => $empleadoId,
            'contenido' => $contenido,
            'tipo_contenido' => 'texto',
            'leido' => true,
        ]);

        $canalMensaje = null;
        $dispatchQueued = false;

        if ($this->conversacionActiva->canal_principal === 'whatsapp' && $this->conversacionActiva->canal_contacto_id) {
            $canalMensaje = ChatMensajeCanal::create([
                'idmsg' => $mensaje->idmsg,
                'idconv' => $this->conversacionActiva->idconv,
                'contacto_canal_id' => $this->conversacionActiva->canal_contacto_id,
                'canal' => 'whatsapp',
                'direccion' => 'outbound',
                'external_status' => 'queued',
                'payload' => [
                    'origen' => 'panel-empleado',
                    'dispatch' => [
                        'queued_at' => now()->toIso8601String(),
                    ],
                ],
            ]);

            $metadata = $this->conversacionActiva->metadata ?? [];
            $contactMetadata = $this->conversacionActiva->contactoCanal?->metadata ?? [];

            $instance = data_get($metadata, 'instance') ?? data_get($contactMetadata, 'instance');
            $apiKey = data_get($metadata, 'apikey') ?? data_get($contactMetadata, 'apikey');
            $serverUrl = data_get($metadata, 'server_url') ?? data_get($contactMetadata, 'server_url');

            /** @var WhatsAppOutboundService $outbound */
            $outbound = app(WhatsAppOutboundService::class);
            $whatsappChannel = null;

            if (!$apiKey || !$serverUrl) {
                $channelConfigId = data_get($metadata, 'whatsapp_channel_id') ?? data_get($contactMetadata, 'whatsapp_channel_id');

                if ($channelConfigId) {
                    $whatsappChannel = \App\Models\ChatWhatsappChannel::query()->availableForOutbound()->find($channelConfigId);
                }

                if (!$whatsappChannel && $instance) {
                    $whatsappChannel = $outbound->resolveChannelByInstance($instance);
                }

                if ($whatsappChannel) {
                    $instance = $instance ?: $whatsappChannel->instance_name;
                    $apiKey = $apiKey ?: $whatsappChannel->api_key;
                    $serverUrl = $serverUrl ?: $whatsappChannel->server_url;
                }
            }

            $destino = (string) ($this->conversacionActiva->contactoCanal?->canal_user_id ?? '');

            app()->terminating(function () use ($outbound, $canalMensaje, $destino, $contenido, $instance, $apiKey, $serverUrl) {
                if (!$instance || !$apiKey) {
                    $canalMensaje->update([
                        'external_status' => 'failed',
                        'payload' => array_merge($canalMensaje->payload ?? [], [
                            'dispatch' => [
                                'ok' => false,
                                'status' => 0,
                                'error' => 'Faltan credenciales de instancia o apiKey para este contacto.',
                            ],
                        ]),
                    ]);

                    return;
                }

                $dispatch = $outbound->sendText(
                    $destino,
                    $contenido,
                    $instance,
                    $apiKey,
                    $serverUrl,
                    [
                        'tipo_contenido' => 'texto',
                    ]
                );

                $canalMensaje->update([
                    'external_status' => $dispatch['ok'] ? 'sent' : 'failed',
                    'external_message_id' => $dispatch['external_message_id'] ?? null,
                    'payload' => array_merge($canalMensaje->payload ?? [], [
                        'dispatch' => [
                            'ok' => $dispatch['ok'],
                            'status' => $dispatch['status'] ?? null,
                            'error' => $dispatch['error'] ?? null,
                        ],
                    ]),
                ]);
            });

            $dispatchQueued = true;
        }

        // Actualizar estado de conversación
        $this->conversacionActiva->cambiarEstado('en_atencion', $empleadoId);

        if ($canalMensaje && !$dispatchQueued) {
            $this->addError('mensaje', 'El mensaje se guardó, pero el envio WhatsApp no se pudo programar.');
        }

        $this->mensajes[] = $mensaje->load('empleado')->toArray();

        // Limpiar el input
        $this->reset('nuevoMensaje');

        $this->dispatch('mensajesActualizados');
        $this->dispatch('mensaje-enviado');
    }

    public function nombreConversacion(Conversacion $conversacion): string
    {
        if ($conversacion->cliente?->nombrecli) {
            return $conversacion->cliente->nombrecli;
        }

        return $conversacion->contactoCanal?->telefono_normalizado
            ?: $conversacion->contactoCanal?->canal_user_id
            ?: 'Sin número';
    }

    public function subtituloConversacion(Conversacion $conversacion): string
    {
        if ($conversacion->cliente?->telefonocli) {
            return $conversacion->cliente->telefonocli;
        }

        return $conversacion->contactoCanal?->telefono_normalizado
            ?: $conversacion->contactoCanal?->canal_user_id
            ?: 'Contacto sin teléfono';
    }

    public function canalClaseColor(Conversacion $conversacion): string
    {
        if ($conversacion->canal_principal !== 'whatsapp') {
            return 'canal-dot-default';
        }

        $declaredColor = data_get($conversacion->metadata, 'whatsapp_color')
            ?? data_get($conversacion->contactoCanal?->metadata, 'whatsapp_color');

        if ($declaredColor === 'verde') {
            return 'canal-dot-green';
        }

        if ($declaredColor === 'azul') {
            return 'canal-dot-blue';
        }

        $instance = strtolower((string) (
            data_get($conversacion->metadata, 'instance')
            ?? data_get($conversacion->contactoCanal?->metadata, 'instance')
            ?? ''
        ));

        // Regla operativa del proyecto: bot-pagos = verde, cualquier otra instancia = azul.
        if ($instance !== '') {
            return $instance === 'bot-pagos' ? 'canal-dot-green' : 'canal-dot-blue';
        }

        return 'canal-dot-green';
    }

    public function vistaPreviaMensaje(Conversacion $conversacion): string
    {
        $mensaje = $conversacion->ultimoMensaje;

        if (!$mensaje) {
            return 'Sin mensajes';
        }

        $contenido = trim((string) $mensaje->contenido);
        if ($contenido !== '') {
            return $contenido;
        }

        return match ($mensaje->tipo_contenido) {
            'imagen' => '📷 Imagen',
            'audio' => '🎙️ Audio',
            'video' => '🎬 Video',
            'archivo', 'documento' => '📎 Archivo',
            default => 'Mensaje',
        };
    }

    public function cerrarConversacion()
    {
        if (Gate::denies('chat.cerrar')) {
            $this->addError('mensaje', 'No tienes permiso para cerrar conversaciones');
            return;
        }

        $this->conversacionActiva->cambiarEstado('cerrada', Auth::id());

        // Mensaje del sistema
        Mensaje::create([
            'idconv' => $this->conversacionActiva->idconv,
            'tipo_remitente' => 'sistema',
            'contenido' => 'Conversación cerrada por ' . Auth::user()->nombreemp,
            'tipo_contenido' => 'sistema',
        ]);

        $this->conversacionActiva = null;
        $this->mensajes = [];
    }
}
