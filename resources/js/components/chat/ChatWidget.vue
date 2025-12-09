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
      :session-expiration="sessionExpiration"
      :is-typing="isTyping"
      @close="closeChat"
      @send-message="sendMessage"
    />
  </div>
</template>

<script>
import ChatBubble from './ChatBubble.vue';
import ChatWindow from './ChatWindow.vue';
import SessionManager from './utils/sessionManager.js';

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
    enviarUrl: {
      type: String,
      required: true,
    },
    conversacionUrl: {
      type: String,
      required: true,
    },
    csrfToken: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      isOpen: false,
      conversation: null,
      messages: [],
      unreadCount: 0,
      sessionManager: new SessionManager(),
      sessionExpiration: null,
      isTyping: false,
      pollInterval: null,
    };
  },
  computed: {
    isAuthenticated() {
      return this.clienteId !== null;
    },
    sessionId() {
      return this.isAuthenticated ? null : this.sessionManager.getSessionId();
    },
  },
  async mounted() {
    console.log('🟢 ChatWidget mounted');
    console.log('📋 Config:', {
      clienteId: this.clienteId,
      enviarUrl: this.enviarUrl,
      conversacionUrl: this.conversacionUrl,
      isAuthenticated: this.isAuthenticated,
    });

    await this.initSession();

    // Actualizar tiempo de expiración cada minuto
    if (!this.isAuthenticated) {
      setInterval(() => {
        this.sessionExpiration = this.sessionManager.getExpirationTime();
      }, 60000);
    }
  },
  beforeUnmount() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
  },
  methods: {
    async initSession() {
      if (!this.isAuthenticated) {
        // Cliente anónimo: crear o recuperar sesión
        await this.sessionManager.init();
        this.sessionExpiration = this.sessionManager.getExpirationTime();
      }

      // Cargar conversación existente
      await this.loadConversation();
    },

    async loadConversation() {
      try {
        const endpoint = this.isAuthenticated
          ? this.conversacionUrl
          : `${this.conversacionUrl}/${this.sessionId}/conversacion`;

        console.log('📥 Loading conversation from:', endpoint);

        const response = await fetch(endpoint);
        const data = await response.json();

        console.log('📨 Conversation response:', data);

        if (data.success && data.data.conversacion) {
          this.conversation = data.data.conversacion;
          this.messages = data.data.mensajes || [];
          this.unreadCount = 0;
          console.log('✅ Conversation loaded:', this.messages.length, 'messages');
        } else {
          console.warn('⚠️ No conversation found or success=false');
        }
      } catch (error) {
        console.error('❌ Error loading conversation:', error);
      }
    },

    async sendMessage(contenido) {
      try {
        console.log('📤 Sending message:', contenido);

        const payload = this.isAuthenticated
          ? { idcli: this.clienteId, contenido }
          : { session_id: this.sessionId, contenido };

        console.log('📦 Payload:', payload);
        console.log('🔗 URL:', this.enviarUrl);

        const response = await fetch(this.enviarUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
          },
          body: JSON.stringify(payload),
        });

        console.log('📨 Response status:', response.status);
        const data = await response.json();
        console.log('📨 Response data:', data);

        if (data.success) {
          this.messages.push(data.data.mensaje);
          this.conversation = data.data.conversacion;
          console.log('✅ Message sent successfully');

          // Simular typing indicator
          this.isTyping = true;
          setTimeout(() => {
            this.isTyping = false;
          }, 2000);
        } else {
          console.error('❌ Error al enviar mensaje:', data.error, data.message);
          alert('Error al enviar mensaje: ' + (data.message || data.error));
        }
      } catch (error) {
        console.error('❌ Error sending message:', error);
        alert('Error de conexión: ' + error.message);
      }
    },

    openChat() {
      this.isOpen = true;
      this.unreadCount = 0;

      // Iniciar polling cada 5 segundos para nuevos mensajes
      this.startPolling();
    },

    closeChat() {
      this.isOpen = false;
      this.stopPolling();
    },

    startPolling() {
      // Verificar nuevos mensajes cada 5 segundos
      this.pollInterval = setInterval(async () => {
        if (this.conversation) {
          await this.loadConversation();
        }
      }, 5000);
    },

    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
        this.pollInterval = null;
      }
    },
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

/* Animaciones */
@media (max-width: 768px) {
  .chat-widget-container.widget-open {
    bottom: 0;
    right: 0;
    left: 0;
  }

  .chat-widget-container.widget-open :deep(.chat-window) {
    width: 100vw;
    height: 100vh;
    border-radius: 0;
  }
}
</style>
