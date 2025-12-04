import { createApp } from 'vue';
import ChatWidget from './components/chat/ChatWidget.vue';

// Inicializar el widget de chat cuando el DOM esté listo
window.initChatWidget = function(clienteId = null, apiUrl = '/api/v1/chat') {
    const mountPoint = document.getElementById('chat-widget-mount');

    if (!mountPoint) {
        console.error('Chat widget mount point not found');
        return;
    }

    const app = createApp(ChatWidget, {
        clienteId: clienteId,
        apiUrl: apiUrl,
    });

    app.mount('#chat-widget-mount');
};
