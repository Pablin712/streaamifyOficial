<template>
  <div class="chat-bubble" @click="$emit('open')" title="Soporte técnico - Haz click para chatear">
    <div class="bubble-icon">
      <!-- Icono de agente de soporte técnico -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="32" height="32">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
      </svg>
    </div>

    <!-- Animación de pulso -->
    <div class="bubble-pulse"></div>

    <div v-if="unreadCount > 0" class="bubble-badge">
      {{ unreadCount > 99 ? '99+' : unreadCount }}
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
  width: 65px;
  height: 65px;
  background: linear-gradient(135deg, #7950f2 0%, #5f3dc4 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(121, 80, 242, 0.35);
  transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55), box-shadow 0.3s;
  position: relative;
  overflow: visible;
}

.chat-bubble:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 25px rgba(121, 80, 242, 0.45);
}

.bubble-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 2;
}

/* Animación de pulso */
.bubble-pulse {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: #7950f2;
  opacity: 0.5;
  animation: pulse-support 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  z-index: 1;
}

@keyframes pulse-support {
  0%, 100% {
    transform: scale(1);
    opacity: 0.5;
  }
  50% {
    transform: scale(1.15);
    opacity: 0;
  }
}

/* Badge de mensajes no leídos */
.bubble-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff6b6b;
  color: white;
  border-radius: 50%;
  min-width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
  padding: 0 6px;
  box-shadow: 0 2px 6px rgba(255, 107, 107, 0.5);
  border: 2px solid white;
  z-index: 3;
  animation: badge-pulse 2s ease-in-out infinite;
}

@keyframes badge-pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}
</style>
