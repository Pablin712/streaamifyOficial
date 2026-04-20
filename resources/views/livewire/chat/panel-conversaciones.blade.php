<div class="chat-streamify-shell">
    <div class="chat-panel-container" wire:poll.5s="actualizarMensajes">
        <!-- Lista de conversaciones -->
        <div class="chat-sidebar" data-vista-mobile="{{ $vistaMobile }}">
        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-top">
                <h2 class="chat-title">Chat Streamify</h2>
                <p class="chat-subtitle">Panel de Conversaciones</p>
            </div>

            <div class="chat-header-kpi">
                <span class="chat-kpi-chip">{{ $conversaciones->total() }} conversaciones</span>
                <span class="chat-kpi-chip chat-kpi-unread">{{ $conversaciones->sum('mensajes_no_leidos') }} no leídas</span>
            </div>
        </div>

        <!-- Filtros -->
        <div class="chat-filters">
            <input
                wire:model.live="busqueda"
                type="text"
                placeholder="Buscar cliente..."
                class="chat-search-input"
            />

            <div class="chat-filter-buttons">
                <button
                    wire:click="$set('filtroEstado', 'todas')"
                    class="chat-filter-btn {{ $filtroEstado === 'todas' ? 'active' : '' }}"
                >
                    Todas
                </button>
                <button
                    wire:click="$set('filtroEstado', 'no_leidas')"
                    class="chat-filter-btn {{ $filtroEstado === 'no_leidas' ? 'active' : '' }}"
                >
                    No leídas
                </button>
            </div>
        </div>

        <!-- Lista -->
        <div class="chat-conversations-list">
            @forelse($conversaciones as $conv)
                <div
                    wire:click="seleccionarConversacion({{ $conv->idconv }})"
                    class="chat-conversation-item {{ $conversacionActiva?->idconv == $conv->idconv ? 'active' : '' }}"
                >
                    <div class="chat-conversation-body">
                        <div class="chat-conversation-avatar">
                            {{ mb_strtoupper(mb_substr($this->nombreConversacion($conv), 0, 1)) }}
                        </div>

                        <div class="chat-conversation-content">
                            <div class="chat-conversation-row">
                                <div class="chat-conversation-title-wrap">
                                    <span class="canal-dot {{ $this->canalClaseColor($conv) }}"></span>
                                    <span class="chat-conversation-name">
                                        {{ $this->nombreConversacion($conv) }}
                                    </span>
                                </div>

                                <span class="chat-timestamp">
                                    {{ $conv->ultima_actividad->diffForHumans() }}
                                </span>
                            </div>

                            <p class="chat-conversation-preview">
                                {{ $this->vistaPreviaMensaje($conv) }}
                            </p>

                            <div class="chat-conversation-meta">
                                <div class="chat-conversation-tags">
                                    <span class="chat-badge secondary">
                                        {{ strtoupper($conv->canal_principal ?? 'CHAT') }}
                                    </span>

                                    @if($conv->requiere_humano)
                                        <span class="chat-badge unread">
                                            Urgente
                                        </span>
                                    @endif
                                </div>

                                @if($conv->mensajes_no_leidos > 0)
                                    <span class="chat-badge unread chat-unread-pill">
                                        {{ $conv->mensajes_no_leidos }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="chat-empty-state">
                    <div class="chat-empty-icon">💬</div>
                    <p class="chat-empty-text">No hay conversaciones</p>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        @if($conversaciones->hasPages())
            <div class="chat-pagination">
                {{ $conversaciones->links('vendor.pagination.chat-numbers-only') }}
            </div>
        @endif
    </div>

    <!-- Panel de mensajes -->
    <div class="chat-main" data-vista-mobile="{{ $vistaMobile }}">
        @if($conversacionActiva)
            <!-- Header del chat -->
            <div class="chat-main-header">
                <div class="chat-main-header-content">
                    <!-- Botón volver (solo móvil) -->
                    <button wire:click="volverALista" class="chat-back-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <div class="chat-main-heading">
                        <div class="chat-main-title-row">
                            <span class="canal-dot {{ $this->canalClaseColor($conversacionActiva) }}"></span>
                            <h3 class="chat-main-title">
                                {{ $this->nombreConversacion($conversacionActiva) }}
                            </h3>
                        </div>
                        <p class="chat-main-subtitle">
                            {{ $this->subtituloConversacion($conversacionActiva) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mensajes -->
            <div class="chat-messages-container" id="messages-container">
                @foreach($mensajes as $msg)
                    @php
                        $msgData = is_array($msg) ? $msg : $msg->toArray();
                        $tipoRemitente = $msgData['tipo_remitente'] ?? 'sistema';
                        $contenido = $msgData['contenido'] ?? '';
                        $createdAt = isset($msgData['created_at']) ? \Carbon\Carbon::parse($msgData['created_at']) : now();

                        // Determinar nombre del remitente (solo para empleados y sistema)
                        $nombreRemitente = match($tipoRemitente) {
                            'empleado' => $msgData['empleado']['nombreemp'] ?? 'Soporte',
                            'ia' => 'Asistente Virtual',
                            'sistema' => 'Sistema',
                            default => null
                        };
                    @endphp
                    <div class="chat-message {{ $tipoRemitente }}">
                        <div class="chat-message-bubble">
                            @if($nombreRemitente)
                                <p class="chat-message-sender">{{ $nombreRemitente }}</p>
                            @endif

                            @if(($msgData['tipo_contenido'] ?? 'texto') === 'imagen' && !empty($msgData['archivo_url']))
                                <div class="chat-media-wrapper">
                                    <img src="{{ $msgData['archivo_url'] }}" alt="Imagen" class="chat-media-image">
                                </div>
                            @elseif(($msgData['tipo_contenido'] ?? 'texto') === 'audio' && !empty($msgData['archivo_url']))
                                <div class="chat-media-wrapper">
                                    <audio controls class="chat-media-audio">
                                        <source src="{{ $msgData['archivo_url'] }}">
                                        Tu navegador no soporta audio.
                                    </audio>
                                </div>
                            @elseif(($msgData['tipo_contenido'] ?? 'texto') === 'video' && !empty($msgData['archivo_url']))
                                <div class="chat-media-wrapper">
                                    <video controls class="chat-media-video">
                                        <source src="{{ $msgData['archivo_url'] }}">
                                        Tu navegador no soporta video.
                                    </video>
                                </div>
                            @elseif(in_array(($msgData['tipo_contenido'] ?? 'texto'), ['archivo', 'documento']) && !empty($msgData['archivo_url']))
                                <div class="chat-media-wrapper">
                                    <a href="{{ $msgData['archivo_url'] }}" target="_blank" class="chat-media-file-link" rel="noopener noreferrer">
                                        Abrir archivo adjunto
                                    </a>
                                </div>
                            @endif

                            @if($contenido !== '')
                                <p class="chat-message-content">{{ $contenido }}</p>
                            @endif
                            <p class="chat-message-time">{{ $createdAt->format('H:i') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input -->
            <div class="chat-input-area">
                <form wire:submit.prevent="enviarMensaje" class="chat-input-form">
                    <input
                        wire:model="nuevoMensaje"
                        type="text"
                        placeholder="Escribe un mensaje..."
                        class="chat-input-field"
                    />
                    <button type="submit" class="chat-send-btn">
                        <span>Enviar</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 2L11 13" />
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" />
                        </svg>
                    </button>
                </form>

                @error('mensaje')
                    <p style="color: var(--danger-color); font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div class="chat-empty-state">
                <div class="chat-empty-icon">💬</div>
                <p class="chat-empty-text">Selecciona una conversación para comenzar</p>
            </div>
        @endif
    </div>

<!-- Notificación flotante -->
<div id="chat-notification" class="chat-notification-bubble" style="display: none;">
    <div class="chat-notification-icon">💬</div>
    <div class="chat-notification-content">
        <div class="chat-notification-title">Nuevo mensaje</div>
        <div class="chat-notification-message" id="notification-message"></div>
    </div>
    <button class="chat-notification-close" onclick="cerrarNotificacion()">×</button>
</div>

<script>
    let panelNotificationTimeout; // Renombrado para evitar conflicto con notificador-global

    // Auto-scroll al final cuando llega un nuevo mensaje
    document.addEventListener('livewire:init', () => {
        Livewire.on('mensajesActualizados', () => {
            const container = document.getElementById('messages-container');
            if (container) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            }
        });

        // Escuchar nuevos mensajes
        Livewire.on('nuevoMensajeRecibido', (event) => {
            mostrarNotificacion(event.count);
            reproducirSonido();
        });
    });

    // Auto-scroll inicial
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });

    function mostrarNotificacion(count) {
        const notif = document.getElementById('chat-notification');
        const message = document.getElementById('notification-message');

        if (count === 1) {
            message.textContent = 'Tienes 1 mensaje nuevo';
        } else {
            message.textContent = `Tienes ${count} mensajes nuevos`;
        }

        notif.style.display = 'flex';

        // Auto-ocultar después de 5 segundos
        clearTimeout(panelNotificationTimeout);
        panelNotificationTimeout = setTimeout(() => {
            cerrarNotificacion();
        }, 5000);

        // Permitir clic para ir a los mensajes
        notif.onclick = (e) => {
            if (!e.target.classList.contains('chat-notification-close')) {
                cerrarNotificacion();
                // Aquí podrías agregar lógica para abrir la primera conversación no leída
            }
        };
    }

    function cerrarNotificacion() {
        const notif = document.getElementById('chat-notification');
        notif.style.display = 'none';
        clearTimeout(panelNotificationTimeout);
    }

    function reproducirSonido() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();

            // Primer tono
            const oscillator1 = audioContext.createOscillator();
            const gainNode1 = audioContext.createGain();

            oscillator1.connect(gainNode1);
            gainNode1.connect(audioContext.destination);

            oscillator1.frequency.value = 800;
            oscillator1.type = 'sine';

            gainNode1.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode1.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

            oscillator1.start(audioContext.currentTime);
            oscillator1.stop(audioContext.currentTime + 0.15);

            // Segundo tono (más agudo)
            const oscillator2 = audioContext.createOscillator();
            const gainNode2 = audioContext.createGain();

            oscillator2.connect(gainNode2);
            gainNode2.connect(audioContext.destination);

            oscillator2.frequency.value = 1000;
            oscillator2.type = 'sine';

            gainNode2.gain.setValueAtTime(0.3, audioContext.currentTime + 0.1);
            gainNode2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

            oscillator2.start(audioContext.currentTime + 0.1);
            oscillator2.stop(audioContext.currentTime + 0.3);
        } catch (err) {
            console.log('Error al generar sonido:', err);
        }
    }

    // Limpiar input después de enviar mensaje
    document.addEventListener('livewire:init', () => {
        Livewire.on('mensaje-enviado', () => {
            const input = document.querySelector('.chat-input-field');
            if (input) {
                input.value = '';
                input.focus();
            }
        });
    });
</script>
</div>
