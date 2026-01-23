import { createApp } from 'vue';
import ChatWidgetAI from './components/chat/ChatWidgetAI.vue';

// Inicializar el widget de chat AI cuando el DOM esté listo
window.initChatWidgetAI = function(config) {
    const mountPoint = document.getElementById('chat-ai-widget-mount');

    if (!mountPoint) {
        console.error('Chat AI widget mount point not found');
        return;
    }

    const app = createApp(ChatWidgetAI, {
        apiUrl: config.apiUrl,
        csrfToken: config.csrfToken,
    });

    app.mount('#chat-ai-widget-mount');

    console.log('🤖 Chat AI Widget initialized successfully');
};
