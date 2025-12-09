import { createApp } from 'vue';
import ChatWidget from './components/chat/ChatWidget.vue';

// Inicializar el widget de chat cuando el DOM esté listo
window.initChatWidget = function(config) {
    const mountPoint = document.getElementById('chat-widget-mount');

    if (!mountPoint) {
        console.error('Chat widget mount point not found');
        return;
    }

    const app = createApp(ChatWidget, {
        clienteId: config.clienteId,
        enviarUrl: config.enviarUrl,
        conversacionUrl: config.conversacionUrl,
        csrfToken: config.csrfToken,
    });

    app.mount('#chat-widget-mount');
};
