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
      <button class="close-btn" @click="$emit('close')" title="Minimizar chat">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
          <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
      </button>
    </div>

    <!-- Advertencia para anónimos -->
    <div v-if="!isAuthenticated" class="anonymous-warning">
      <div class="warning-content">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
          <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
        </svg>
        <span>
          Sesión temporal.
          <a href="/cliente/login" class="login-link">Inicia sesión</a>
          para historial completo.
        </span>
      </div>
      <div v-if="sessionExpiration" class="session-timer">
        ⏱ Expira en: {{ sessionExpiration.hours }}h {{ sessionExpiration.minutes }}m
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
          <div class="message-sender">{{ getSenderName(message) }}</div>
          <div class="message-text">{{ message.contenido }}</div>
          <div class="message-time">{{ formatTime(message.created_at) }}</div>
        </div>
      </div>

      <!-- Indicador "escribiendo..." -->
      <div v-if="isTyping" class="typing-indicator">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
      </div>
    </div>

    <!-- Input de mensaje -->
    <div class="message-input-container">
      <textarea
        v-model="newMessage"
        @keydown.enter.exact.prevent="handleSend"
        placeholder="Escribe un mensaje..."
        rows="1"
        class="message-input"
        :disabled="isSending"
      ></textarea>
      <button
        @click="handleSend"
        :disabled="!newMessage.trim() || isSending"
        class="send-btn"
        title="Enviar mensaje"
      >
        <svg v-if="!isSending" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
        <div v-else class="spinner"></div>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatWindow',
  props: {
    conversation: {
      type: Object,
      default: null,
    },
    messages: {
      type: Array,
      default: () => [],
    },
    isAuthenticated: {
      type: Boolean,
      default: false,
    },
    sessionExpiration: {
      type: Object,
      default: null,
    },
    isTyping: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['close', 'send-message'],
  data() {
    return {
      newMessage: '',
      isSending: false,
    };
  },
  computed: {
    statusClass() {
      return this.conversation?.estado === 'en_atencion' ? 'online' : 'offline';
    },
    statusText() {
      return this.conversation?.estado === 'en_atencion'
        ? 'En línea'
        : 'Te responderemos pronto';
    },
  },
  watch: {
    messages: {
      handler() {
        this.scrollToBottom();
      },
      deep: true,
    },
  },
  methods: {
    messageClass(message) {
      return message.tipo_remitente === 'cliente' ? 'message-sent' : 'message-received';
    },

    getSenderName(message) {
      if (message.nombre_remitente) return message.nombre_remitente;

      const names = {
        'cliente': 'Tú',
        'empleado': 'Soporte',
        'ia': 'Asistente Virtual',
        'sistema': 'Sistema',
      };
      return names[message.tipo_remitente] || 'Desconocido';
    },

    formatTime(timestamp) {
      const date = new Date(timestamp);
      return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    },

    async handleSend() {
      if (!this.newMessage.trim() || this.isSending) return;

      this.isSending = true;
      const message = this.newMessage.trim();
      this.newMessage = '';

      try {
        await this.$emit('send-message', message);
      } catch (error) {
        console.error('Error sending message:', error);
        this.newMessage = message; // Restaurar mensaje si falla
      } finally {
        this.isSending = false;
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },
  },
  mounted() {
    this.scrollToBottom();
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
  background: linear-gradient(135deg, #c92a2a 0%, #2f9e44 100%);
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
  background: #2f9e44;
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
  opacity: 0.8;
  transition: opacity 0.2s;
}

.close-btn:hover {
  opacity: 1;
}

.anonymous-warning {
  background: #fff5f5;
  border-bottom: 2px solid #c92a2a;
  padding: 12px;
  font-size: 13px;
  color: #7f1d1d;
}

.warning-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.warning-content svg {
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
  margin-top: 6px;
  padding-left: 24px;
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
  text-align: center;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 12px;
}

.message {
  margin-bottom: 12px;
  display: flex;
  animation: fadeIn 0.3s;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.message-sent {
  justify-content: flex-end;
}

.message-sent .message-content {
  background: #c92a2a;
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
  white-space: pre-wrap;
  word-wrap: break-word;
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
  align-items: center;
}

.typing-dot {
  width: 8px;
  height: 8px;
  background: #9ca3af;
  border-radius: 50%;
  animation: typing 1.4s infinite;
}

.typing-dot:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-dot:nth-child(3) {
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
  max-height: 120px;
}

.message-input:focus {
  border-color: #c92a2a;
}

.message-input:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

.send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #c92a2a;
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
  flex-shrink: 0;
}

.send-btn:disabled {
  background: #d1d5db;
  cursor: not-allowed;
}

.send-btn:not(:disabled):hover {
  background: #a61e1e;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid #ffffff;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Scrollbar personalizado */
.messages-container::-webkit-scrollbar {
  width: 6px;
}

.messages-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.messages-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
