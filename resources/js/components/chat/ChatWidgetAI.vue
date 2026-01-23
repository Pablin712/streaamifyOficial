<template>
  <div class="chat-ai-widget-container" :class="{ 'widget-open': isOpen }">
    <!-- Burbuja flotante con icono de robot -->
    <ChatBubbleAI
      v-if="!isOpen"
      @open="openChat"
    />

    <!-- Ventana de chat con IA -->
    <ChatWindowAI
      v-else
      :messages="messages"
      :is-typing="isTyping"
      :is-connecting="isConnecting"
      @close="closeChat"
      @send-message="sendMessage"
      @clear-history="clearChatHistory"
    />
  </div>
</template>

<script>
import ChatBubbleAI from './ChatBubbleAI.vue';
import ChatWindowAI from './ChatWindowAI.vue';

export default {
  name: 'ChatWidgetAI',
  components: {
    ChatBubbleAI,
    ChatWindowAI,
  },
  props: {
    apiUrl: {
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
      messages: [],
      isTyping: false,
      isConnecting: false,
      storageKey: 'streamify_ai_chat_history',
    };
  },
  mounted() {
    console.log('🤖 ChatWidgetAI mounted');
    console.log('📋 API URL:', this.apiUrl);

    // Cargar historial desde localStorage
    this.loadChatHistory();

    // Si no hay mensajes, agregar bienvenida
    if (this.messages.length === 0) {
      this.addBotMessage('¡Hola! Soy tu asistente de IA. ¿En qué puedo ayudarte hoy?');
    }
  },
  methods: {
    async sendMessage(contenido) {
      if (!contenido.trim()) return;

      try {
        // Agregar mensaje del usuario
        this.addUserMessage(contenido);

        // Mostrar indicador de escritura
        this.isTyping = true;
        this.isConnecting = true;

        console.log('📤 Sending to API:', this.apiUrl);
        console.log('📦 Message:', contenido);

        // Preparar historial de chat (últimos 10 mensajes para contexto)
        const chatHistory = this.messages
          .slice(-11) // Últimos 11 (incluyendo el que acabamos de agregar)
          .map(msg => ({
            role: msg.remitente === 'user' ? 'user' : 'assistant',
            content: msg.contenido,
            timestamp: msg.created_at
          }));

        // Enviar a nuestra API Laravel (que hará proxy a n8n)
        const payload = {
          message: contenido,
          chatHistory: chatHistory,
          sessionId: this.getSessionId(),
        };

        console.log('📦 Payload completo:', payload);

        const response = await fetch(this.apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken,
          },
          credentials: 'same-origin', // Importante: envía cookies de sesión
          body: JSON.stringify(payload),
        });

        console.log('📨 Response status:', response.status);
        console.log('📨 Response headers:', response.headers);

        this.isConnecting = false;

        if (!response.ok) {
          const errorText = await response.text();
          console.error('❌ Response error:', errorText);
          throw new Error(`HTTP error! status: ${response.status}, body: ${errorText}`);
        }

        const contentType = response.headers.get('content-type');
        let data;

        if (contentType && contentType.includes('application/json')) {
          data = await response.json();
        } else {
          const text = await response.text();
          console.warn('⚠️ Response is not JSON:', text);
          data = { message: text };
        }

        console.log('📨 Response from n8n:', data);

        // Extraer respuesta del bot (probar múltiples formatos)
        let botResponse = data.output || data.response || data.message || data.text || data.reply;

        // Si data es un array, tomar el primer elemento
        if (Array.isArray(data) && data.length > 0) {
          botResponse = data[0].output || data[0].response || data[0].message || data[0].text;
        }

        // Si aún no hay respuesta, intentar convertir todo a string
        if (!botResponse) {
          console.warn('⚠️ No standard response field found, using full data');
          botResponse = typeof data === 'string' ? data : JSON.stringify(data);
        }

        if (!botResponse || botResponse === 'undefined' || botResponse === 'null') {
          botResponse = 'Lo siento, no pude generar una respuesta válida.';
        }

        // Simular delay de escritura
        setTimeout(() => {
          this.isTyping = false;
          this.addBotMessage(botResponse);
        }, 500);

      } catch (error) {
        console.error('❌ Error completo:', error);
        console.error('❌ Error name:', error.name);
        console.error('❌ Error message:', error.message);
        console.error('❌ Error stack:', error.stack);

        this.isTyping = false;
        this.isConnecting = false;

        let errorMessage = 'Lo siento, hubo un error al conectar con el asistente.';

        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
          errorMessage += ' (Error de red - verifica la conexión o CORS)';
        } else if (error.message.includes('HTTP error')) {
          errorMessage += ` (${error.message})`;
        }

        this.addBotMessage(errorMessage + '\n\nDetalles: ' + error.message);
      }
    },

    addUserMessage(contenido) {
      const message = {
        id: Date.now(),
        contenido,
        remitente: 'user',
        created_at: new Date().toISOString(),
      };
      this.messages.push(message);
      this.saveChatHistory();
    },

    addBotMessage(contenido) {
      const message = {
        id: Date.now() + 1,
        contenido,
        remitente: 'bot',
        created_at: new Date().toISOString(),
      };
      this.messages.push(message);
      this.saveChatHistory();
    },

    saveChatHistory() {
      try {
        // Guardar solo los últimos 50 mensajes para no sobrecargar localStorage
        const historyToSave = this.messages.slice(-50);
        localStorage.setItem(this.storageKey, JSON.stringify(historyToSave));
      } catch (error) {
        console.error('Error saving chat history:', error);
      }
    },

    loadChatHistory() {
      try {
        const saved = localStorage.getItem(this.storageKey);
        if (saved) {
          this.messages = JSON.parse(saved);
          console.log('📜 Chat history loaded:', this.messages.length, 'messages');
        }
      } catch (error) {
        console.error('Error loading chat history:', error);
        this.messages = [];
      }
    },

    clearChatHistory() {
      this.messages = [];
      localStorage.removeItem(this.storageKey);
      this.addBotMessage('¡Hola! Soy tu asistente de IA. ¿En qué puedo ayudarte hoy?');
    },

    getSessionId() {
      // Generar o recuperar un ID de sesión único
      let sessionId = localStorage.getItem('streamify_ai_session_id');
      if (!sessionId) {
        sessionId = 'ai-session-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        localStorage.setItem('streamify_ai_session_id', sessionId);
      }
      return sessionId;
    },

    openChat() {
      this.isOpen = true;
    },

    closeChat() {
      this.isOpen = false;
    },
  },
};
</script>

<style scoped>
.chat-ai-widget-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
}

.chat-ai-widget-container.widget-open {
  bottom: 20px;
  right: 20px;
}
</style>
