<template>
  <div class="chat-window-ai">
    <!-- Header del chat AI -->
    <div class="chat-ai-header">
      <div class="chat-ai-header-content">
        <div class="chat-ai-avatar">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="28" height="28">
            <path d="M12 2c-1.1 0-2 .9-2 2v1H7c-1.1 0-2 .9-2 2v1H3v2h2v3c0 2.2 1.8 4 4 4h1v2h4v-2h1c2.2 0 4-1.8 4-4v-3h2V8h-2V7c0-1.1-.9-2-2-2h-3V4c0-1.1-.9-2-2-2zm-2 6h4v5c0 1.1-.9 2-2 2s-2-.9-2-2V8zm-1 2H8v3h1v-3zm6 0v3h1v-3h-1z"/>
            <circle cx="9.5" cy="10.5" r="1"/>
            <circle cx="14.5" cy="10.5" r="1"/>
          </svg>
        </div>
        <div class="chat-ai-info">
          <h3 class="chat-ai-title">Asistente IA</h3>
          <p class="chat-ai-status" :class="{ 'connecting': isConnecting }">
            {{ isConnecting ? 'Conectando...' : 'En línea' }}
          </p>
        </div>
      </div>
      <button @click="$emit('close')" class="chat-ai-close" title="Cerrar chat">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
          <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
        </svg>
      </button>
      <button @click="clearHistory" class="chat-ai-clear" title="Limpiar historial">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
          <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
        </svg>
      </button>
    </div>

    <!-- Mensajes -->
    <div class="chat-ai-messages" ref="messagesContainer">
      <div v-for="message in messages" :key="message.id" class="message-wrapper">
        <div class="chat-ai-message" :class="message.remitente === 'user' ? 'user-message' : 'bot-message'">
          <div v-if="message.remitente === 'bot'" class="message-avatar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
              <path d="M12 2c-1.1 0-2 .9-2 2v1H7c-1.1 0-2 .9-2 2v1H3v2h2v3c0 2.2 1.8 4 4 4h1v2h4v-2h1c2.2 0 4-1.8 4-4v-3h2V8h-2V7c0-1.1-.9-2-2-2h-3V4c0-1.1-.9-2-2-2zm-2 6h4v5c0 1.1-.9 2-2 2s-2-.9-2-2V8zm-1 2H8v3h1v-3zm6 0v3h1v-3h-1z"/>
              <circle cx="9.5" cy="10.5" r="1"/>
              <circle cx="14.5" cy="10.5" r="1"/>
            </svg>
          </div>
          <div class="message-content">
            <p>{{ message.contenido }}</p>
            <span class="message-time">{{ formatTime(message.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Indicador de escritura -->
      <div v-if="isTyping" class="chat-ai-message bot-message typing-indicator">
        <div class="message-avatar">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
            <path d="M12 2c-1.1 0-2 .9-2 2v1H7c-1.1 0-2 .9-2 2v1H3v2h2v3c0 2.2 1.8 4 4 4h1v2h4v-2h1c2.2 0 4-1.8 4-4v-3h2V8h-2V7c0-1.1-.9-2-2-2h-3V4c0-1.1-.9-2-2-2zm-2 6h4v5c0 1.1-.9 2-2 2s-2-.9-2-2V8zm-1 2H8v3h1v-3zm6 0v3h1v-3h-1z"/>
            <circle cx="9.5" cy="10.5" r="1"/>
            <circle cx="14.5" cy="10.5" r="1"/>
          </svg>
        </div>
        <div class="message-content">
          <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Input de mensaje -->
    <div class="chat-ai-input-container">
      <textarea
        v-model="messageInput"
        @keydown.enter.prevent="handleSend"
        placeholder="Escribe tu mensaje..."
        rows="1"
        class="chat-ai-input"
        :disabled="isConnecting"
      ></textarea>
      <button @click="handleSend" :disabled="!messageInput.trim() || isConnecting" class="chat-ai-send-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatWindowAI',
  props: {
    messages: {
      type: Array,
      default: () => [],
    },
    isTyping: {
      type: Boolean,
      default: false,
    },
    isConnecting: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      messageInput: '',
    };
  },
  emits: ['close', 'send-message', 'clear-history'],
  watch: {
    messages: {
      handler() {
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      },
      deep: true,
    },
  },
  mounted() {
    this.scrollToBottom();
  },
  methods: {
    handleSend() {
      if (this.messageInput.trim() && !this.isConnecting) {
        this.$emit('send-message', this.messageInput.trim());
        this.messageInput = '';
      }
    },
    clearHistory() {
      if (confirm('¿Estás seguro de que quieres limpiar todo el historial del chat?')) {
        this.$emit('clear-history');
      }
    },
    scrollToBottom() {
      if (this.$refs.messagesContainer) {
        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
      }
    },
    formatTime(timestamp) {
      const date = new Date(timestamp);
      return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    },
  },
};
</script>

<style scoped>
.chat-window-ai {
  width: 380px;
  height: 550px;
  background: var(--bg-card);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

[data-dark-mode="true"] .chat-window-ai {
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.6);
}

/* Header */
.chat-ai-header {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid var(--primary-dark);
}

.chat-ai-header-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.chat-ai-avatar {
  width: 45px;
  height: 45px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.chat-ai-info {
  display: flex;
  flex-direction: column;
}

.chat-ai-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-on-primary);
}

.chat-ai-status {
  margin: 0;
  font-size: 0.75rem;
  color: var(--text-on-primary);
  opacity: 0.9;
}

.chat-ai-status.connecting {
  animation: pulse-text 1.5s ease-in-out infinite;
}

@keyframes pulse-text {
  0%, 100% { opacity: 0.9; }
  50% { opacity: 0.5; }
}

.chat-ai-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-on-primary);
  transition: var(--transition-fast);
}

.chat-ai-clear {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-on-primary);
  transition: var(--transition-fast);
  margin-left: 0.5rem;
}

.chat-ai-clear:hover {
  background: rgba(255, 100, 100, 0.3);
  transform: scale(1.1);
}

.chat-ai-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

/* Mensajes */
.chat-ai-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: var(--bg-body);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.message-wrapper {
  display: flex;
  flex-direction: column;
}

.chat-ai-message {
  display: flex;
  gap: 0.5rem;
  max-width: 85%;
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.user-message {
  align-self: flex-end;
  flex-direction: row-reverse;
}

.bot-message {
  align-self: flex-start;
}

.message-avatar {
  width: 32px;
  height: 32px;
  background: var(--primary-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--text-on-primary);
  box-shadow: var(--shadow-sm);
}

.message-content {
  background: var(--bg-card);
  padding: 0.75rem 1rem;
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
}

.user-message .message-content {
  background: var(--primary-gradient);
  color: var(--text-on-primary);
  border-bottom-right-radius: 4px;
}

.bot-message .message-content {
  border-bottom-left-radius: 4px;
  border: 1px solid var(--border-color);
}

.message-content p {
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.5;
  word-wrap: break-word;
}

.message-time {
  display: block;
  font-size: 0.7rem;
  margin-top: 0.25rem;
  opacity: 0.7;
}

/* Indicador de escritura */
.typing-indicator .message-content {
  padding: 0.5rem 1rem;
}

.typing-dots {
  display: flex;
  gap: 4px;
  align-items: center;
}

.typing-dots span {
  width: 8px;
  height: 8px;
  background: var(--text-secondary);
  border-radius: 50%;
  animation: typing-bounce 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) {
  animation-delay: -0.32s;
}

.typing-dots span:nth-child(2) {
  animation-delay: -0.16s;
}

@keyframes typing-bounce {
  0%, 80%, 100% {
    transform: scale(0);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

/* Input */
.chat-ai-input-container {
  padding: 1rem;
  background: var(--bg-card);
  border-top: 1px solid var(--border-color);
  display: flex;
  gap: 0.5rem;
  align-items: flex-end;
}

.chat-ai-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  background: var(--bg-light);
  color: var(--text-primary);
  font-size: 0.9rem;
  resize: none;
  max-height: 100px;
  font-family: inherit;
  transition: var(--transition-fast);
}

.chat-ai-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(255, 226, 38, 0.1);
}

[data-dark-mode="true"] .chat-ai-input:focus {
  box-shadow: 0 0 0 3px rgba(255, 226, 38, 0.2);
}

.chat-ai-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.chat-ai-send-btn {
  width: 44px;
  height: 44px;
  background: var(--primary-gradient);
  border: none;
  border-radius: 12px;
  color: var(--text-on-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition-fast);
  flex-shrink: 0;
}

.chat-ai-send-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: var(--shadow-hover);
}

.chat-ai-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Scrollbar personalizado */
.chat-ai-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-ai-messages::-webkit-scrollbar-track {
  background: var(--bg-light);
}

.chat-ai-messages::-webkit-scrollbar-thumb {
  background: var(--primary-color);
  border-radius: 3px;
}

.chat-ai-messages::-webkit-scrollbar-thumb:hover {
  background: var(--primary-dark);
}

/* Responsive */
@media (max-width: 768px) {
  .chat-window-ai {
    width: calc(100vw - 40px);
    height: calc(100vh - 100px);
  }
}
</style>
