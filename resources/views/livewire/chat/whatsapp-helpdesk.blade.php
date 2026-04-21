<div class="wa-helpdesk" wire:poll.3s="refreshChat">
    <style>
        .wa-helpdesk {
            --wa-bg: #f4f6f8;
            --wa-panel: #ffffff;
            --wa-border: #d9e1e8;
            --wa-text: #1f2933;
            --wa-muted: #65758b;
            --wa-accent: #0f8b8d;
            --wa-accent-strong: #0b6264;
            --wa-green: #21a67a;
            --wa-danger: #c2413c;
            --wa-warning: #b7791f;
            height: calc(100vh - 74px);
            min-height: 640px;
            display: grid;
            grid-template-columns: minmax(300px, 360px) minmax(420px, 1fr) minmax(280px, 340px);
            background: var(--wa-bg);
            color: var(--wa-text);
            border-top: 1px solid var(--wa-border);
        }
        .wa-column {
            min-height: 0;
            background: var(--wa-panel);
            border-right: 1px solid var(--wa-border);
            display: flex;
            flex-direction: column;
        }
        .wa-right {
            border-right: 0;
            border-left: 1px solid var(--wa-border);
        }
        .wa-toolbar {
            padding: 14px;
            border-bottom: 1px solid var(--wa-border);
            display: grid;
            gap: 10px;
        }
        .wa-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }
        .wa-search,
        .wa-textarea,
        .wa-select {
            width: 100%;
            border: 1px solid var(--wa-border);
            border-radius: 6px;
            padding: 9px 10px;
            color: var(--wa-text);
            background: #fff;
        }
        .wa-filters {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .wa-filter,
        .wa-icon-btn,
        .wa-action,
        .wa-send {
            border: 1px solid var(--wa-border);
            border-radius: 6px;
            background: #fff;
            color: var(--wa-text);
            padding: 8px 10px;
            cursor: pointer;
            font-size: 13px;
            line-height: 1;
        }
        .wa-filter.active,
        .wa-send {
            background: var(--wa-accent);
            border-color: var(--wa-accent);
            color: #fff;
        }
        .wa-list {
            overflow: auto;
            min-height: 0;
        }
        .wa-item {
            width: 100%;
            text-align: left;
            border: 0;
            border-bottom: 1px solid var(--wa-border);
            background: #fff;
            padding: 12px 14px;
            cursor: pointer;
        }
        .wa-item.active,
        .wa-item:hover {
            background: #eef8f7;
        }
        .wa-item-row,
        .wa-chat-header-row,
        .wa-meta-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
        }
        .wa-name {
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .wa-number,
        .wa-preview,
        .wa-small {
            color: var(--wa-muted);
            font-size: 12px;
        }
        .wa-preview {
            margin-top: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .wa-badge {
            display: inline-flex;
            align-items: center;
            min-height: 20px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #edf2f7;
            color: #344054;
            font-size: 11px;
            font-weight: 700;
        }
        .wa-badge.green { background: #dcfce7; color: #166534; }
        .wa-badge.red { background: #fee2e2; color: #991b1b; }
        .wa-badge.blue { background: #dbeafe; color: #1d4ed8; }
        .wa-chat {
            min-height: 0;
            display: flex;
            flex-direction: column;
            background: #edf1f5;
        }
        .wa-chat-header,
        .wa-composer,
        .wa-client-card {
            background: #fff;
            border-bottom: 1px solid var(--wa-border);
            padding: 14px;
        }
        .wa-chat-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }
        .wa-chat-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .wa-messages {
            flex: 1;
            overflow: auto;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .wa-message {
            max-width: 72%;
            display: grid;
            gap: 4px;
        }
        .wa-message.cliente { align-self: flex-start; }
        .wa-message.empleado { align-self: flex-end; }
        .wa-message.sistema {
            align-self: center;
            max-width: 88%;
        }
        .wa-bubble {
            border-radius: 8px;
            padding: 9px 11px;
            background: #fff;
            box-shadow: 0 1px 1px rgba(15, 23, 42, 0.08);
        }
        .wa-message.empleado .wa-bubble {
            background: #dcf8e7;
        }
        .wa-message.sistema .wa-bubble {
            background: #e5e7eb;
            color: #4b5563;
            text-align: center;
            font-size: 12px;
        }
        .wa-media {
            max-width: 260px;
            border-radius: 6px;
            display: block;
        }
        .wa-time {
            color: var(--wa-muted);
            font-size: 11px;
            text-align: right;
        }
        .wa-date {
            align-self: center;
            background: #dbe3ea;
            color: #3f4d5f;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
        }
        .wa-composer {
            border-top: 1px solid var(--wa-border);
            display: grid;
            gap: 10px;
        }
        .wa-compose-row {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        .wa-textarea {
            min-height: 44px;
            max-height: 110px;
            resize: vertical;
        }
        .wa-file-input {
            display: none;
        }
        .wa-panel-scroll {
            overflow: auto;
            min-height: 0;
            padding: 14px;
            display: grid;
            gap: 12px;
        }
        .wa-card {
            border: 1px solid var(--wa-border);
            border-radius: 8px;
            background: #fff;
            padding: 12px;
            display: grid;
            gap: 8px;
        }
        .wa-empty {
            margin: auto;
            text-align: center;
            color: var(--wa-muted);
        }
        .wa-back {
            display: none;
        }
        @media (max-width: 1060px) {
            .wa-helpdesk {
                grid-template-columns: 320px 1fr;
            }
            .wa-right {
                display: none;
            }
        }
        @media (max-width: 760px) {
            .wa-helpdesk {
                height: calc(100vh - 58px);
                min-height: 0;
                grid-template-columns: 1fr;
            }
            .wa-column:first-child {
                display: {{ $mobilePane === 'list' ? 'flex' : 'none' }};
            }
            .wa-chat {
                display: {{ $mobilePane === 'chat' ? 'flex' : 'none' }};
            }
            .wa-back {
                display: inline-flex;
            }
        }
    </style>

    <aside class="wa-column">
         <div class="wa-toolbar">
             <div class="wa-item-row">
                 <h1 class="wa-title">WhatsApp</h1>
                 <div style="display: flex; gap: 8px; align-items: center;">
                     <span class="wa-badge green">helpdesk</span>
                     <button wire:click="$toggle('showSettingsModal')" class="wa-icon-btn" title="Configuracion">⚙️</button>
                 </div>
             </div>

            <input wire:model.live.debounce.300ms="search" class="wa-search" type="search" placeholder="Buscar por nombre o numero">

            <div class="wa-filters">
                @foreach([
                    'nuevas' => 'Nuevas',
                    'no_leidas' => 'No leidas',
                    'asignadas_mi' => 'Mias',
                    'abiertas' => 'Abiertas',
                    'cerradas' => 'Cerradas',
                ] as $key => $label)
                    <button wire:click="$set('filter', '{{ $key }}')" class="wa-filter {{ $filter === $key ? 'active' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="wa-list">
            @forelse($conversations as $conversation)
                @php
                    $displayName = $conversation->cliente?->nombrecli
                        ?: $conversation->contactoCanal?->nombre_canal
                        ?: $conversation->contactoCanal?->telefono_normalizado
                        ?: $conversation->contactoCanal?->canal_user_id
                        ?: 'Contacto';
                    $number = $conversation->contactoCanal?->telefono_normalizado
                        ?: $conversation->contactoCanal?->canal_user_id
                        ?: $conversation->cliente?->telefonocli
                        ?: 'Sin numero';
                    $lastMessage = $conversation->ultimoMensaje;
                    $unread = (int) ($conversation->unread_count ?: $conversation->mensajes_no_leidos);
                @endphp
                <button wire:click="selectConversation({{ $conversation->idconv }})" class="wa-item {{ $activeConversationId === $conversation->idconv ? 'active' : '' }}">
                    <div class="wa-item-row">
                        <span class="wa-name">{{ $displayName }}</span>
                        <span class="wa-small">{{ optional($conversation->last_message_at ?: $conversation->ultima_actividad)->format('H:i') }}</span>
                    </div>
                    <div class="wa-number">{{ $number }}</div>
                    <div class="wa-preview">
                        {{ $lastMessage?->contenido ?: match($lastMessage?->tipo_contenido) {
                            'imagen' => '[imagen]',
                            'audio' => '[audio]',
                            'documento', 'archivo' => '[documento]',
                            default => 'Sin mensajes',
                        } }}
                    </div>
                    <div class="wa-meta-row" style="margin-top: 8px;">
                        <span class="wa-badge green">WhatsApp</span>
                        <span class="wa-small">{{ $conversation->operadorAsignado?->nombreemp ?: 'Sin operador' }}</span>
                        @if($unread > 0)
                            <span class="wa-badge red">{{ $unread }}</span>
                        @endif
                    </div>
                </button>
            @empty
                <div class="wa-empty">No hay conversaciones.</div>
            @endforelse
        </div>

         @if($conversations->hasPages())
             <div class="wa-toolbar">
                 {{ $conversations->links() }}
             </div>
         @endif

         <!-- Boton Configuracion -->
         <div class="wa-toolbar" style="border-top: 1px solid var(--wa-border);">
             <button wire:click="$toggle('showSettingsModal')" class="wa-action" style="width: 100%;">⚙️ Configuracion Chat</button>
         </div>
     </aside>

    <main class="wa-chat">
        @if($activeConversation)
            @php
                $activeName = $activeConversation->cliente?->nombrecli
                    ?: $activeConversation->contactoCanal?->nombre_canal
                    ?: $activeConversation->contactoCanal?->telefono_normalizado
                    ?: 'Contacto';
                $activeNumber = $activeConversation->contactoCanal?->telefono_normalizado
                    ?: $activeConversation->contactoCanal?->canal_user_id
                    ?: $activeConversation->cliente?->telefonocli
                    ?: 'Sin numero';
                $typingOperator = $activeConversation->operadorEscribiendo;
                $isTyping = $typingOperator
                    && $activeConversation->operator_typing_at
                    && $activeConversation->operator_typing_at->gt(now()->subSeconds(8))
                    && $typingOperator->idemp !== auth()->user()?->idemp;
            @endphp

            <header class="wa-chat-header">
                <div class="wa-chat-header-row">
                    <div>
                        <button wire:click="backToList" class="wa-icon-btn wa-back" type="button">←</button>
                        <h2 class="wa-chat-title">{{ $activeName }}</h2>
                        <div class="wa-small">{{ $activeNumber }}</div>
                    </div>
                    <div class="wa-chat-actions">
                        <span class="wa-badge blue">{{ $activeConversation->estado }}</span>
                        <span class="wa-badge">{{ $activeConversation->operadorAsignado?->nombreemp ?: 'Sin operador' }}</span>
                    </div>
                </div>
                <div class="wa-meta-row" style="margin-top: 10px;">
                    <div class="wa-filters">
                        @foreach((array) data_get($activeConversation->metadata, 'tags', []) as $tag)
                            <span class="wa-badge">{{ $tag }}</span>
                        @endforeach
                    </div>
                    <div class="wa-chat-actions">
                        <button wire:click="takeConversation" class="wa-action" type="button">Tomar</button>
                        @can('chat.supervisor')
                            <select wire:change="assignTo($event.target.value)" class="wa-select" style="width: 160px;">
                                <option value="">Asignar</option>
                                @foreach($operators as $operator)
                                    <option value="{{ $operator->idemp }}">{{ $operator->nombreemp }}</option>
                                @endforeach
                            </select>
                        @endcan
                        @if(in_array($activeConversation->estado, ['cerrado', 'cerrada'], true))
                            <button wire:click="reopenConversation" class="wa-action" type="button">Reabrir</button>
                        @else
                            <button wire:click="closeConversation" class="wa-action" type="button">Cerrar</button>
                        @endif
                    </div>
                </div>
                @if($isTyping)
                    <div class="wa-small" style="margin-top: 8px;">{{ $typingOperator->nombreemp }} esta escribiendo</div>
                @elseif($activeConversation->operadorAsignado && $activeConversation->operadorAsignado->idemp !== auth()->user()?->idemp)
                    <div class="wa-small" style="margin-top: 8px;">Atendido por {{ $activeConversation->operadorAsignado->nombreemp }}</div>
                @endif
            </header>

            <section class="wa-messages" id="wa-messages">
                @php $lastDate = null; @endphp
                @foreach($messages as $message)
                    @php
                        $dateKey = optional($message->created_at)->format('Y-m-d');
                        $type = $message->tipo ?: $message->tipo_contenido;
                        $mediaUrl = $message->media_url ?: $message->archivo_url;
                    @endphp
                    @if($dateKey !== $lastDate)
                        <div class="wa-date">{{ optional($message->created_at)->format('d/m/Y') }}</div>
                        @php $lastDate = $dateKey; @endphp
                    @endif
                    <article class="wa-message {{ $message->tipo_remitente }}">
                        <div class="wa-bubble">
                            @if($type === 'imagen' && $mediaUrl)
                                <img src="{{ $mediaUrl }}" class="wa-media" alt="Imagen recibida">
                            @elseif($type === 'audio' && $mediaUrl)
                                <audio controls src="{{ $mediaUrl }}" style="width: 260px; max-width: 100%;"></audio>
                            @elseif(in_array($type, ['documento', 'archivo'], true) && $mediaUrl)
                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer">Abrir documento</a>
                            @endif

                            @if($message->contenido !== '')
                                <div>{{ $message->contenido }}</div>
                            @endif

                            <div class="wa-time">
                                {{ optional($message->created_at)->format('H:i') }}
                                @if($message->tipo_remitente === 'empleado')
                                    · {{ $message->error_message ? 'error' : ($message->delivered_at ? 'enviado' : 'pendiente') }}
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <footer class="wa-composer">
                @error('messageText') <span class="wa-small" style="color: var(--wa-danger);">{{ $message }}</span> @enderror
                @error('imageUpload') <span class="wa-small" style="color: var(--wa-danger);">{{ $message }}</span> @enderror
                @error('audioUpload') <span class="wa-small" style="color: var(--wa-danger);">{{ $message }}</span> @enderror

                <div class="wa-compose-row">
                    @if($settings['chat_allow_text'])
                        <textarea wire:model.defer="messageText" wire:keydown="markTyping" class="wa-textarea" placeholder="Escribe un mensaje"></textarea>
                    @endif

                    @if($settings['chat_allow_image'])
                        <label class="wa-icon-btn" title="Adjuntar imagen">
                            Img
                            <input wire:model="imageUpload" class="wa-file-input" type="file" accept="image/*">
                        </label>
                    @endif

                    @if($settings['chat_allow_audio'])
                        <label class="wa-icon-btn" title="Subir audio">
                            Aud
                            <input wire:model="audioUpload" class="wa-file-input" type="file" accept="audio/*">
                        </label>
                    @endif

                    @if($settings['chat_allow_text'])
                        <button wire:click="sendText" class="wa-send" type="button">Enviar</button>
                    @endif
                </div>

                <div class="wa-compose-row">
                    @if($imageUpload)
                        <span class="wa-small">Imagen lista: {{ $imageUpload->getClientOriginalName() }}</span>
                        <button wire:click="sendImage" class="wa-action" type="button">Enviar imagen</button>
                    @endif
                    @if($audioUpload)
                        <span class="wa-small">Audio listo: {{ $audioUpload->getClientOriginalName() }}</span>
                        <button wire:click="sendAudio" class="wa-action" type="button">Enviar audio</button>
                    @endif
                </div>
            </footer>
        @else
            <div class="wa-empty">Selecciona una conversacion.</div>
        @endif
    </main>

     <aside class="wa-column wa-right">
         @if($activeConversation)
             @php
                 $client = $activeConversation->cliente;
                 $firstContact = data_get($activeConversation->metadata, 'primer_contacto_at') ?: optional($activeConversation->created_at)->toIso8601String();
             @endphp
             <div class="wa-client-card">
                 <h2 class="wa-title">Ficha cliente</h2>
             </div>
             <div class="wa-panel-scroll">
                 <section class="wa-card">
                     <strong>{{ $client?->nombrecli ?: $activeName }}</strong>
                     <span class="wa-small">{{ $client?->telefonocli ?: $activeNumber }}</span>
                     <span class="wa-small">Primer contacto: {{ \Carbon\Carbon::parse($firstContact)->format('d/m/Y H:i') }}</span>
                 </section>

                 <section class="wa-card">
                     <strong>Historial compras</strong>
                     @forelse($client?->ventas?->take(6) ?? [] as $sale)
                         <div class="wa-meta-row">
                             <span class="wa-small">{{ optional($sale->fechaven)->format('d/m/Y') ?: $sale->idven }}</span>
                             <span class="wa-badge">${{ number_format((float) $sale->totalpagoven, 2) }}</span>
                         </div>
                     @empty
                         <span class="wa-small">Sin compras registradas.</span>
                     @endforelse
                 </section>

                 <section class="wa-card">
                     <strong>Notas internas</strong>
                     <span class="wa-small">{{ data_get($activeConversation->metadata, 'notas') ?: 'Sin notas.' }}</span>
                 </section>

                 <section class="wa-card">
                     <strong>Tags</strong>
                     <div class="wa-filters">
                         @forelse((array) data_get($activeConversation->metadata, 'tags', []) as $tag)
                             <span class="wa-badge">{{ $tag }}</span>
                         @empty
                             <span class="wa-small">Sin tags.</span>
                         @endforelse
                     </div>
                 </section>
             </div>
         @else
             <div class="wa-empty">Sin conversacion activa.</div>
         @endif
     </aside>

     <!-- Modal Configuracion -->
     @if($showSettingsModal)
     <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
         <div style="background: white; border-radius: 8px; padding: 24px; width: 500px; max-width: 90%; max-height: 80vh; overflow-y: auto;">
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                 <h2 style="margin: 0; font-size: 18px; font-weight: 700;">⚙️ Configuracion Chat WhatsApp</h2>
                 <button wire:click="$toggle('showSettingsModal')" style="background: none; border: 0; cursor: pointer; font-size: 20px; padding: 4px 8px;">×</button>
             </div>

             <div style="display: grid; gap: 16px;">
                 <div style="border: 1px solid #d9e1e8; border-radius: 6px; padding: 12px;">
                     <strong>CHAT_WEBHOOK_TOKEN</strong>
                     <input type="text" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.chat_webhook_token" wire:change="saveSetting('chat_webhook_token', $event.target.value)" placeholder="Token de seguridad webhook">
                 </div>

                 <div style="border: 1px solid #d9e1e8; border-radius: 6px; padding: 12px;">
                     <strong>N8N_WEBHOOK_URL</strong>
                     <input type="text" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.n8n_webhook_url" wire:change="saveSetting('n8n_webhook_url', $event.target.value)" placeholder="URL webhook n8n para salidas">
                 </div>

                 <div style="border: 1px solid #d9e1e8; border-radius: 6px; padding: 12px;">
                     <strong>Evolution API URL</strong>
                     <input type="text" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.evoapi_base_url" wire:change="saveSetting('evoapi_base_url', $event.target.value)" placeholder="https://evoapi.tudominio.com">
                 </div>

                 <div style="border: 1px solid #d9e1e8; border-radius: 6px; padding: 12px;">
                     <strong>Evolution API Key</strong>
                     <input type="password" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.evoapi_api_key" wire:change="saveSetting('evoapi_api_key', $event.target.value)" placeholder="API Key de Evolution">
                 </div>

                 <hr style="border: 0; border-top: 1px solid #d9e1e8; margin: 4px 0;">

                 <h3 style="margin: 0; font-size: 16px;">Tipos de mensaje permitidos:</h3>
                 <div style="border: 1px solid #d9e1e8; border-radius: 6px; padding: 12px;">
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Permitir texto</span>
                         <input type="checkbox" wire:change="saveSetting('chat_allow_text', $event.target.checked)" {{ $settings['chat_allow_text'] ? 'checked' : '' }}>
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Permitir imagen</span>
                         <input type="checkbox" wire:change="saveSetting('chat_allow_image', $event.target.checked)" {{ $settings['chat_allow_image'] ? 'checked' : '' }}>
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Permitir audio</span>
                         <input type="checkbox" wire:change="saveSetting('chat_allow_audio', $event.target.checked)" {{ $settings['chat_allow_audio'] ? 'checked' : '' }}>
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center;">
                         <span>Limite upload (MB)</span>
                         <input type="number" class="wa-search" style="width: 80px;" wire:model.blur="settings.chat_max_upload_mb" wire:change="saveSetting('chat_max_upload_mb', $event.target.value)" min="1" max="100">
                     </div>
                 </div>

                 <hr style="border: 0; border-top: 1px solid #d9e1e8; margin: 4px 0;">

                 <h3 style="margin: 0; font-size: 16px;">Endpoints listos:</h3>
                 <div style="border: 1px solid #d9e1e8; border-radius: 6px; padding: 12px;">
                     <div style="display: grid; gap: 8px;">
                         <div>
                             <span class="wa-small">Webhook Inbound:</span>
                             <code style="background: var(--wa-bg); padding: 4px 6px; border-radius: 4px; font-size: 11px; display: block; word-break: break-all;">{{ route('api.chat.whatsapp.inbound') }}</code>
                         </div>
                         <div>
                             <span class="wa-small">Webhook Token Header:</span>
                             <code style="background: var(--wa-bg); padding: 4px 6px; border-radius: 4px; font-size: 11px; display: block;">X-Chat-Webhook-Token</code>
                         </div>
                     </div>
                 </div>

                 <div style="margin-top: 16px;">
                     <button wire:click="$toggle('showSettingsModal')" class="wa-send" style="width: 100%;">Guardar y cerrar</button>
                 </div>
             </div>
         </div>
     </div>
     @endif

     <script>
         document.addEventListener('livewire:init', () => {
             Livewire.on('chat-scroll-bottom', () => {
                 setTimeout(() => {
                     const container = document.getElementById('wa-messages');
                     if (container) {
                         container.scrollTop = container.scrollHeight;
                     }
                 }, 80);
             });
         });
     </script>
 </div>

