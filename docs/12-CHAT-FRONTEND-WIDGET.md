# 12. Frontend Chat - Widget y Panel de Empleados

## Índice
1. [Arquitectura del Frontend](#1-arquitectura-del-frontend)
2. [Widget de Chat para Clientes](#2-widget-de-chat-para-clientes)
3. [Panel de Conversaciones para Empleados](#3-panel-de-conversaciones-para-empleados)
4. [Sistema de Sesiones Anónimas](#4-sistema-de-sesiones-anónimas)
5. [WebSockets con Laravel Reverb](#5-websockets-con-laravel-reverb)
6. [Implementación Paso a Paso](#6-implementación-paso-a-paso)

---

## 1. Arquitectura del Frontend

### 1.1 Componentes del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND CHAT SYSTEM                      │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  LADO CLIENTE (Vista Pública)                               │
│  ┌────────────────────────────────────────┐                 │
│  │  Widget Chat (Componente Inyectable)   │                 │
│  │  ┌──────────────────────────────────┐  │                 │
│  │  │  🔵 Burbuja Flotante (cerrada)   │  │                 │
│  │  │  ────────────────────────────────│  │                 │
│  │  │  Click → Abre ventana de chat    │  │                 │
│  │  └──────────────────────────────────┘  │                 │
│  │                                         │                 │
│  │  ┌──────────────────────────────────┐  │                 │
│  │  │  💬 Ventana Chat (abierta)       │  │                 │
│  │  │  ────────────────────────────────│  │                 │
│  │  │  • Cliente autenticado: chat     │  │                 │
│  │  │    asociado a idcli              │  │                 │
│  │  │                                  │  │                 │
│  │  │  • Cliente anónimo: sesión temp  │  │                 │
│  │  │    guardada en localStorage      │  │                 │
│  │  │    duración: 20 horas            │  │                 │
│  │  └──────────────────────────────────┘  │                 │
│  └────────────────────────────────────────┘                 │
│                                                              │
│  LADO EMPLEADOS (Panel Admin)                               │
│  ┌────────────────────────────────────────┐                 │
│  │  Panel de Conversaciones               │                 │
│  │  ┌──────────────────────────────────┐  │                 │
│  │  │  Lista de Chats       │ Mensajes │  │                 │
│  │  │  ─────────────────────│──────────│  │                 │
│  │  │  [🔴 3] Cliente 1     │          │  │                 │
│  │  │  [🟡 1] Cliente 2     │  Área de │  │                 │
│  │  │  [🟢 0] Cliente 3     │  mensajes│  │                 │
│  │  │  [👤 5] Anónimo #123  │          │  │                 │
│  │  └──────────────────────────────────┘  │                 │
│  └────────────────────────────────────────┘                 │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 Flujo de Autenticación

```
┌─────────────────────────────────────────────────────────────┐
│  Cliente visita la página                                   │
│         │                                                    │
│         ▼                                                    │
│  ¿Está autenticado?                                         │
│         │                                                    │
│    ┌────┴────┐                                              │
│    │         │                                              │
│   SÍ        NO                                              │
│    │         │                                              │
│    │    ┌────▼────────────────────────────────┐            │
│    │    │ Generar/Recuperar Session ID        │            │
│    │    │ (localStorage)                      │            │
│    │    │                                     │            │
│    │    │ Format: anon_[fingerprint]_[hash]  │            │
│    │    │ Duración: 20 horas                 │            │
│    │    └─────────────────────────────────────┘            │
│    │         │                                              │
│    ▼         ▼                                              │
│  Cargar conversación                                        │
│  existente o crear nueva                                    │
│         │                                                    │
│         ▼                                                    │
│  Renderizar widget con historial                            │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 1.3 Estados del Widget

- **🔵 Minimizado**: Burbuja flotante en esquina inferior derecha
- **💬 Abierto**: Ventana de chat expandida (400x600px)
- **⏳ Conectando**: Estableciendo WebSocket
- **✅ Conectado**: WebSocket activo, tiempo real
- **❌ Desconectado**: Sin conexión, mensajes en cola

---

## 2. Widget de Chat para Clientes

### 2.1 Estructura de Archivos

```
resources/
├── js/
│   └── components/
│       └── chat/
│           ├── ChatWidget.vue          # Componente principal
│           ├── ChatBubble.vue          # Burbuja flotante
│           ├── ChatWindow.vue          # Ventana de chat
│           ├── MessageList.vue         # Lista de mensajes
│           ├── MessageInput.vue        # Input de mensaje
│           └── utils/
│               ├── sessionManager.js   # Manejo sesiones anónimas
│               └── fingerprint.js      # Device fingerprinting
└── views/
    └── components/
        └── chat-widget.blade.php       # Para inyectar en vistas
```

### 2.2 Componente Principal: ChatWidget.vue

```vue
<template>
  <div class="chat-widget-container" :class="{ 'widget-open': isOpen }">
    <!-- Burbuja flotante -->
    <ChatBubble 
      v-if="!isOpen"
      :unread-count="unreadCount"
      @open="openChat"
    />

    <!-- Ventana de chat -->
    <ChatWindow
      v-else
      :conversation="conversation"
      :messages="messages"
      :is-authenticated="isAuthenticated"
      :session-info="sessionInfo"
      @close="closeChat"
      @send-message="sendMessage"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import ChatBubble from './ChatBubble.vue';
import ChatWindow from './ChatWindow.vue';
import SessionManager from './utils/sessionManager';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export default {
  name: 'ChatWidget',
  components: {
    ChatBubble,
    ChatWindow,
  },
  props: {
    clienteId: {
      type: Number,
      default: null, // null = anónimo
    },
    apiUrl: {
      type: String,
      default: '/api/v1/chat',
    },
  },
  setup(props) {
    const isOpen = ref(false);
    const conversation = ref(null);
    const messages = ref([]);
    const unreadCount = ref(0);
    const sessionManager = new SessionManager();
    const echoInstance = ref(null);

    const isAuthenticated = computed(() => props.clienteId !== null);
    
    const sessionInfo = computed(() => ({
      type: isAuthenticated.value ? 'authenticated' : 'anonymous',
      id: isAuthenticated.value ? props.clienteId : sessionManager.getSessionId(),
      expiresAt: sessionManager.getExpirationTime(),
    }));

    // Inicializar sesión
    const initSession = async () => {
      if (!isAuthenticated.value) {
        // Cliente anónimo: crear o recuperar sesión
        await sessionManager.init();
      }

      // Cargar conversación existente
      await loadConversation();
    };

    // Cargar conversación
    const loadConversation = async () => {
      try {
        const endpoint = isAuthenticated.value
          ? `${props.apiUrl}/cliente/${props.clienteId}/conversacion`
          : `${props.apiUrl}/anonimo/${sessionManager.getSessionId()}/conversacion`;

        const response = await fetch(endpoint);
        const data = await response.json();

        if (data.success) {
          conversation.value = data.data.conversacion;
          messages.value = data.data.mensajes || [];
          unreadCount.value = 0;
        }
      } catch (error) {
        console.error('Error loading conversation:', error);
      }
    };

    // Enviar mensaje
    const sendMessage = async (contenido) => {
      try {
        const payload = isAuthenticated.value
          ? { idcli: props.clienteId, contenido }
          : { session_id: sessionManager.getSessionId(), contenido };

        const response = await fetch(`${props.apiUrl}/cliente/enviar`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (data.success) {
          messages.value.push(data.data.mensaje);
          conversation.value = data.data.conversacion;
        }
      } catch (error) {
        console.error('Error sending message:', error);
      }
    };

    // WebSocket - Escuchar nuevos mensajes
    const setupWebSocket = () => {
      if (!conversation.value) return;

      echoInstance.value = new Echo({
        broadcaster: 'reverb',
        key: window.REVERB_APP_KEY,
        wsHost: window.REVERB_HOST,
        wsPort: window.REVERB_PORT,
        forceTLS: false,
      });

      echoInstance.value
        .channel(`conversacion.${conversation.value.idconv}`)
        .listen('.mensaje.nuevo', (event) => {
          // Solo agregar si es mensaje de empleado/sistema
          if (event.mensaje.tipo_remitente !== 'cliente') {
            messages.value.push(event.mensaje);
            
            // Si está minimizado, incrementar contador
            if (!isOpen.value) {
              unreadCount.value++;
            }
          }
        });
    };

    const openChat = () => {
      isOpen.value = true;
      unreadCount.value = 0;
      setupWebSocket();
    };

    const closeChat = () => {
      isOpen.value = false;
      if (echoInstance.value) {
        echoInstance.value.disconnect();
      }
    };

    onMounted(async () => {
      await initSession();
    });

    onUnmounted(() => {
      if (echoInstance.value) {
        echoInstance.value.disconnect();
      }
    });

    return {
      isOpen,
      conversation,
      messages,
      unreadCount,
      isAuthenticated,
      sessionInfo,
      openChat,
      closeChat,
      sendMessage,
    };
  },
};
</script>

<style scoped>
.chat-widget-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
}

.chat-widget-container.widget-open {
  bottom: 20px;
  right: 20px;
}
</style>
```

### 2.3 Gestor de Sesiones Anónimas

**Archivo**: `resources/js/components/chat/utils/sessionManager.js`

```javascript
import FingerprintJS from '@fingerprintjs/fingerprintjs';

class SessionManager {
  constructor() {
    this.storageKey = 'streamify_chat_session';
    this.sessionDuration = 20 * 60 * 60 * 1000; // 20 horas en ms
  }

  /**
   * Inicializar sesión anónima
   */
  async init() {
    const existingSession = this.getSession();

    // Si existe sesión válida, retornarla
    if (existingSession && !this.isExpired(existingSession)) {
      return existingSession;
    }

    // Crear nueva sesión
    const newSession = await this.createSession();
    this.saveSession(newSession);
    return newSession;
  }

  /**
   * Crear nueva sesión con fingerprint
   */
  async createSession() {
    const fp = await FingerprintJS.load();
    const result = await fp.get();
    
    const sessionId = `anon_${result.visitorId}_${this.generateHash()}`;
    const createdAt = Date.now();
    const expiresAt = createdAt + this.sessionDuration;

    return {
      sessionId,
      fingerprint: result.visitorId,
      createdAt,
      expiresAt,
      components: result.components, // Info del dispositivo
    };
  }

  /**
   * Generar hash aleatorio
   */
  generateHash() {
    return Math.random().toString(36).substring(2, 15) +
           Math.random().toString(36).substring(2, 15);
  }

  /**
   * Guardar sesión en localStorage
   */
  saveSession(session) {
    localStorage.setItem(this.storageKey, JSON.stringify(session));
  }

  /**
   * Obtener sesión actual
   */
  getSession() {
    const data = localStorage.getItem(this.storageKey);
    return data ? JSON.parse(data) : null;
  }

  /**
   * Obtener solo el ID de sesión
   */
  getSessionId() {
    const session = this.getSession();
    return session ? session.sessionId : null;
  }

  /**
   * Verificar si sesión está expirada
   */
  isExpired(session) {
    return Date.now() > session.expiresAt;
  }

  /**
   * Obtener tiempo de expiración
   */
  getExpirationTime() {
    const session = this.getSession();
    if (!session) return null;

    const remainingTime = session.expiresAt - Date.now();
    const hours = Math.floor(remainingTime / (1000 * 60 * 60));
    const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));

    return { hours, minutes, timestamp: session.expiresAt };
  }

  /**
   * Limpiar sesión expirada
   */
  clearSession() {
    localStorage.removeItem(this.storageKey);
  }

  /**
   * Renovar sesión (extender duración)
   */
  renewSession() {
    const session = this.getSession();
    if (session) {
      session.expiresAt = Date.now() + this.sessionDuration;
      this.saveSession(session);
    }
  }
}

export default SessionManager;
```

### 2.4 Componente Burbuja

**Archivo**: `resources/js/components/chat/ChatBubble.vue`

```vue
<template>
  <div class="chat-bubble" @click="$emit('open')">
    <div class="bubble-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="32" height="32">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
      </svg>
    </div>
    
    <div v-if="unreadCount > 0" class="bubble-badge">
      {{ unreadCount }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatBubble',
  props: {
    unreadCount: {
      type: Number,
      default: 0,
    },
  },
  emits: ['open'],
};
</script>

<style scoped>
.chat-bubble {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
}

.chat-bubble:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

.bubble-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.bubble-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}
</style>
```

### 2.5 Ventana de Chat

**Archivo**: `resources/js/components/chat/ChatWindow.vue`

```vue
<template>
  <div class="chat-window">
    <!-- Header -->
    <div class="chat-header">
      <div class="header-info">
        <div class="header-title">Streamify Soporte</div>
        <div class="header-status">
          <span class="status-dot" :class="statusClass"></span>
          {{ statusText }}
        </div>
      </div>
      <button class="close-btn" @click="$emit('close')">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
          <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
      </button>
    </div>

    <!-- Advertencia para anónimos -->
    <div v-if="!isAuthenticated" class="anonymous-warning">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
      </svg>
      <span>
        Sesión temporal. 
        <a href="/login" class="login-link">Inicia sesión</a> 
        para historial completo.
      </span>
      <div class="session-timer">
        Expira en: {{ formatExpirationTime }}
      </div>
    </div>

    <!-- Área de mensajes -->
    <div class="messages-container" ref="messagesContainer">
      <div v-if="messages.length === 0" class="empty-state">
        <div class="empty-icon">💬</div>
        <p>¡Hola! ¿En qué podemos ayudarte hoy?</p>
      </div>

      <div
        v-for="message in messages"
        :key="message.idmsg"
        class="message"
        :class="messageClass(message)"
      >
        <div class="message-content">
          <div class="message-sender">{{ message.nombre_remitente }}</div>
          <div class="message-text">{{ message.contenido }}</div>
          <div class="message-time">{{ formatTime(message.created_at) }}</div>
        </div>
      </div>

      <!-- Indicador "escribiendo..." -->
      <div v-if="isTyping" class="typing-indicator">
        <span></span><span></span><span></span>
      </div>
    </div>

    <!-- Input de mensaje -->
    <div class="message-input-container">
      <textarea
        v-model="newMessage"
        @keydown.enter.prevent="handleSend"
        placeholder="Escribe un mensaje..."
        rows="1"
        class="message-input"
      ></textarea>
      <button
        @click="handleSend"
        :disabled="!newMessage.trim()"
        class="send-btn"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick } from 'vue';

export default {
  name: 'ChatWindow',
  props: {
    conversation: Object,
    messages: Array,
    isAuthenticated: Boolean,
    sessionInfo: Object,
  },
  emits: ['close', 'send-message'],
  setup(props, { emit }) {
    const newMessage = ref('');
    const messagesContainer = ref(null);
    const isTyping = ref(false);

    const statusClass = computed(() => {
      return props.conversation?.estado === 'en_atencion' ? 'online' : 'offline';
    });

    const statusText = computed(() => {
      return props.conversation?.estado === 'en_atencion' 
        ? 'En línea' 
        : 'Te responderemos pronto';
    });

    const formatExpirationTime = computed(() => {
      if (!props.sessionInfo?.expiresAt) return '';
      const { hours, minutes } = props.sessionInfo.expiresAt;
      return `${hours}h ${minutes}m`;
    });

    const messageClass = (message) => {
      return message.tipo_remitente === 'cliente' ? 'message-sent' : 'message-received';
    };

    const formatTime = (timestamp) => {
      const date = new Date(timestamp);
      return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    };

    const handleSend = () => {
      if (!newMessage.value.trim()) return;
      
      emit('send-message', newMessage.value.trim());
      newMessage.value = '';
    };

    const scrollToBottom = () => {
      nextTick(() => {
        if (messagesContainer.value) {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
      });
    };

    watch(() => props.messages, () => {
      scrollToBottom();
    }, { deep: true });

    return {
      newMessage,
      messagesContainer,
      isTyping,
      statusClass,
      statusText,
      formatExpirationTime,
      messageClass,
      formatTime,
      handleSend,
    };
  },
};
</script>

<style scoped>
.chat-window {
  width: 380px;
  height: 600px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-title {
  font-weight: 600;
  font-size: 16px;
}

.header-status {
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
}

.status-dot.offline {
  background: #6b7280;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
}

.anonymous-warning {
  background: #fef3c7;
  border-bottom: 1px solid #fbbf24;
  padding: 12px;
  font-size: 13px;
  color: #92400e;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.anonymous-warning svg {
  color: #f59e0b;
  flex-shrink: 0;
}

.login-link {
  color: #2563eb;
  text-decoration: underline;
}

.session-timer {
  font-size: 11px;
  color: #78350f;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f9fafb;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #6b7280;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 12px;
}

.message {
  margin-bottom: 12px;
  display: flex;
}

.message-sent {
  justify-content: flex-end;
}

.message-sent .message-content {
  background: #667eea;
  color: white;
  border-radius: 12px 12px 0 12px;
}

.message-received .message-content {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px 12px 12px 0;
}

.message-content {
  max-width: 75%;
  padding: 10px 14px;
}

.message-sender {
  font-size: 11px;
  font-weight: 600;
  margin-bottom: 4px;
  opacity: 0.8;
}

.message-text {
  font-size: 14px;
  line-height: 1.4;
}

.message-time {
  font-size: 10px;
  margin-top: 4px;
  opacity: 0.6;
}

.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 12px;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  background: #9ca3af;
  border-radius: 50%;
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
  }
  30% {
    transform: translateY(-10px);
  }
}

.message-input-container {
  border-top: 1px solid #e5e7eb;
  padding: 12px;
  display: flex;
  gap: 8px;
  background: white;
}

.message-input {
  flex: 1;
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  padding: 10px 16px;
  resize: none;
  font-size: 14px;
  font-family: inherit;
  outline: none;
}

.message-input:focus {
  border-color: #667eea;
}

.send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #667eea;
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.send-btn:disabled {
  background: #d1d5db;
  cursor: not-allowed;
}

.send-btn:not(:disabled):hover {
  background: #5568d3;
}
</style>
```

---

## 3. Panel de Conversaciones para Empleados

### 3.1 Componente Livewire

```bash
php artisan make:livewire Chat/PanelConversaciones
```

**Archivo**: `app/Livewire/Chat/PanelConversaciones.php`

```php
<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversacion;
use App\Models\Mensaje;
use Livewire\WithPagination;

class PanelConversaciones extends Component
{
    use WithPagination;

    public $conversacionActiva;
    public $mensajes = [];
    public $nuevoMensaje = '';
    public $filtroEstado = 'todas';
    public $busqueda = '';

    protected $listeners = [
        'echo:chat,mensaje.nuevo' => 'recibirMensaje',
        'echo:chat,conversacion.actualizada' => 'actualizarConversacion',
    ];

    public function mount()
    {
        // Verificar permiso
        if (!auth()->user()->can('chat.ver')) {
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
            $query->abiertas();
        }

        // Búsqueda por cliente
        if ($this->busqueda) {
            $query->whereHas('cliente', function ($q) {
                $q->where('nombrecli', 'like', "%{$this->busqueda}%")
                  ->orWhere('telefonocli', 'like', "%{$this->busqueda}%");
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
        $this->mensajes = $this->conversacionActiva->mensajes()->with(['empleado', 'cliente'])->get();
        
        // Marcar como leída
        $this->conversacionActiva->marcarComoLeida();
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

        $this->mensajes[] = $mensaje->load('empleado');
        $this->nuevoMensaje = '';

        // Broadcast via WebSocket
        broadcast(new \App\Events\NuevoMensaje($mensaje));
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

    public function recibirMensaje($event)
    {
        // Si el mensaje es de la conversación activa, agregarlo
        if ($this->conversacionActiva && $event['mensaje']['idconv'] == $this->conversacionActiva->idconv) {
            $this->mensajes[] = Mensaje::with(['empleado', 'cliente'])->find($event['mensaje']['idmsg']);
        }
    }

    public function actualizarConversacion($event)
    {
        // Refrescar lista de conversaciones
        $this->render();
    }
}
```

### 3.2 Vista Blade

**Archivo**: `resources/views/livewire/chat/panel-conversaciones.blade.php`

```blade
<div class="flex h-screen bg-gray-100">
    <!-- Lista de conversaciones -->
    <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
            <h2 class="text-xl font-bold">Chat Streamify</h2>
            <p class="text-sm opacity-90 mt-1">Panel de Conversaciones</p>
        </div>

        <!-- Filtros -->
        <div class="p-4 space-y-3 border-b border-gray-200">
            <input
                wire:model.live="busqueda"
                type="text"
                placeholder="Buscar cliente..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
            />

            <div class="flex gap-2">
                <button
                    wire:click="$set('filtroEstado', 'todas')"
                    class="flex-1 px-3 py-2 text-sm rounded-lg {{ $filtroEstado === 'todas' ? 'bg-purple-600 text-white' : 'bg-gray-200' }}"
                >
                    Todas
                </button>
                <button
                    wire:click="$set('filtroEstado', 'abierta')"
                    class="flex-1 px-3 py-2 text-sm rounded-lg {{ $filtroEstado === 'abierta' ? 'bg-purple-600 text-white' : 'bg-gray-200' }}"
                >
                    Abiertas
                </button>
                <button
                    wire:click="$set('filtroEstado', 'cerrada')"
                    class="flex-1 px-3 py-2 text-sm rounded-lg {{ $filtroEstado === 'cerrada' ? 'bg-purple-600 text-white' : 'bg-gray-200' }}"
                >
                    Cerradas
                </button>
            </div>
        </div>

        <!-- Lista -->
        <div class="flex-1 overflow-y-auto">
            @forelse($conversaciones as $conv)
                <div
                    wire:click="seleccionarConversacion({{ $conv->idconv }})"
                    class="p-4 border-b border-gray-200 hover:bg-gray-50 cursor-pointer transition {{ $conversacionActiva?->idconv == $conv->idconv ? 'bg-purple-50 border-l-4 border-l-purple-600' : '' }}"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-900">
                                    {{ $conv->cliente->nombrecli ?? 'Anónimo' }}
                                </span>
                                
                                @if($conv->mensajes_no_leidos > 0)
                                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                        {{ $conv->mensajes_no_leidos }}
                                    </span>
                                @endif
                            </div>
                            
                            <p class="text-sm text-gray-600 truncate mt-1">
                                {{ $conv->ultimoMensaje?->contenido ?? 'Sin mensajes' }}
                            </p>
                            
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs px-2 py-1 rounded-full
                                    {{ $conv->estado === 'abierta' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $conv->estado === 'en_atencion' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $conv->estado === 'cerrada' ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst($conv->estado) }}
                                </span>
                                
                                @if($conv->requiere_humano)
                                    <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-800">
                                        🚨 Requiere humano
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <span class="text-xs text-gray-500">
                            {{ $conv->ultima_actividad->diffForHumans() }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <p>No hay conversaciones</p>
                </div>
            @endforelse
        </div>

        <!-- Paginación -->
        <div class="p-4 border-t border-gray-200">
            {{ $conversaciones->links() }}
        </div>
    </div>

    <!-- Panel de mensajes -->
    <div class="w-2/3 flex flex-col">
        @if($conversacionActiva)
            <!-- Header del chat -->
            <div class="p-4 bg-white border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-lg">
                        {{ $conversacionActiva->cliente->nombrecli ?? 'Cliente Anónimo' }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        {{ $conversacionActiva->cliente->telefonocli ?? 'Sin teléfono' }}
                    </p>
                </div>
                
                <div class="flex gap-2">
                    @if($conversacionActiva->estado !== 'cerrada')
                        <button
                            wire:click="cerrarConversacion"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                        >
                            Cerrar Chat
                        </button>
                    @endif
                </div>
            </div>

            <!-- Mensajes -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50">
                @foreach($mensajes as $msg)
                    <div class="mb-4 {{ $msg->tipo_remitente === 'empleado' ? 'text-right' : '' }}">
                        <div class="inline-block max-w-md px-4 py-3 rounded-lg
                            {{ $msg->tipo_remitente === 'empleado' ? 'bg-purple-500 text-white' : 'bg-white text-gray-900' }}
                        ">
                            <p class="text-xs font-semibold mb-1 opacity-75">
                                {{ $msg->nombre_remitente }}
                            </p>
                            <p class="text-sm">{{ $msg->contenido }}</p>
                            <p class="text-xs opacity-75 mt-2">
                                {{ $msg->created_at->format('H:i') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Input -->
            <div class="p-4 bg-white border-t border-gray-200">
                <form wire:submit="enviarMensaje" class="flex gap-2">
                    <input
                        wire:model="nuevoMensaje"
                        type="text"
                        placeholder="Escribe un mensaje..."
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                    />
                    <button
                        type="submit"
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                    >
                        Enviar
                    </button>
                </form>
                
                @error('mensaje')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div class="flex-1 flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <div class="text-6xl mb-4">💬</div>
                    <p>Selecciona una conversación</p>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Escuchar eventos de WebSocket
    Echo.channel('chat')
        .listen('.mensaje.nuevo', (e) => {
            @this.call('recibirMensaje', e);
        })
        .listen('.conversacion.actualizada', (e) => {
            @this.call('actualizarConversacion', e);
        });
</script>
@endpush
```

---

## 4. Sistema de Sesiones Anónimas

### 4.1 Modificar API para soportar anónimos

**Actualizar**: `app/Http/Controllers/Api/V1/ChatController.php`

Agregar método nuevo:

```php
/**
 * Cliente anónimo envía mensaje
 * POST /api/v1/chat/anonimo/enviar
 */
public function anonimoEnviarMensaje(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'contenido' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar o crear conversación anónima
        $conversacion = Conversacion::where('metadata->session_id', $request->session_id)
            ->abiertas()
            ->first();

        if (!$conversacion) {
            $conversacion = Conversacion::create([
                'idcli' => null, // Sin cliente
                'estado' => 'abierta',
                'ultima_actividad' => now(),
                'metadata' => [
                    'session_id' => $request->session_id,
                    'tipo' => 'anonimo',
                    'created_at' => now()->toIso8601String(),
                ],
            ]);
        }

        // Crear mensaje
        $mensaje = Mensaje::create([
            'idconv' => $conversacion->idconv,
            'tipo_remitente' => 'cliente',
            'idcli' => null,
            'contenido' => $request->contenido,
            'tipo_contenido' => 'texto',
            'metadata' => [
                'session_id' => $request->session_id,
            ],
        ]);

        // Incrementar contador
        $conversacion->increment('mensajes_no_leidos');
        $conversacion->update(['ultima_actividad' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado',
            'data' => [
                'conversacion' => $conversacion,
                'mensaje' => $mensaje,
            ]
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al enviar mensaje',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener conversación anónima
 * GET /api/v1/chat/anonimo/{session_id}/conversacion
 */
public function obtenerConversacionAnonima(string $sessionId)
{
    try {
        $conversacion = Conversacion::where('metadata->session_id', $sessionId)
            ->with('mensajes')
            ->abiertas()
            ->first();

        if (!$conversacion) {
            return response()->json([
                'success' => true,
                'data' => [
                    'conversacion' => null,
                    'mensajes' => [],
                ]
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversacion' => $conversacion,
                'mensajes' => $conversacion->mensajes,
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error al obtener conversación',
            'message' => $e->getMessage()
        ], 500);
    }
}
```

### 4.2 Comando Artisan para limpiar sesiones expiradas

```bash
php artisan make:command LimpiarChatAnonimo
```

**Archivo**: `app/Console/Commands/LimpiarChatAnonimo.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Conversacion;
use Carbon\Carbon;

class LimpiarChatAnonimo extends Command
{
    protected $signature = 'chat:limpiar-anonimos';
    protected $description = 'Eliminar conversaciones anónimas expiradas (>20 horas)';

    public function handle()
    {
        $limite = Carbon::now()->subHours(20);

        $eliminadas = Conversacion::whereNull('idcli')
            ->where('metadata->tipo', 'anonimo')
            ->where('created_at', '<', $limite)
            ->delete();

        $this->info("✅ {$eliminadas} conversaciones anónimas eliminadas");

        return Command::SUCCESS;
    }
}
```

Programar en `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Limpiar chats anónimos cada hora
    $schedule->command('chat:limpiar-anonimos')->hourly();
}
```

---

## 5. WebSockets con Laravel Reverb

### 5.1 Eventos

**Crear evento**: `php artisan make:event NuevoMensaje`

```php
<?php

namespace App\Events;

use App\Models\Mensaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevoMensaje implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mensaje;

    public function __construct(Mensaje $mensaje)
    {
        $this->mensaje = $mensaje->load(['empleado', 'cliente', 'conversacion']);
    }

    public function broadcastOn()
    {
        return [
            new Channel('chat'),
            new Channel('conversacion.' . $this->mensaje->idconv),
        ];
    }

    public function broadcastAs()
    {
        return 'mensaje.nuevo';
    }
}
```

---

## 6. Implementación Paso a Paso

### Paso 1: Instalar dependencias

```bash
# Frontend
npm install @fingerprintjs/fingerprintjs pusher-js laravel-echo

# Backend
composer require pusher/pusher-php-server
php artisan install:broadcasting
```

### Paso 2: Configurar `.env`

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Paso 3: Iniciar Reverb

```bash
php artisan reverb:start
```

### Paso 4: Compilar assets

```bash
npm run dev
```

### Paso 5: Agregar widget a vista de clientes

```blade
<!-- En tu layout principal -->
<div id="chat-widget-mount"></div>

@push('scripts')
<script type="module">
import ChatWidget from './components/chat/ChatWidget.vue';
import { createApp } from 'vue';

const app = createApp(ChatWidget, {
    clienteId: {{ auth('cliente')->check() ? auth('cliente')->id() : 'null' }},
    apiUrl: '/api/v1/chat',
});

app.mount('#chat-widget-mount');
</script>
@endpush
```

---

## 7. Asistentes IA para Empleados

### 7.1 Arquitectura de Asistentes IA

```
┌─────────────────────────────────────────────────────────────────────┐
│                  SISTEMA DE ASISTENTES IA INTERNOS                   │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  EMPLEADO                                                           │
│     │                                                               │
│     ▼                                                               │
│  ┌──────────────────────────────────────────────────────┐          │
│  │  Panel de Asistentes (Sidebar/Modal)                 │          │
│  │  ┌────────────────────────────────────────────────┐  │          │
│  │  │  🤖 Asistentes Disponibles                     │  │          │
│  │  │  ────────────────────────────────────────────  │  │          │
│  │  │  📊 Analista de Datos                         │  │          │
│  │  │  💰 Asesor Financiero                         │  │          │
│  │  │  🎯 Estratega de Ventas                       │  │          │
│  │  │  📝 Generador de Reportes                     │  │          │
│  │  │  🔧 Asistente Técnico                         │  │          │
│  │  │  🎨 Creador de Contenido                      │  │          │
│  │  │  👨‍💼 Coach de Productividad                    │  │          │
│  │  │  🔍 Investigador de Mercado                   │  │          │
│  │  └────────────────────────────────────────────────┘  │          │
│  └──────────────────────────────────────────────────────┘          │
│                                                                      │
│  FLUJO DE INTERACCIÓN                                               │
│  ┌────────────────────────────────────────────────────────────┐    │
│  │  1. Empleado selecciona asistente                          │    │
│  │  2. Abre chat especializado (contexto específico)          │    │
│  │  3. Hace pregunta/solicitud                                │    │
│  │  4. Sistema envía a n8n → DeepSeek/OpenAI                  │    │
│  │  5. IA procesa con contexto del rol                        │    │
│  │  6. Respuesta con acciones sugeridas                       │    │
│  │  7. Empleado ejecuta/rechaza/modifica sugerencias          │    │
│  └────────────────────────────────────────────────────────────┘    │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### 7.2 Roles de Asistentes IA

#### **1. 📊 Analista de Datos**

**Propósito**: Análisis de métricas, KPIs, tendencias y patrones en datos del sistema.

**Capacidades**:
- Generar análisis de ventas diarias/mensuales/anuales
- Identificar productos más vendidos y de baja rotación
- Calcular tasas de conversión de clientes
- Detectar anomalías en transacciones
- Predecir demanda de productos
- Analizar comportamiento de clientes por segmentos

**Prompts del Sistema**:
```
Eres un Analista de Datos experto en Streamify, un sistema de gestión de recargas y ventas.
Tienes acceso a datos de: clientes, productos, ventas, recargas, empleados, contabilidad.
Tu objetivo es proporcionar insights accionables basados en datos reales.
Cuando analices datos, siempre incluye:
- Tendencias numéricas (porcentajes, comparativas)
- Visualizaciones recomendadas
- Acciones sugeridas
```

**Ejemplos de Uso**:
- "¿Cuáles son los 5 productos más vendidos este mes?"
- "Analiza el comportamiento de compra de clientes nuevos vs recurrentes"
- "Predice las ventas para el próximo mes basándote en el historial"
- "Identifica clientes con alto riesgo de abandono"

**Acceso a Datos**:
```php
// El asistente puede consultar:
- DailyStatistic (estadísticas diarias agregadas)
- Venta (transacciones de ventas)
- Producto (inventario, categorías)
- Cliente (segmentación, historial)
- Contabilidad (flujos de caja, ingresos)
```

---

#### **2. 💰 Asesor Financiero**

**Propósito**: Gestión de finanzas, presupuestos, flujos de caja y optimización de costos.

**Capacidades**:
- Calcular márgenes de ganancia por producto
- Analizar flujo de caja proyectado
- Sugerir optimizaciones de costos
- Generar proyecciones financieras
- Detectar gastos inusuales
- Evaluar ROI de campañas/promociones

**Prompts del Sistema**:
```
Eres un Asesor Financiero especializado en negocios de retail y recargas.
Tu misión es mantener la salud financiera del negocio Streamify.
Proporciona:
- Análisis de rentabilidad
- Recomendaciones de pricing
- Alertas de flujo de caja
- Estrategias de reducción de costos
Usa terminología financiera profesional pero clara.
```

**Ejemplos de Uso**:
- "¿Cuál es el margen de ganancia promedio de nuestras recargas?"
- "Proyecta el flujo de caja para los próximos 3 meses"
- "Analiza qué gastos podemos reducir sin afectar operaciones"
- "Calcula el punto de equilibrio de nuestro inventario actual"

---

#### **3. 🎯 Estratega de Ventas**

**Propósito**: Optimizar estrategias de ventas, conversiones y retención de clientes.

**Capacidades**:
- Diseñar campañas promocionales basadas en datos
- Identificar oportunidades de upselling/cross-selling
- Segmentar clientes para marketing dirigido
- Crear scripts de ventas personalizados
- Analizar efectividad de promociones pasadas
- Sugerir bundles de productos

**Prompts del Sistema**:
```
Eres un Estratega de Ventas con expertise en retail y telecomunicaciones.
Tu objetivo es maximizar ingresos y retención de clientes en Streamify.
Diseña estrategias basadas en:
- Comportamiento histórico de clientes
- Tendencias de mercado
- Márgenes de producto
- Estacionalidad
Cada estrategia debe incluir: objetivo, táctica, métrica de éxito.
```

**Ejemplos de Uso**:
- "Diseña una promoción para aumentar ventas de recargas de $20"
- "Crea un plan de retención para clientes inactivos hace 30 días"
- "Sugiere productos complementarios para clientes que compran recargas Movistar"
- "Analiza por qué bajaron las ventas esta semana"

---

#### **4. 📝 Generador de Reportes**

**Propósito**: Crear reportes ejecutivos, operativos y personalizados en lenguaje natural.

**Capacidades**:
- Generar reportes diarios/semanales/mensuales automáticos
- Crear resúmenes ejecutivos en PDF/Excel
- Diseñar dashboards personalizados
- Comparar periodos (mes actual vs anterior)
- Exportar datos en múltiples formatos
- Programar reportes recurrentes

**Prompts del Sistema**:
```
Eres un Generador de Reportes que convierte datos complejos en documentos claros.
Formato estándar de reportes:
1. Resumen ejecutivo (bullet points)
2. Métricas clave (KPIs)
3. Análisis detallado
4. Gráficos/tablas recomendadas
5. Conclusiones y recomendaciones
Prioriza claridad y accionabilidad.
```

**Ejemplos de Uso**:
- "Genera reporte de ventas de diciembre 2025"
- "Crea resumen ejecutivo de rendimiento mensual"
- "Compara ventas de recargas: noviembre vs diciembre"
- "Diseña un dashboard para monitorear stock crítico"

---

#### **5. 🔧 Asistente Técnico**

**Propósito**: Resolver problemas técnicos, depurar errores y optimizar el sistema.

**Capacidades**:
- Diagnosticar errores en el sistema
- Sugerir optimizaciones de base de datos
- Explicar funcionamiento de módulos
- Generar queries SQL complejas
- Documentar procesos técnicos
- Revisar logs y detectar problemas

**Prompts del Sistema**:
```
Eres un Asistente Técnico experto en Laravel, MySQL y sistemas de gestión.
Arquitectura del sistema Streamify:
- Backend: Laravel 11
- Base de datos: MySQL/MariaDB
- Frontend: Livewire, Vue.js
- APIs: RESTful con autenticación API Key
Proporciona soluciones técnicas claras, con código cuando sea necesario.
```

**Ejemplos de Uso**:
- "¿Por qué el trigger de actualización de stock no se ejecuta?"
- "Optimiza esta consulta de reporte de ventas mensuales"
- "Explica cómo funciona el módulo de contabilidad"
- "Genera un query para listar clientes con deuda mayor a $100"

---

#### **6. 🎨 Creador de Contenido**

**Propósito**: Generar contenido para marketing, redes sociales y comunicación con clientes.

**Capacidades**:
- Redactar posts para redes sociales
- Crear copys para promociones
- Diseñar emails de marketing
- Generar descripciones de productos
- Escribir respuestas para FAQs
- Crear scripts para atención al cliente

**Prompts del Sistema**:
```
Eres un Creador de Contenido especializado en telecomunicaciones y retail.
Tono de Streamify: profesional, cercano, confiable.
Audiencia: clientes de recargas telefónicas y productos digitales.
Cada pieza de contenido debe:
- Ser clara y concisa
- Incluir llamado a la acción
- Adaptarse al canal (SMS, email, redes)
- Reflejar valores de marca
```

**Ejemplos de Uso**:
- "Redacta un post de Instagram para promoción de Black Friday"
- "Crea un email de bienvenida para nuevos clientes"
- "Escribe 5 respuestas para preguntas frecuentes sobre recargas"
- "Genera copy para banner de promoción 2x1 en recargas Claro"

---

#### **7. 👨‍💼 Coach de Productividad**

**Propósito**: Mejorar productividad de empleados, optimizar flujos de trabajo y reducir tareas repetitivas.

**Capacidades**:
- Analizar rendimiento individual de empleados
- Sugerir automatizaciones de tareas
- Crear checklists y procedimientos
- Optimizar horarios y turnos
- Identificar cuellos de botella operativos
- Recomendar capacitaciones

**Prompts del Sistema**:
```
Eres un Coach de Productividad enfocado en operaciones de retail.
Analiza:
- Tiempos de atención promedio
- Tareas repetitivas automatizables
- Distribución de carga de trabajo
- Eficiencia de procesos
Proporciona planes de acción concretos para mejorar productividad del equipo.
```

**Ejemplos de Uso**:
- "¿Cómo puedo reducir el tiempo de cierre de caja diario?"
- "Analiza el rendimiento del equipo de ventas esta semana"
- "Sugiere automatizaciones para el proceso de recarga"
- "Crea un checklist para apertura/cierre de turno"

---

#### **8. 🔍 Investigador de Mercado**

**Propósito**: Monitorear competencia, tendencias de mercado y oportunidades de negocio.

**Capacidades**:
- Analizar precios de competencia
- Identificar nuevas oportunidades de producto
- Monitorear tendencias de industria
- Evaluar demanda de nuevos servicios
- Investigar comportamiento de consumidores
- Generar análisis FODA

**Prompts del Sistema**:
```
Eres un Investigador de Mercado especializado en telecomunicaciones en Latinoamérica.
Foco en:
- Operadoras: Movistar, Claro, Tigo, WOM, etc.
- Tendencias: recargas digitales, servicios OTT, paquetes prepago
- Competencia: precios, promociones, nuevos servicios
Proporciona insights estratégicos basados en datos del mercado.
```

**Ejemplos de Uso**:
- "¿Cuáles son las promociones actuales de Movistar en Perú?"
- "Analiza oportunidades de agregar servicios de streaming"
- "Investiga demanda de recargas internacionales"
- "Compara nuestros precios con la competencia"

---

### 7.3 Base de Datos para Asistentes

**Nueva tabla**: `asistentes_ia`

```php
php artisan make:migration create_asistentes_ia_table
```

```php
Schema::create('asistentes_ia', function (Blueprint $table) {
    $table->id('idasistente');
    $table->string('nombre'); // "Analista de Datos", "Asesor Financiero", etc.
    $table->string('slug')->unique(); // "analista-datos", "asesor-financiero"
    $table->string('emoji'); // "📊", "💰", etc.
    $table->text('descripcion');
    $table->text('system_prompt'); // Prompt del sistema para IA
    $table->json('capacidades'); // Array de capacidades
    $table->json('permisos_requeridos')->nullable(); // Permisos necesarios para usar
    $table->string('color_tema')->default('#667eea'); // Color del chat
    $table->boolean('activo')->default(true);
    $table->json('metadata')->nullable(); // Config adicional
    $table->timestamps();
});
```

**Nueva tabla**: `conversaciones_asistentes`

```php
php artisan make:migration create_conversaciones_asistentes_table
```

```php
Schema::create('conversaciones_asistentes', function (Blueprint $table) {
    $table->id('idconv_asistente');
    $table->foreignId('idemp')->constrained('empleados', 'idemp')->onDelete('cascade');
    $table->foreignId('idasistente')->constrained('asistentes_ia', 'idasistente');
    $table->string('titulo')->nullable(); // Título opcional de la conversación
    $table->enum('estado', ['activa', 'archivada'])->default('activa');
    $table->timestamp('ultima_actividad');
    $table->json('contexto')->nullable(); // Contexto adicional (datos cargados, etc.)
    $table->timestamps();
    
    $table->index('idemp');
    $table->index('idasistente');
});
```

**Nueva tabla**: `mensajes_asistentes`

```php
php artisan make:migration create_mensajes_asistentes_table
```

```php
Schema::create('mensajes_asistentes', function (Blueprint $table) {
    $table->id('idmsg_asistente');
    $table->foreignId('idconv_asistente')->constrained('conversaciones_asistentes', 'idconv_asistente')->onDelete('cascade');
    $table->enum('tipo', ['empleado', 'ia', 'sistema']);
    $table->text('contenido');
    $table->json('metadata')->nullable(); // Tokens usados, tiempo respuesta, confidence
    $table->json('acciones_sugeridas')->nullable(); // Botones/acciones que la IA sugiere
    $table->boolean('util')->nullable(); // El empleado marcó si fue útil
    $table->timestamps();
    
    $table->index('idconv_asistente');
});
```

### 7.4 Seeder de Asistentes

```php
php artisan make:seeder AsistentesIASeeder
```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsistentesIASeeder extends Seeder
{
    public function run()
    {
        $asistentes = [
            [
                'nombre' => 'Analista de Datos',
                'slug' => 'analista-datos',
                'emoji' => '📊',
                'descripcion' => 'Experto en análisis de métricas, KPIs, tendencias y patrones en datos del sistema.',
                'system_prompt' => 'Eres un Analista de Datos experto en Streamify, un sistema de gestión de recargas y ventas. Tienes acceso a datos de: clientes, productos, ventas, recargas, empleados, contabilidad. Tu objetivo es proporcionar insights accionables basados en datos reales.',
                'capacidades' => json_encode([
                    'Análisis de ventas por periodo',
                    'Identificación de productos top',
                    'Predicción de demanda',
                    'Detección de anomalías',
                    'Segmentación de clientes',
                ]),
                'permisos_requeridos' => json_encode(['ventas.ver', 'reportes.ver']),
                'color_tema' => '#3b82f6',
            ],
            [
                'nombre' => 'Asesor Financiero',
                'slug' => 'asesor-financiero',
                'emoji' => '💰',
                'descripcion' => 'Especialista en gestión financiera, presupuestos, flujos de caja y optimización de costos.',
                'system_prompt' => 'Eres un Asesor Financiero especializado en negocios de retail y recargas. Tu misión es mantener la salud financiera del negocio Streamify. Proporciona análisis de rentabilidad, recomendaciones de pricing, alertas de flujo de caja y estrategias de reducción de costos.',
                'capacidades' => json_encode([
                    'Cálculo de márgenes',
                    'Proyección de flujo de caja',
                    'Optimización de costos',
                    'Análisis ROI',
                    'Evaluación de rentabilidad',
                ]),
                'permisos_requeridos' => json_encode(['contabilidad.ver', 'reportes.ver']),
                'color_tema' => '#10b981',
            ],
            [
                'nombre' => 'Estratega de Ventas',
                'slug' => 'estratega-ventas',
                'emoji' => '🎯',
                'descripcion' => 'Optimiza estrategias de ventas, conversiones y retención de clientes.',
                'system_prompt' => 'Eres un Estratega de Ventas con expertise en retail y telecomunicaciones. Tu objetivo es maximizar ingresos y retención de clientes en Streamify. Diseña estrategias basadas en comportamiento histórico, tendencias de mercado y márgenes de producto.',
                'capacidades' => json_encode([
                    'Diseño de campañas',
                    'Upselling/cross-selling',
                    'Segmentación de clientes',
                    'Scripts de ventas',
                    'Análisis de promociones',
                ]),
                'permisos_requeridos' => json_encode(['ventas.ver', 'clientes.ver']),
                'color_tema' => '#ef4444',
            ],
            [
                'nombre' => 'Generador de Reportes',
                'slug' => 'generador-reportes',
                'emoji' => '📝',
                'descripcion' => 'Crea reportes ejecutivos, operativos y personalizados en lenguaje natural.',
                'system_prompt' => 'Eres un Generador de Reportes que convierte datos complejos en documentos claros. Formato: 1) Resumen ejecutivo, 2) KPIs, 3) Análisis detallado, 4) Gráficos, 5) Conclusiones. Prioriza claridad y accionabilidad.',
                'capacidades' => json_encode([
                    'Reportes automáticos',
                    'Resúmenes ejecutivos',
                    'Comparativas de periodos',
                    'Exportación de datos',
                    'Dashboards personalizados',
                ]),
                'permisos_requeridos' => json_encode(['reportes.ver']),
                'color_tema' => '#8b5cf6',
            ],
            [
                'nombre' => 'Asistente Técnico',
                'slug' => 'asistente-tecnico',
                'emoji' => '🔧',
                'descripcion' => 'Resuelve problemas técnicos, depura errores y optimiza el sistema.',
                'system_prompt' => 'Eres un Asistente Técnico experto en Laravel 11, MySQL y sistemas de gestión. Conoces toda la arquitectura de Streamify. Proporciona soluciones técnicas claras con código cuando sea necesario.',
                'capacidades' => json_encode([
                    'Diagnóstico de errores',
                    'Optimización de queries',
                    'Generación de código',
                    'Revisión de logs',
                    'Documentación técnica',
                ]),
                'permisos_requeridos' => json_encode(['sistema.admin']),
                'color_tema' => '#6b7280',
            ],
            [
                'nombre' => 'Creador de Contenido',
                'slug' => 'creador-contenido',
                'emoji' => '🎨',
                'descripcion' => 'Genera contenido para marketing, redes sociales y comunicación con clientes.',
                'system_prompt' => 'Eres un Creador de Contenido especializado en telecomunicaciones. Tono de Streamify: profesional, cercano, confiable. Crea contenido claro, con CTA, adaptado al canal y reflejando valores de marca.',
                'capacidades' => json_encode([
                    'Posts para redes sociales',
                    'Emails de marketing',
                    'Descripciones de productos',
                    'FAQs y respuestas',
                    'Scripts de atención',
                ]),
                'permisos_requeridos' => json_encode(['marketing.editar']),
                'color_tema' => '#f59e0b',
            ],
            [
                'nombre' => 'Coach de Productividad',
                'slug' => 'coach-productividad',
                'emoji' => '👨‍💼',
                'descripcion' => 'Mejora productividad de empleados, optimiza flujos de trabajo y reduce tareas repetitivas.',
                'system_prompt' => 'Eres un Coach de Productividad enfocado en operaciones de retail. Analiza rendimiento, sugiere automatizaciones, optimiza horarios y proporciona planes de acción concretos para mejorar productividad.',
                'capacidades' => json_encode([
                    'Análisis de rendimiento',
                    'Automatización de tareas',
                    'Optimización de procesos',
                    'Checklists y procedimientos',
                    'Capacitaciones sugeridas',
                ]),
                'permisos_requeridos' => json_encode(['empleados.ver', 'reportes.ver']),
                'color_tema' => '#14b8a6',
            ],
            [
                'nombre' => 'Investigador de Mercado',
                'slug' => 'investigador-mercado',
                'emoji' => '🔍',
                'descripcion' => 'Monitorea competencia, tendencias de mercado y oportunidades de negocio.',
                'system_prompt' => 'Eres un Investigador de Mercado especializado en telecomunicaciones en Latinoamérica. Analiza operadoras, tendencias, competencia y proporciona insights estratégicos basados en datos del mercado.',
                'capacidades' => json_encode([
                    'Análisis de competencia',
                    'Identificación de oportunidades',
                    'Monitoreo de tendencias',
                    'Evaluación de demanda',
                    'Análisis FODA',
                ]),
                'permisos_requeridos' => json_encode(['reportes.ver']),
                'color_tema' => '#ec4899',
            ],
        ];

        foreach ($asistentes as $asistente) {
            DB::table('asistentes_ia')->insert(array_merge($asistente, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ 8 Asistentes IA creados');
    }
}
```

### 7.5 API de Asistentes para Empleados

**Nuevos endpoints en ChatController**:

```php
/**
 * Listar asistentes IA disponibles para el empleado
 * GET /api/v1/chat/asistentes
 */
public function listarAsistentes(Request $request)
{
    $empleado = $request->user(); // Asumiendo auth:sanctum
    
    $asistentes = DB::table('asistentes_ia')
        ->where('activo', true)
        ->get()
        ->filter(function ($asistente) use ($empleado) {
            // Verificar permisos requeridos
            $permisos = json_decode($asistente->permisos_requeridos, true);
            
            if (empty($permisos)) return true;
            
            return collect($permisos)->every(fn($permiso) => $empleado->can($permiso));
        })
        ->values();

    return response()->json([
        'success' => true,
        'data' => $asistentes,
    ]);
}

/**
 * Crear conversación con asistente
 * POST /api/v1/chat/asistentes/{slug}/conversacion
 */
public function crearConversacionAsistente(Request $request, string $slug)
{
    $empleado = $request->user();
    
    $asistente = DB::table('asistentes_ia')
        ->where('slug', $slug)
        ->where('activo', true)
        ->first();

    if (!$asistente) {
        return response()->json(['success' => false, 'error' => 'Asistente no encontrado'], 404);
    }

    $conversacion = DB::table('conversaciones_asistentes')->insertGetId([
        'idemp' => $empleado->idemp,
        'idasistente' => $asistente->idasistente,
        'titulo' => $request->titulo ?? 'Nueva conversación',
        'estado' => 'activa',
        'ultima_actividad' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'data' => [
            'idconv_asistente' => $conversacion,
            'asistente' => $asistente,
        ]
    ], 201);
}

/**
 * Enviar mensaje a asistente IA
 * POST /api/v1/chat/asistentes/conversacion/{id}/mensaje
 */
public function enviarMensajeAsistente(Request $request, int $idconv)
{
    $validator = Validator::make($request->all(), [
        'contenido' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    $conversacion = DB::table('conversaciones_asistentes')->find($idconv);
    
    if (!$conversacion || $conversacion->idemp != $request->user()->idemp) {
        return response()->json(['success' => false, 'error' => 'Conversación no encontrada'], 404);
    }

    // Guardar mensaje del empleado
    $mensajeEmpleado = DB::table('mensajes_asistentes')->insertGetId([
        'idconv_asistente' => $idconv,
        'tipo' => 'empleado',
        'contenido' => $request->contenido,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Enviar a n8n para procesamiento IA
    $asistente = DB::table('asistentes_ia')->find($conversacion->idasistente);
    
    $webhookUrl = env('N8N_ASISTENTE_WEBHOOK_URL');
    
    Http::post($webhookUrl, [
        'idconv_asistente' => $idconv,
        'idmsg_empleado' => $mensajeEmpleado,
        'asistente_slug' => $asistente->slug,
        'system_prompt' => $asistente->system_prompt,
        'mensaje' => $request->contenido,
        'contexto' => json_decode($conversacion->contexto, true),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Mensaje enviado, procesando respuesta de IA...',
        'data' => ['idmsg' => $mensajeEmpleado]
    ], 201);
}
```

### 7.6 Componente Frontend: Panel de Asistentes

**Componente Livewire**: `app/Livewire/Chat/PanelAsistentes.php`

```php
<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PanelAsistentes extends Component
{
    public $asistentes = [];
    public $conversacionActiva;
    public $mensajes = [];
    public $nuevoMensaje = '';

    public function mount()
    {
        $this->cargarAsistentes();
    }

    public function cargarAsistentes()
    {
        $empleado = auth()->user();
        
        $this->asistentes = DB::table('asistentes_ia')
            ->where('activo', true)
            ->get()
            ->filter(function ($asistente) use ($empleado) {
                $permisos = json_decode($asistente->permisos_requeridos, true);
                
                if (empty($permisos)) return true;
                
                return collect($permisos)->every(fn($p) => $empleado->can($p));
            })
            ->values();
    }

    public function abrirAsistente($idasistente)
    {
        // Crear nueva conversación
        $idconv = DB::table('conversaciones_asistentes')->insertGetId([
            'idemp' => auth()->id(),
            'idasistente' => $idasistente,
            'titulo' => 'Nueva conversación',
            'estado' => 'activa',
            'ultima_actividad' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->conversacionActiva = DB::table('conversaciones_asistentes')
            ->join('asistentes_ia', 'conversaciones_asistentes.idasistente', '=', 'asistentes_ia.idasistente')
            ->where('idconv_asistente', $idconv)
            ->first();

        $this->mensajes = [];
    }

    public function enviarMensaje()
    {
        if (empty($this->nuevoMensaje)) return;

        // Guardar mensaje del empleado
        DB::table('mensajes_asistentes')->insert([
            'idconv_asistente' => $this->conversacionActiva->idconv_asistente,
            'tipo' => 'empleado',
            'contenido' => $this->nuevoMensaje,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->mensajes[] = [
            'tipo' => 'empleado',
            'contenido' => $this->nuevoMensaje,
            'created_at' => now(),
        ];

        // Simular respuesta IA (en producción llamar a n8n)
        $this->mensajes[] = [
            'tipo' => 'ia',
            'contenido' => 'Procesando tu solicitud...',
            'created_at' => now(),
        ];

        $this->nuevoMensaje = '';
    }

    public function render()
    {
        return view('livewire.chat.panel-asistentes');
    }
}
```

---

## Resumen

✅ Widget de chat flotante para clientes
✅ Soporte para clientes autenticados y anónimos  
✅ Sesiones anónimas con 20 horas de duración
✅ Device fingerprinting para sesiones persistentes
✅ Panel de empleados con Livewire
✅ WebSockets en tiempo real con Laravel Reverb
✅ Limpieza automática de chats expirados
✅ **8 Asistentes IA especializados para empleados**
✅ **Sistema de conversaciones separadas para cada asistente**
✅ **Permisos granulares por asistente**

**Próximo paso**: Implementar los componentes Vue y probar el sistema completo.
